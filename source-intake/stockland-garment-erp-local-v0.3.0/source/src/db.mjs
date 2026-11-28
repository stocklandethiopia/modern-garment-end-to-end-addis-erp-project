import fs from 'node:fs';
import path from 'node:path';
import { DatabaseSync } from 'node:sqlite';
import { fileURLToPath } from 'node:url';

export const DEFAULT_LOCATIONS = [
  { code: 'CENTRAL_WAREHOUSE', name: 'Central Warehouse', kind: 'warehouse', branchCode: null },
  { code: 'GORO_BRANCH', name: 'Goro Branch', kind: 'branch', branchCode: 'GORO_BRANCH' },
  { code: 'GERJI_BRANCH', name: 'Gerji Branch', kind: 'branch', branchCode: 'GERJI_BRANCH' },
  { code: 'MEXICO_BRANCH', name: 'Mexico Branch', kind: 'branch', branchCode: 'MEXICO_BRANCH' },
  { code: 'MEGENAGNA_BRANCH', name: 'Megenagna Branch', kind: 'branch', branchCode: 'MEGENAGNA_BRANCH' }
];

export function openDatabase(dbPath = process.env.DATABASE_PATH || './data/stockland.sqlite') {
  const resolved = dbPath === ':memory:' ? dbPath : path.resolve(dbPath);
  if (resolved !== ':memory:') fs.mkdirSync(path.dirname(resolved), { recursive: true });
  const db = new DatabaseSync(resolved);
  db.exec('PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 5000;');
  if (resolved !== ':memory:') db.exec('PRAGMA journal_mode = WAL;');
  migrate(db);
  return db;
}

export function migrate(db) {
  db.exec(`
    CREATE TABLE IF NOT EXISTS locations (
      id INTEGER PRIMARY KEY,
      code TEXT NOT NULL UNIQUE,
      name TEXT NOT NULL,
      kind TEXT NOT NULL CHECK(kind IN ('warehouse','branch')),
      active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY,
      email TEXT NOT NULL UNIQUE COLLATE NOCASE,
      full_name TEXT NOT NULL,
      password_hash TEXT NOT NULL,
      role TEXT NOT NULL CHECK(role IN ('owner','admin','branch_manager','warehouse_manager','cashier','accountant','auditor')),
      location_id INTEGER REFERENCES locations(id),
      active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS sessions (
      token_hash TEXT PRIMARY KEY,
      user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      csrf_token TEXT NOT NULL,
      expires_at INTEGER NOT NULL,
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_sessions_expiry ON sessions(expires_at);

    CREATE TABLE IF NOT EXISTS products (
      id INTEGER PRIMARY KEY,
      sku TEXT NOT NULL UNIQUE COLLATE NOCASE,
      name TEXT NOT NULL,
      brand TEXT NOT NULL DEFAULT '',
      category TEXT NOT NULL DEFAULT '',
      department TEXT NOT NULL DEFAULT '',
      age_group TEXT NOT NULL DEFAULT '',
      size TEXT NOT NULL DEFAULT '',
      color TEXT NOT NULL DEFAULT '',
      barcode TEXT UNIQUE,
      unit TEXT NOT NULL DEFAULT 'piece',
      cost_cents INTEGER NOT NULL DEFAULT 0 CHECK(cost_cents >= 0),
      retail_cents INTEGER NOT NULL DEFAULT 0 CHECK(retail_cents >= 0),
      wholesale_cents INTEGER NOT NULL DEFAULT 0 CHECK(wholesale_cents >= 0),
      active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);
    CREATE INDEX IF NOT EXISTS idx_products_brand ON products(brand);

    CREATE TABLE IF NOT EXISTS inventory_movements (
      id INTEGER PRIMARY KEY,
      sku TEXT NOT NULL REFERENCES products(sku),
      location_id INTEGER NOT NULL REFERENCES locations(id),
      movement_type TEXT NOT NULL CHECK(movement_type IN ('RECEIPT','SALE','TRANSFER_IN','TRANSFER_OUT','RETURN','DAMAGE','ADJUSTMENT')),
      qty_delta REAL NOT NULL CHECK(qty_delta <> 0),
      unit_cost_cents INTEGER NOT NULL DEFAULT 0,
      source_type TEXT NOT NULL,
      source_id TEXT NOT NULL,
      note TEXT NOT NULL DEFAULT '',
      created_by INTEGER REFERENCES users(id),
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_movements_sku_location ON inventory_movements(sku, location_id);
    CREATE INDEX IF NOT EXISTS idx_movements_created ON inventory_movements(created_at);
    CREATE INDEX IF NOT EXISTS idx_movements_source ON inventory_movements(source_type, source_id);

    CREATE TABLE IF NOT EXISTS pos_sales (
      id TEXT PRIMARY KEY,
      external_sale_id TEXT NOT NULL,
      location_id INTEGER NOT NULL REFERENCES locations(id),
      cashier_id INTEGER REFERENCES users(id),
      source_type TEXT NOT NULL CHECK(source_type IN ('POS','POS_IMPORT')),
      payment_method TEXT NOT NULL CHECK(payment_method IN ('CASH','CARD','MOBILE_MONEY','BANK_TRANSFER')),
      total_cents INTEGER NOT NULL CHECK(total_cents >= 0),
      sale_time TEXT NOT NULL,
      import_batch_id INTEGER,
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE(location_id, external_sale_id)
    );
    CREATE INDEX IF NOT EXISTS idx_pos_sales_time ON pos_sales(sale_time);

    CREATE TABLE IF NOT EXISTS pos_sale_lines (
      id INTEGER PRIMARY KEY,
      sale_id TEXT NOT NULL REFERENCES pos_sales(id),
      sku TEXT NOT NULL REFERENCES products(sku),
      qty REAL NOT NULL CHECK(qty > 0),
      unit_price_cents INTEGER NOT NULL CHECK(unit_price_cents >= 0),
      line_total_cents INTEGER NOT NULL CHECK(line_total_cents >= 0)
    );

    CREATE TABLE IF NOT EXISTS import_batches (
      id INTEGER PRIMARY KEY,
      digest TEXT NOT NULL UNIQUE,
      source_name TEXT NOT NULL,
      location_id INTEGER NOT NULL REFERENCES locations(id),
      status TEXT NOT NULL CHECK(status IN ('staged','needs_correction','blocked','posted')),
      row_count INTEGER NOT NULL,
      valid_rows INTEGER NOT NULL,
      invalid_rows INTEGER NOT NULL,
      expected_total_cents INTEGER NOT NULL,
      reconciled_total_cents INTEGER NOT NULL,
      created_by INTEGER REFERENCES users(id),
      released_by INTEGER REFERENCES users(id),
      released_at TEXT,
      posted_at TEXT,
      last_error TEXT NOT NULL DEFAULT '',
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS import_rows (
      id INTEGER PRIMARY KEY,
      batch_id INTEGER NOT NULL REFERENCES import_batches(id) ON DELETE CASCADE,
      row_number INTEGER NOT NULL,
      external_sale_id TEXT NOT NULL,
      sku TEXT NOT NULL,
      qty REAL NOT NULL,
      unit_price_cents INTEGER NOT NULL,
      payment_method TEXT NOT NULL,
      sale_time TEXT NOT NULL,
      row_status TEXT NOT NULL CHECK(row_status IN ('valid','invalid')),
      errors_json TEXT NOT NULL DEFAULT '[]',
      raw_json TEXT NOT NULL,
      UNIQUE(batch_id, row_number)
    );
    CREATE INDEX IF NOT EXISTS idx_import_rows_batch ON import_rows(batch_id);

    CREATE TABLE IF NOT EXISTS audit_log (
      id INTEGER PRIMARY KEY,
      actor_id INTEGER REFERENCES users(id),
      action TEXT NOT NULL,
      entity_type TEXT NOT NULL,
      entity_id TEXT NOT NULL,
      details_json TEXT NOT NULL DEFAULT '{}',
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log(created_at);

    CREATE TRIGGER IF NOT EXISTS inventory_movements_no_update
    BEFORE UPDATE ON inventory_movements BEGIN SELECT RAISE(ABORT, 'inventory ledger entries are immutable'); END;
    CREATE TRIGGER IF NOT EXISTS inventory_movements_no_delete
    BEFORE DELETE ON inventory_movements BEGIN SELECT RAISE(ABORT, 'inventory ledger entries are immutable'); END;
    CREATE TRIGGER IF NOT EXISTS audit_log_no_update
    BEFORE UPDATE ON audit_log BEGIN SELECT RAISE(ABORT, 'audit records are immutable'); END;
    CREATE TRIGGER IF NOT EXISTS audit_log_no_delete
    BEFORE DELETE ON audit_log BEGIN SELECT RAISE(ABORT, 'audit records are immutable'); END;
    PRAGMA user_version = 1;
  `);
  const schemaPath = path.join(path.dirname(fileURLToPath(import.meta.url)), 'migrations', '002_enterprise.sql');
  db.exec(fs.readFileSync(schemaPath, 'utf8'));
}

export function transaction(db, callback) {
  db.exec('BEGIN IMMEDIATE');
  try {
    const result = callback();
    db.exec('COMMIT');
    return result;
  } catch (error) {
    try { db.exec('ROLLBACK'); } catch {}
    throw error;
  }
}

export function seedLocations(db) {
  const insert = db.prepare('INSERT OR IGNORE INTO locations(code,name,kind) VALUES(?,?,?)');
  for (const location of DEFAULT_LOCATIONS) insert.run(location.code, location.name, location.kind);
}

export function getLocationByCode(db, code) {
  return db.prepare('SELECT * FROM locations WHERE code = ? AND active = 1').get(code);
}

export function getOnHand(db, sku, locationId) {
  const row = db.prepare('SELECT COALESCE(SUM(qty_delta),0) AS qty FROM inventory_movements WHERE sku=? AND location_id=?').get(sku, locationId);
  return Number(row?.qty || 0);
}
