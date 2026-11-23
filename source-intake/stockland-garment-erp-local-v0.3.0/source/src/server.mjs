import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import crypto from 'node:crypto';
import { openDatabase, seedLocations, transaction, getLocationByCode, getOnHand } from './db.mjs';
import { hashPassword, verifyPassword, randomToken, sha256, newId, parseCookies, cookieHeader, clearCookieHeader } from './security.mjs';
import { handleEnterpriseApi, postSaleJournal, postBalancedJournal } from './modules/enterprise.mjs';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const PUBLIC_DIR = path.resolve(HERE, '../public');
const SESSION_COOKIE = 'stockland_session';
const PAYMENTS = new Set(['CASH', 'CARD', 'MOBILE_MONEY', 'BANK_TRANSFER']);
const WRITABLE_ROLES = new Set(['owner', 'admin', 'branch_manager', 'warehouse_manager', 'cashier']);

class HttpError extends Error {
  constructor(status, message, details = undefined) { super(message); this.status = status; this.details = details; }
}

const asText = (v, max = 160) => String(v ?? '').trim().slice(0, max);
const asMoneyCents = (v) => {
  const n = typeof v === 'number' ? v : Number(String(v ?? '').trim());
  if (!Number.isFinite(n) || n < 0 || n > 100000000) throw new HttpError(400, 'Amount must be a non-negative valid number.');
  return Math.round((n + Number.EPSILON) * 100);
};
const asQty = (v) => {
  const n = typeof v === 'number' ? v : Number(String(v ?? '').trim());
  if (!Number.isFinite(n) || n <= 0 || n > 1000000 || Math.abs(n * 1000 - Math.round(n * 1000)) > 1e-7) {
    throw new HttpError(400, 'Quantity must be greater than zero and use no more than 3 decimal places.');
  }
  return Math.round(n * 1000) / 1000;
};
const centsToAmount = (n) => Number(n || 0) / 100;
const isoNow = () => new Date().toISOString();
const json = (res, status, body, extraHeaders = {}) => {
  const payload = JSON.stringify(body);
  res.writeHead(status, { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff', 'Referrer-Policy': 'same-origin', 'X-Frame-Options': 'DENY', 'Permissions-Policy': 'camera=(), microphone=(), geolocation=()', 'Content-Security-Policy': "default-src 'self'; script-src 'self'; connect-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; font-src 'self'; object-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'", 'Content-Length': Buffer.byteLength(payload), ...extraHeaders });
  res.end(payload);
};
const noContent = (res, headers = {}) => { res.writeHead(204, headers); res.end(); };

async function readBody(req, limit = 3 * 1024 * 1024) {
  const chunks = [];
  let size = 0;
  for await (const chunk of req) {
    size += chunk.length;
    if (size > limit) throw new HttpError(413, 'Request body is too large.');
    chunks.push(chunk);
  }
  const raw = Buffer.concat(chunks).toString('utf8');
  if (!raw) return {};
  const type = String(req.headers['content-type'] || '').split(';')[0].trim();
  if (type !== 'application/json') throw new HttpError(415, 'Content-Type must be application/json.');
  try { return JSON.parse(raw); } catch { throw new HttpError(400, 'Request body is not valid JSON.'); }
}

function audit(db, actorId, action, entityType, entityId, details = {}) {
  db.prepare('INSERT INTO audit_log(actor_id,action,entity_type,entity_id,details_json) VALUES(?,?,?,?,?)')
    .run(actorId ?? null, action, entityType, String(entityId), JSON.stringify(details));
}

function userDto(user) {
  if (!user) return null;
  return { id: user.id, email: user.email, fullName: user.full_name, role: user.role, locationId: user.location_id, locationCode: user.location_code || null, locationName: user.location_name || null };
}

function getUserCount(db) { return Number(db.prepare('SELECT COUNT(*) AS n FROM users').get().n); }

function getSession(db, req, hashSessionToken) {
  const raw = parseCookies(req.headers.cookie || '')[SESSION_COOKIE];
  if (!raw || raw.length > 200) return null;
  const tokenHash = hashSessionToken(raw);
  const row = db.prepare(`SELECT s.csrf_token,s.expires_at,u.id,u.email,u.full_name,u.role,u.location_id,l.code AS location_code,l.name AS location_name,u.active
    FROM sessions s JOIN users u ON u.id=s.user_id LEFT JOIN locations l ON l.id=u.location_id WHERE s.token_hash=?`).get(tokenHash);
  if (!row || row.active !== 1 || row.expires_at < Date.now()) {
    if (row) db.prepare('DELETE FROM sessions WHERE token_hash=?').run(tokenHash);
    return null;
  }
  return { tokenHash, csrf: row.csrf_token, user: userDto(row) };
}

function requireRole(session, roles) {
  if (!session) throw new HttpError(401, 'Sign in is required.');
  if (!roles.includes(session.user.role)) throw new HttpError(403, 'Your role does not have permission for this action.');
  if (['branch_manager', 'cashier', 'warehouse_manager'].includes(session.user.role) && !session.user.locationId) {
    throw new HttpError(403, 'This role must be assigned to a location before accessing operational data.');
  }
  return session.user;
}

function assertLocationScope(user, location) {
  if (!location) throw new HttpError(404, 'Location not found.');
  if (['owner', 'admin', 'accountant', 'auditor'].includes(user.role)) return;
  if (!user.locationId || user.locationId !== location.id) throw new HttpError(403, 'This user is restricted to their assigned location.');
}

function parseCsv(text) {
  const rows = [];
  let row = [], field = '', quoted = false;
  for (let i = 0; i < text.length; i++) {
    const ch = text[i];
    if (quoted) {
      if (ch === '"' && text[i + 1] === '"') { field += '"'; i++; }
      else if (ch === '"') quoted = false;
      else field += ch;
    } else if (ch === '"' && field.length === 0) quoted = true;
    else if (ch === ',') { row.push(field); field = ''; }
    else if (ch === '\n') { row.push(field.replace(/\r$/, '')); if (row.some(x => x.trim() !== '')) rows.push(row); row = []; field = ''; }
    else field += ch;
  }
  if (quoted) throw new HttpError(400, 'CSV contains an unclosed quoted field.');
  if (field.length || row.length) { row.push(field.replace(/\r$/, '')); if (row.some(x => x.trim() !== '')) rows.push(row); }
  if (!rows.length) throw new HttpError(400, 'CSV file is empty.');
  const headers = rows.shift().map(x => x.replace(/^\uFEFF/, '').trim().toLowerCase());
  if (new Set(headers).size !== headers.length) throw new HttpError(400, 'CSV has duplicate column names.');
  return rows.map((values, index) => {
    const record = { _rowNumber: index + 2 };
    headers.forEach((key, i) => { record[key] = String(values[i] ?? '').trim(); });
    return record;
  });
}

function createSale(db, { location, userId, externalSaleId, paymentMethod, saleTime, lines, sourceType = 'POS', batchId = null }) {
  if (!PAYMENTS.has(paymentMethod)) throw new HttpError(400, 'Unsupported payment method. Credit sales are not enabled in this local baseline.');
  const prepared = [];
  const requestedBySku = new Map();
  let totalCents = 0;
  for (const line of lines) {
    const sku = asText(line.sku, 64).toUpperCase();
    const product = db.prepare('SELECT * FROM products WHERE sku=? AND active=1').get(sku);
    if (!product) throw new HttpError(422, `Unknown or inactive SKU: ${sku || '(blank)'}.`);
    const qty = asQty(line.qty);
    const unitPriceCents = line.unitPriceCents !== undefined ? Number(line.unitPriceCents) : product.retail_cents;
    if (!Number.isSafeInteger(unitPriceCents) || unitPriceCents < 0) throw new HttpError(400, `Invalid sale price for ${sku}.`);
    const lineTotal = Math.round(unitPriceCents * qty);
    totalCents += lineTotal;
    requestedBySku.set(sku, (requestedBySku.get(sku) || 0) + qty);
    prepared.push({ sku, qty, unitPriceCents, lineTotal, unitCostCents: product.cost_cents });
  }
  for (const [sku, requested] of requestedBySku) {
    const reserved = Number(db.prepare(`SELECT COALESCE(SUM(rl.qty),0) AS qty FROM reservation_lines rl JOIN reservations r ON r.id=rl.reservation_id WHERE rl.sku=? AND r.location_id=? AND r.status='active' AND (r.reserved_until IS NULL OR date(r.reserved_until)>=date('now'))`).get(sku, location.id).qty || 0);
    const available = Math.max(0, getOnHand(db, sku, location.id) - reserved);
    if (available + 1e-9 < requested) throw new HttpError(409, `Insufficient stock for ${sku}: requested ${requested}, available ${available}.`, { sku, requested, available });
  }
  if (!prepared.length) throw new HttpError(400, 'A sale must contain at least one line.');
  if (!Number.isSafeInteger(totalCents)) throw new HttpError(400, 'Sale total is outside the supported amount range.');
  const saleId = newId();
  db.prepare(`INSERT INTO pos_sales(id,external_sale_id,location_id,cashier_id,source_type,payment_method,total_cents,sale_time,import_batch_id)
    VALUES(?,?,?,?,?,?,?,?,?)`).run(saleId, externalSaleId, location.id, userId ?? null, sourceType, paymentMethod, totalCents, saleTime || isoNow(), batchId);
  const insertLine = db.prepare('INSERT INTO pos_sale_lines(sale_id,sku,qty,unit_price_cents,line_total_cents) VALUES(?,?,?,?,?)');
  const insertMove = db.prepare(`INSERT INTO inventory_movements(sku,location_id,movement_type,qty_delta,unit_cost_cents,source_type,source_id,note,created_by)
    VALUES(?,?,?,?,?,?,?,?,?)`);
  let cogsCents = 0;
  for (const item of prepared) {
    insertLine.run(saleId, item.sku, item.qty, item.unitPriceCents, item.lineTotal);
    insertMove.run(item.sku, location.id, 'SALE', -item.qty, item.unitCostCents, sourceType, saleId, `Sale ${externalSaleId}`, userId ?? null);
    cogsCents += Math.round(item.unitCostCents * item.qty);
  }
  postSaleJournal(db, { saleId, userId, paymentMethod, totalCents, cogsCents, saleTime: saleTime || isoNow(), sourceType });
  audit(db, userId, 'POS_SALE_POSTED', 'pos_sale', saleId, { externalSaleId, location: location.code, lines: prepared.length, totalCents, sourceType });
  return { id: saleId, externalSaleId, total: centsToAmount(totalCents), lineCount: prepared.length };
}

export function createApplication({ dbPath = process.env.DATABASE_PATH || './data/stockland.sqlite', publicDir = PUBLIC_DIR } = {}) {
  const secureCookie = process.env.NODE_ENV === 'production';
  const appSecret = process.env.APP_SECRET || 'stockland-local-session-pepper-v0';
  if (secureCookie && (!process.env.APP_SECRET || process.env.APP_SECRET.length < 32)) {
    throw new Error('APP_SECRET must be explicitly set to at least 32 characters in production.');
  }
  const sessionTtlMs = Math.max(1, Number(process.env.SESSION_TTL_HOURS || 12)) * 3600000;
  const hashSessionToken = token => sha256(`${token}:${appSecret}`);
  const db = openDatabase(dbPath);
  seedLocations(db);
  const loginAttempts = new Map();

  async function api(req, res, url) {
    const method = req.method || 'GET';
    const route = url.pathname;
    const session = getSession(db, req, hashSessionToken);
    if (method !== 'GET' && method !== 'HEAD' && !['/api/setup', '/api/auth/login', '/api/auth/logout'].includes(route)) {
      if (!session) throw new HttpError(401, 'Sign in is required.');
      if (req.headers['x-csrf-token'] !== session.csrf) throw new HttpError(403, 'CSRF token is missing or invalid.');
    }

    if (route === '/api/health' && method === 'GET') {
      return json(res, 200, { status: 'ok', application: 'Stockland Garment ERP', version: '0.3.0', database: 'connected', setupRequired: getUserCount(db) === 0, runtime: process.version, timestamp: isoNow() });
    }

    if (route === '/api/setup' && method === 'POST') {
      if (getUserCount(db) !== 0) throw new HttpError(409, 'Initial setup is already complete.');
      const body = await readBody(req);
      const email = asText(body.email, 190).toLowerCase();
      const fullName = asText(body.fullName, 120);
      if (!/^\S+@\S+\.\S+$/.test(email) || !fullName) throw new HttpError(400, 'Enter a valid owner name and email.');
      const passwordHash = await hashPassword(body.password);
      let user;
      transaction(db, () => {
        if (getUserCount(db) !== 0) throw new HttpError(409, 'Initial setup is already complete.');
        seedLocations(db);
        const location = getLocationByCode(db, 'CENTRAL_WAREHOUSE');
        const result = db.prepare('INSERT INTO users(email,full_name,password_hash,role,location_id) VALUES(?,?,?,?,?)').run(email, fullName, passwordHash, 'owner', location.id);
        user = { id: Number(result.lastInsertRowid), email, fullName, role: 'owner', locationId: location.id, locationCode: location.code, locationName: location.name };
        audit(db, user.id, 'INITIAL_SETUP', 'user', user.id, { seededLocations: 5 });
      });
      const rawToken = randomToken();
      const csrf = randomToken(24);
      db.prepare('INSERT INTO sessions(token_hash,user_id,csrf_token,expires_at) VALUES(?,?,?,?)').run(hashSessionToken(rawToken), user.id, csrf, Date.now() + sessionTtlMs);
      return json(res, 201, { user, csrfToken: csrf }, { 'Set-Cookie': cookieHeader(SESSION_COOKIE, rawToken, { maxAge: Math.floor(sessionTtlMs / 1000), secure: secureCookie }) });
    }

    if (route === '/api/auth/login' && method === 'POST') {
      const ip = req.socket.remoteAddress || 'unknown';
      const state = loginAttempts.get(ip) || { count: 0, resetAt: Date.now() + 60000 };
      if (state.resetAt < Date.now()) { state.count = 0; state.resetAt = Date.now() + 60000; }
      if (state.count >= 10) throw new HttpError(429, 'Too many sign-in attempts. Try again later.');
      const body = await readBody(req);
      const email = asText(body.email, 190).toLowerCase();
      const userRow = db.prepare(`SELECT u.*,l.code AS location_code,l.name AS location_name FROM users u LEFT JOIN locations l ON l.id=u.location_id WHERE u.email=? AND u.active=1`).get(email);
      const valid = userRow ? await verifyPassword(body.password, userRow.password_hash) : false;
      if (!valid) { state.count++; loginAttempts.set(ip, state); throw new HttpError(401, 'Email or password is incorrect.'); }
      loginAttempts.delete(ip);
      const rawToken = randomToken();
      const csrf = randomToken(24);
      db.prepare('INSERT INTO sessions(token_hash,user_id,csrf_token,expires_at) VALUES(?,?,?,?)').run(hashSessionToken(rawToken), userRow.id, csrf, Date.now() + sessionTtlMs);
      audit(db, userRow.id, 'LOGIN', 'user', userRow.id, {});
      return json(res, 200, { user: userDto(userRow), csrfToken: csrf }, { 'Set-Cookie': cookieHeader(SESSION_COOKIE, rawToken, { maxAge: Math.floor(sessionTtlMs / 1000), secure: secureCookie }) });
    }

    if (route === '/api/auth/logout' && method === 'POST') {
      if (session) db.prepare('DELETE FROM sessions WHERE token_hash=?').run(session.tokenHash);
      return json(res, 200, { success: true }, { 'Set-Cookie': clearCookieHeader(SESSION_COOKIE, secureCookie) });
    }

    if (route === '/api/auth/session' && method === 'GET') {
      return json(res, 200, { authenticated: Boolean(session), user: session?.user || null, csrfToken: session?.csrf || null, setupRequired: getUserCount(db) === 0 });
    }

    if (route === '/api/locations' && method === 'GET') {
      requireRole(session, [...WRITABLE_ROLES, 'accountant', 'auditor']);
      const rows = db.prepare("SELECT id,code,name,kind FROM locations WHERE active=1 ORDER BY CASE WHEN code='CENTRAL_WAREHOUSE' THEN 0 ELSE 1 END,name").all();
      return json(res, 200, rows);
    }

    if (route === '/api/users' && method === 'GET') {
      requireRole(session, ['owner', 'admin']);
      const users = db.prepare(`SELECT u.id,u.email,u.full_name,u.role,u.location_id,u.active,l.code AS location_code,l.name AS location_name
        FROM users u LEFT JOIN locations l ON l.id=u.location_id ORDER BY u.created_at DESC`).all();
      return json(res, 200, users.map(userDto));
    }

    if (route === '/api/users' && method === 'POST') {
      const actor = requireRole(session, ['owner', 'admin']);
      const body = await readBody(req);
      const email = asText(body.email, 190).toLowerCase();
      const fullName = asText(body.fullName, 120);
      const role = asText(body.role, 40);
      const allowed = ['admin', 'branch_manager', 'warehouse_manager', 'cashier', 'accountant', 'auditor'];
      if (!/^\S+@\S+\.\S+$/.test(email) || !fullName || !allowed.includes(role)) throw new HttpError(400, 'Invalid user details or role.');
      let locationId = null;
      if (body.locationCode) {
        const location = getLocationByCode(db, asText(body.locationCode, 40));
        if (!location) throw new HttpError(400, 'Unknown location.');
        locationId = location.id;
      }
      if (['branch_manager', 'cashier'].includes(role) && !locationId) throw new HttpError(400, 'Branch managers and cashiers must be assigned to a branch.');
      if (['branch_manager', 'cashier'].includes(role) && db.prepare('SELECT kind FROM locations WHERE id=?').get(locationId)?.kind !== 'branch') throw new HttpError(400, 'Branch managers and cashiers must be assigned to a branch, not the Central Warehouse.');
      if (role === 'warehouse_manager' && !locationId) throw new HttpError(400, 'Warehouse managers must be assigned to a warehouse or branch location.');
      const passwordHash = await hashPassword(body.password);
      let id;
      try {
        const r = db.prepare('INSERT INTO users(email,full_name,password_hash,role,location_id) VALUES(?,?,?,?,?)').run(email, fullName, passwordHash, role, locationId);
        id = Number(r.lastInsertRowid);
      } catch (error) { if (String(error.message).includes('UNIQUE')) throw new HttpError(409, 'Email already exists.'); throw error; }
      audit(db, actor.id, 'USER_CREATED', 'user', id, { email, role, locationId });
      return json(res, 201, { id, email, fullName, role, locationId });
    }

    const userMatch = route.match(/^\/api\/users\/(\d+)$/);
    if (userMatch && method === 'PATCH') {
      const actor = requireRole(session, ['owner','admin']);
      const id = Number(userMatch[1]);
      const target = db.prepare('SELECT * FROM users WHERE id=?').get(id);
      if (!target) throw new HttpError(404, 'User not found.');
      if (target.role === 'owner') throw new HttpError(403, 'The primary Owner account is administered through its own secure profile.');
      const body = await readBody(req);
      const fullName = asText(body.fullName ?? target.full_name, 120);
      const role = asText(body.role ?? target.role, 40);
      const allowed = ['admin','branch_manager','warehouse_manager','cashier','accountant','auditor'];
      if (!fullName || !allowed.includes(role)) throw new HttpError(400, 'Enter a valid user name and role.');
      let locationId = target.location_id;
      if (body.locationCode !== undefined) {
        if (body.locationCode === '') locationId = null;
        else {
          const location = getLocationByCode(db, asText(body.locationCode,40));
          if (!location) throw new HttpError(400, 'Unknown location.');
          locationId = location.id;
        }
      }
      if (['branch_manager','cashier'].includes(role) && !locationId) throw new HttpError(400, 'Branch managers and cashiers must be assigned to a branch.');
      if (['branch_manager','cashier'].includes(role) && db.prepare('SELECT kind FROM locations WHERE id=?').get(locationId)?.kind !== 'branch') throw new HttpError(400, 'Branch managers and cashiers must be assigned to a retail branch.');
      if (role === 'warehouse_manager' && !locationId) throw new HttpError(400, 'Warehouse managers must be assigned to a Stockland location.');
      const active = body.active === undefined ? target.active : (body.active ? 1 : 0);
      if (id === actor.id && !active) throw new HttpError(400, 'A signed-in user cannot deactivate their own account.');
      let passwordHash = target.password_hash;
      if (body.password !== undefined && body.password !== '') passwordHash = await hashPassword(body.password);
      transaction(db,()=>{
        db.prepare('UPDATE users SET full_name=?,role=?,location_id=?,active=?,password_hash=? WHERE id=?')
          .run(fullName,role,locationId,active,passwordHash,id);
        audit(db,actor.id,'USER_UPDATED','user',id,{fullName,role,locationId,active,passwordChanged:passwordHash!==target.password_hash});
      });
      return json(res,200,{id,fullName,role,locationId,active:Boolean(active)});
    }

    if (route === '/api/dashboard' && method === 'GET') {
      const actor = requireRole(session, [...WRITABLE_ROLES, 'accountant', 'auditor']);
      const scoped = ['branch_manager', 'cashier', 'warehouse_manager'].includes(actor.role) && actor.locationId ? actor.locationId : null;
      const locSql = scoped ? ' AND location_id=?' : '';
      const args = scoped ? [scoped] : [];
      const productCount = Number(db.prepare('SELECT COUNT(*) AS n FROM products WHERE active=1').get().n);
      const stockRow = db.prepare(`SELECT COALESCE(SUM(qty_delta),0) AS units FROM inventory_movements WHERE 1=1${locSql}`).get(...args);
      const low = db.prepare(`SELECT COUNT(*) AS n FROM (
        SELECT p.sku,COALESCE(SUM(m.qty_delta),0) AS qty FROM products p LEFT JOIN inventory_movements m ON m.sku=p.sku ${scoped ? 'AND m.location_id=?' : ''}
        WHERE p.active=1 GROUP BY p.sku HAVING qty <= 5
      )`).get(...(scoped ? [scoped] : []));
      const salesToday = db.prepare(`SELECT COALESCE(SUM(total_cents),0) AS amount,COUNT(*) AS n FROM pos_sales WHERE date(sale_time)=date('now','localtime')${scoped ? ' AND location_id=?' : ''}`).get(...args);
      const staged = db.prepare(`SELECT COUNT(*) AS n FROM import_batches WHERE status IN ('staged','needs_correction','blocked')${scoped ? ' AND location_id=?' : ''}`).get(...args);
      return json(res, 200, { productCount, stockUnits: Number(stockRow.units || 0), lowStockCount: Number(low.n), salesToday: centsToAmount(salesToday.amount), saleCountToday: Number(salesToday.n), pendingImports: Number(staged.n), locationScope: scoped });
    }

    if (route === '/api/products' && method === 'GET') {
      const actor = requireRole(session, [...WRITABLE_ROLES, 'accountant', 'auditor']);
      const q = asText(url.searchParams.get('q'), 80);
      const products = db.prepare(`SELECT p.* FROM products p WHERE p.active=1 AND (?='' OR p.sku LIKE ? OR p.name LIKE ? OR p.brand LIKE ? OR p.barcode LIKE ?)
        ORDER BY p.name,p.sku LIMIT 500`).all(q, `%${q}%`, `%${q}%`, `%${q}%`, `%${q}%`);
      return json(res, 200, products.map(p => ({ sku:p.sku,name:p.name,brand:p.brand,category:p.category,department:p.department,ageGroup:p.age_group,size:p.size,color:p.color,barcode:p.barcode,unit:p.unit,cost:centsToAmount(p.cost_cents),retailPrice:centsToAmount(p.retail_cents),wholesalePrice:centsToAmount(p.wholesale_cents) })));
    }

    if (route === '/api/products' && method === 'POST') {
      const actor = requireRole(session, ['owner', 'admin', 'warehouse_manager']);
      const b = await readBody(req);
      const sku = asText(b.sku, 64).toUpperCase();
      const name = asText(b.name, 160);
      if (!/^[A-Z0-9][A-Z0-9._-]{1,63}$/.test(sku) || !name) throw new HttpError(400, 'Enter a valid SKU and product name.');
      const cost = asMoneyCents(b.cost ?? 0), retail = asMoneyCents(b.retailPrice ?? 0), wholesale = asMoneyCents(b.wholesalePrice ?? 0);
      const barcode = asText(b.barcode, 80) || null;
      try {
        db.prepare(`INSERT INTO products(sku,name,brand,category,department,age_group,size,color,barcode,unit,cost_cents,retail_cents,wholesale_cents)
          VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)`).run(sku,name,asText(b.brand,100),asText(b.category,100),asText(b.department,80),asText(b.ageGroup,80),asText(b.size,40),asText(b.color,60),barcode,asText(b.unit,20)||'piece',cost,retail,wholesale);
      } catch (error) {
        if (String(error.message).includes('UNIQUE')) throw new HttpError(409, 'SKU or barcode already exists.');
        throw error;
      }
      audit(db, actor.id, 'PRODUCT_CREATED', 'product', sku, { brand: asText(b.brand,100), size: asText(b.size,40), color: asText(b.color,60) });
      return json(res, 201, { sku, name });
    }

    const productMatch = route.match(/^\/api\/products\/([^/]+)$/);
    if (productMatch && method === 'PATCH') {
      const actor = requireRole(session, ['owner','admin','warehouse_manager']);
      const sku = decodeURIComponent(productMatch[1]).toUpperCase();
      const b = await readBody(req);
      const current = db.prepare('SELECT * FROM products WHERE sku=?').get(sku);
      if (!current) throw new HttpError(404, 'Product not found.');
      const fields = {
        name: asText(b.name ?? current.name,160), brand: asText(b.brand ?? current.brand,100), category: asText(b.category ?? current.category,100),
        department: asText(b.department ?? current.department,80), age_group: asText(b.ageGroup ?? current.age_group,80), size: asText(b.size ?? current.size,40),
        color: asText(b.color ?? current.color,60), barcode: asText(b.barcode ?? current.barcode,80) || null, unit: asText(b.unit ?? current.unit,20),
        cost_cents: b.cost === undefined ? current.cost_cents : asMoneyCents(b.cost), retail_cents: b.retailPrice === undefined ? current.retail_cents : asMoneyCents(b.retailPrice),
        wholesale_cents: b.wholesalePrice === undefined ? current.wholesale_cents : asMoneyCents(b.wholesalePrice)
      };
      try {
        db.prepare(`UPDATE products SET name=?,brand=?,category=?,department=?,age_group=?,size=?,color=?,barcode=?,unit=?,cost_cents=?,retail_cents=?,wholesale_cents=?,updated_at=CURRENT_TIMESTAMP WHERE sku=?`)
          .run(fields.name,fields.brand,fields.category,fields.department,fields.age_group,fields.size,fields.color,fields.barcode,fields.unit,fields.cost_cents,fields.retail_cents,fields.wholesale_cents,sku);
      } catch(error) { if (String(error.message).includes('UNIQUE')) throw new HttpError(409,'Barcode already belongs to another product.'); throw error; }
      audit(db, actor.id, 'PRODUCT_UPDATED', 'product', sku, {});
      return json(res, 200, { success:true, sku });
    }

    if (route === '/api/inventory' && method === 'GET') {
      const actor = requireRole(session, [...WRITABLE_ROLES,'accountant','auditor']);
      const locCode = asText(url.searchParams.get('location'),40);
      let location = null;
      if (locCode) { location = getLocationByCode(db,locCode); assertLocationScope(actor,location); }
      const scopedLocationId = ['branch_manager','cashier','warehouse_manager'].includes(actor.role) && actor.locationId ? actor.locationId : null;
      if (['branch_manager','cashier'].includes(actor.role) && !scopedLocationId) throw new HttpError(403,'User has no assigned branch.');
      const rows = db.prepare(`SELECT p.sku,p.name,p.brand,p.size,p.color,p.unit,l.code AS location_code,l.name AS location_name,
        COALESCE(SUM(m.qty_delta),0) AS quantity,p.retail_cents
        FROM products p CROSS JOIN locations l LEFT JOIN inventory_movements m ON m.sku=p.sku AND m.location_id=l.id
        WHERE p.active=1 AND l.active=1 ${locCode ? 'AND l.code=?' : (scopedLocationId ? 'AND l.id=?' : '')}
        GROUP BY p.sku,l.id HAVING quantity <> 0 OR ?=1 ORDER BY l.name,p.name`).all(...(locCode ? [locCode, 1] : scopedLocationId ? [scopedLocationId, 1] : [1]));
      return json(res,200,rows.map(r=>({...r,retailPrice:centsToAmount(r.retail_cents)})));
    }

    if (route === '/api/inventory/receipts' && method === 'POST') {
      const actor = requireRole(session,['owner','admin','warehouse_manager']);
      const b = await readBody(req);
      const location = getLocationByCode(db,asText(b.locationCode,40)); assertLocationScope(actor,location);
      const sku = asText(b.sku,64).toUpperCase(); const qty=asQty(b.qty); const unitCost=asMoneyCents(b.unitCost ?? 0);
      const product=db.prepare('SELECT sku FROM products WHERE sku=? AND active=1').get(sku); if(!product) throw new HttpError(404,'Active SKU not found.');
      const receiptId=newId();
      transaction(db,()=>{
        db.prepare(`INSERT INTO inventory_movements(sku,location_id,movement_type,qty_delta,unit_cost_cents,source_type,source_id,note,created_by) VALUES(?,?,?,?,?,?,?,?,?)`)
          .run(sku,location.id,'RECEIPT',qty,unitCost,'RECEIPT',receiptId,asText(b.note,300),actor.id);
        const receiptValue=Math.round(qty*unitCost);
        if(receiptValue>0) postBalancedJournal({db,transaction,audit,asText,asMoneyCents,asQty,getLocationByCode,getOnHand,HttpError,centsToAmount,isoNow,newId}, {actorId:actor.id,sourceType:'MANUAL_RECEIPT',sourceId:receiptId,description:`Inventory receipt ${receiptId}`,lines:[{accountCode:'1200',debitCents:receiptValue},{accountCode:'2010',creditCents:receiptValue}]});
        audit(db,actor.id,'STOCK_RECEIVED','inventory_receipt',receiptId,{sku,qty,location:location.code,receiptValue});
      });
      return json(res,201,{receiptId,sku,qty,locationCode:location.code});
    }

    if (route === '/api/inventory/transfers' && method === 'POST') {
      const actor=requireRole(session,['owner','admin','warehouse_manager']);
      const b=await readBody(req); const from=getLocationByCode(db,asText(b.fromLocationCode,40)); const to=getLocationByCode(db,asText(b.toLocationCode,40));
      assertLocationScope(actor,from); if(!to || from.id===to.id) throw new HttpError(400,'Choose two different valid locations.');
      const sku=asText(b.sku,64).toUpperCase(); const qty=asQty(b.qty);
      if(!db.prepare('SELECT 1 FROM products WHERE sku=? AND active=1').get(sku)) throw new HttpError(404,'Active SKU not found.');
      const transferId=newId();
      transaction(db,()=>{
        const available=getOnHand(db,sku,from.id);
        if(available+1e-9<qty) throw new HttpError(409,`Insufficient stock: requested ${qty}, available ${available}.`,{available,requested:qty});
        const sql=`INSERT INTO inventory_movements(sku,location_id,movement_type,qty_delta,source_type,source_id,note,created_by) VALUES(?,?,?,?,?,?,?,?)`;
        db.prepare(sql).run(sku,from.id,'TRANSFER_OUT',-qty,'TRANSFER',transferId,`Transfer to ${to.code}`,actor.id);
        db.prepare(sql).run(sku,to.id,'TRANSFER_IN',qty,'TRANSFER',transferId,`Transfer from ${from.code}`,actor.id);
        audit(db,actor.id,'STOCK_TRANSFER_POSTED','inventory_transfer',transferId,{sku,qty,from:from.code,to:to.code});
      });
      return json(res,201,{transferId,sku,qty,from:from.code,to:to.code});
    }

    if (route === '/api/inventory/adjustments' && method === 'POST') {
      const actor=requireRole(session,['owner','admin']); const b=await readBody(req);
      const location=getLocationByCode(db,asText(b.locationCode,40)); assertLocationScope(actor,location);
      const sku=asText(b.sku,64).toUpperCase(); const delta=Number(b.qtyDelta); const note=asText(b.reason,300);
      if(!Number.isFinite(delta)||delta===0||Math.abs(delta)>1000000||Math.abs(delta*1000-Math.round(delta*1000))>1e-7||note.length<5) throw new HttpError(400,'Enter a valid quantity adjustment and a reason of at least 5 characters.');
      if(!db.prepare('SELECT 1 FROM products WHERE sku=? AND active=1').get(sku)) throw new HttpError(404,'Active SKU not found.');
      const id=newId();
      transaction(db,()=>{
        const available=getOnHand(db,sku,location.id); if(available+delta < -1e-9) throw new HttpError(409,'Adjustment cannot make stock negative.');
        db.prepare(`INSERT INTO inventory_movements(sku,location_id,movement_type,qty_delta,source_type,source_id,note,created_by) VALUES(?,?,?,?,?,?,?,?)`).run(sku,location.id,'ADJUSTMENT',delta,'ADJUSTMENT',id,note,actor.id);
        const product=db.prepare('SELECT cost_cents FROM products WHERE sku=?').get(sku);const value=Math.round(Math.abs(delta)*Number(product.cost_cents||0));
        if(value>0){const lines=delta>0?[{accountCode:'1200',debitCents:value},{accountCode:'4020',creditCents:value}]:[{accountCode:'5300',debitCents:value},{accountCode:'1200',creditCents:value}];postBalancedJournal({db,transaction,audit,asText,asMoneyCents,asQty,getLocationByCode,getOnHand,HttpError,centsToAmount,isoNow,newId},{actorId:actor.id,sourceType:'STOCK_ADJUSTMENT',sourceId:id,description:`Stock adjustment ${id}`,lines});}
        audit(db,actor.id,'STOCK_ADJUSTED','inventory_adjustment',id,{sku,delta,location:location.code,reason:note,journalValue:value});
      });
      return json(res,201,{adjustmentId:id,sku,qtyDelta:delta});
    }

    if (route === '/api/pos/sales' && method === 'GET') {
      const actor=requireRole(session,[...WRITABLE_ROLES,'accountant','auditor']);
      const scoped=['branch_manager','cashier','warehouse_manager'].includes(actor.role)&&actor.locationId?actor.locationId:null;
      const rows=db.prepare(`SELECT s.*,l.code AS location_code,u.full_name AS cashier_name FROM pos_sales s JOIN locations l ON l.id=s.location_id LEFT JOIN users u ON u.id=s.cashier_id
        WHERE (? IS NULL OR s.location_id=?) ORDER BY s.sale_time DESC LIMIT 200`).all(scoped,scoped);
      return json(res,200,rows.map(r=>({...r,total:centsToAmount(r.total_cents)})));
    }

    if (route === '/api/pos/sales' && method === 'POST') {
      const actor=requireRole(session,['owner','admin','branch_manager','cashier']); const b=await readBody(req);
      const location=getLocationByCode(db,asText(b.locationCode,40)); assertLocationScope(actor,location);
      if (location.kind !== 'branch') throw new HttpError(400,'Retail POS sales must be recorded against a branch location.');
      const externalSaleId=asText(req.headers['idempotency-key'] || b.externalSaleId,100);
      if(!externalSaleId) throw new HttpError(400,'Idempotency-Key or externalSaleId is required.');
      const existing=db.prepare('SELECT * FROM pos_sales WHERE location_id=? AND external_sale_id=?').get(location.id,externalSaleId);
      if(existing) return json(res,200,{id:existing.id,externalSaleId,duplicate:true,total:centsToAmount(existing.total_cents)});
      const lines=(Array.isArray(b.lines)?b.lines:[]).map(line=>({sku:line.sku,qty:line.qty}));
      let result;
      try {
        transaction(db,()=>{
          // Retail price is loaded from the server-side product record. The browser cannot set a sale price.
          result=createSale(db,{location,userId:actor.id,externalSaleId,paymentMethod:asText(b.paymentMethod,30).toUpperCase(),saleTime:isoNow(),lines});
        });
      } catch(error) { if(String(error.message).includes('UNIQUE')) { const prior=db.prepare('SELECT * FROM pos_sales WHERE location_id=? AND external_sale_id=?').get(location.id,externalSaleId); if(prior)return json(res,200,{id:prior.id,externalSaleId,duplicate:true,total:centsToAmount(prior.total_cents)}); } throw error; }
      return json(res,201,result);
    }

    if (route === '/api/pos/imports' && method === 'GET') {
      const actor=requireRole(session,[...WRITABLE_ROLES,'accountant','auditor']);
      const scoped=['branch_manager','cashier','warehouse_manager'].includes(actor.role)&&actor.locationId?actor.locationId:null;
      const rows=db.prepare(`SELECT b.*,l.code AS location_code,l.name AS location_name,u.full_name AS uploader FROM import_batches b JOIN locations l ON l.id=b.location_id LEFT JOIN users u ON u.id=b.created_by
        WHERE (? IS NULL OR b.location_id=?) ORDER BY b.id DESC LIMIT 100`).all(scoped,scoped);
      return json(res,200,rows);
    }

    if (route === '/api/pos/imports' && method === 'POST') {
      const actor=requireRole(session,['owner','admin','branch_manager']); const b=await readBody(req);
      const csvText=String(b.csv || ''); if(Buffer.byteLength(csvText)>2*1024*1024) throw new HttpError(413,'CSV exceeds the 2 MB limit.');
      const rows=parseCsv(csvText); const required=['branch_code','external_sale_id','sku','qty','unit_price','payment_method','sale_time'];
      const headers=new Set(Object.keys(rows[0]||{})); const missing=required.filter(x=>!headers.has(x)); if(missing.length) throw new HttpError(400,`Missing CSV columns: ${missing.join(', ')}.`);
      const branchCodes=[...new Set(rows.map(r=>asText(r.branch_code,40)))]; if(branchCodes.length!==1) throw new HttpError(400,'Each upload must contain sales for exactly one branch.');
      const location=getLocationByCode(db,branchCodes[0]); assertLocationScope(actor,location);
      if (location.kind !== 'branch') throw new HttpError(400,'POS import batches must belong to a branch location.');
      if (b.declaredTotal === undefined || b.declaredTotal === null || String(b.declaredTotal).trim() === '') throw new HttpError(400,'The source POS control total is required.');
      const declaredTotalCents=asMoneyCents(b.declaredTotal);
      const digest=crypto.createHash('sha256').update(`${csvText}\nCONTROL:${declaredTotalCents}`,'utf8').digest('hex');
      const previous=db.prepare('SELECT id,status FROM import_batches WHERE digest=?').get(digest);
      if(previous) return json(res,200,{batchId:previous.id,status:previous.status,duplicate:true});
      const validated=rows.map(r=>{
        const errors=[]; const externalSaleId=asText(r.external_sale_id,100); const sku=asText(r.sku,64).toUpperCase();
        if(!externalSaleId) errors.push('external_sale_id is required');
        if(!db.prepare('SELECT 1 FROM products WHERE sku=? AND active=1').get(sku)) errors.push(`Unknown/inactive SKU ${sku}`);
        let qty=0, unitPriceCents=0; try{qty=asQty(r.qty)}catch(e){errors.push(e.message)}
        try{unitPriceCents=asMoneyCents(r.unit_price)}catch(e){errors.push(e.message)}
        const payment=asText(r.payment_method,30).toUpperCase(); if(!PAYMENTS.has(payment)) errors.push('Unsupported payment_method');
        const saleTime=asText(r.sale_time,40); if(!saleTime || Number.isNaN(Date.parse(saleTime))) errors.push('sale_time must be a valid date/time');
        const already=db.prepare('SELECT 1 FROM pos_sales WHERE location_id=? AND external_sale_id=?').get(location.id,externalSaleId);
        if(already) errors.push(`Sale ${externalSaleId} is already posted`);
        return { rowNumber:r._rowNumber, externalSaleId, sku, qty, unitPriceCents, paymentMethod:payment, saleTime, errors, raw:r };
      });
      const bySale = new Map();
      for (const row of validated) {
        if (!bySale.has(row.externalSaleId)) bySale.set(row.externalSaleId, []);
        bySale.get(row.externalSaleId).push(row);
      }
      for (const [externalSaleId, group] of bySale) {
        if (group.length > 1 && group.some(row => row.paymentMethod !== group[0].paymentMethod || row.saleTime !== group[0].saleTime)) {
          for (const row of group) row.errors.push(`Rows for sale ${externalSaleId} must share the same payment method and sale time`);
        }
      }
      const validCount=validated.filter(r=>!r.errors.length).length; const invalidCount=validated.length-validCount;
      const total=validated.reduce((sum,r)=>sum+Math.round(r.unitPriceCents*r.qty),0);
      if(!Number.isSafeInteger(total)) throw new HttpError(400,'Import total is outside the supported amount range.');
      const variance=total-declaredTotalCents;
      const batchStatus=invalidCount?'needs_correction':(variance!==0?'blocked':'staged');
      let batchId;
      transaction(db,()=>{
        const ins=db.prepare(`INSERT INTO import_batches(digest,source_name,location_id,status,row_count,valid_rows,invalid_rows,expected_total_cents,reconciled_total_cents,created_by,last_error)
          VALUES(?,?,?,?,?,?,?,?,?,?,?)`);
        const issueText=invalidCount?`${invalidCount} row(s) require correction.`:(variance!==0?`POS control-total variance: ${centsToAmount(variance).toFixed(2)} ETB.`:'');
        const result=ins.run(digest,asText(b.fileName,180)||'POS import.csv',location.id,batchStatus,validated.length,validCount,invalidCount,declaredTotalCents,total,actor.id,issueText);
        batchId=Number(result.lastInsertRowid);
        const rowIns=db.prepare(`INSERT INTO import_rows(batch_id,row_number,external_sale_id,sku,qty,unit_price_cents,payment_method,sale_time,row_status,errors_json,raw_json)
          VALUES(?,?,?,?,?,?,?,?,?,?,?)`);
        for(const r of validated) rowIns.run(batchId,r.rowNumber,r.externalSaleId,r.sku,r.qty,r.unitPriceCents,r.paymentMethod,r.saleTime,r.errors.length?'invalid':'valid',JSON.stringify(r.errors),JSON.stringify(r.raw));
        audit(db,actor.id,'POS_IMPORT_STAGED','pos_import',batchId,{location:location.code,rowCount:validated.length,validRows:validCount,invalidRows:invalidCount,digest});
      });
      return json(res,201,{batchId,status:batchStatus,rowCount:validated.length,validRows:validCount,invalidRows:invalidCount,expectedTotal:centsToAmount(declaredTotalCents),reconciledTotal:centsToAmount(total),variance:centsToAmount(variance)});
    }

    const batchMatch=route.match(/^\/api\/pos\/imports\/(\d+)$/);
    if(batchMatch && method==='GET') {
      const actor=requireRole(session,[...WRITABLE_ROLES,'accountant','auditor']); const batchId=Number(batchMatch[1]);
      const batch=db.prepare(`SELECT b.*,l.code AS location_code,l.name AS location_name FROM import_batches b JOIN locations l ON l.id=b.location_id WHERE b.id=?`).get(batchId);
      if(!batch) throw new HttpError(404,'Import batch not found.'); assertLocationScope(actor,{id:batch.location_id});
      const rows=db.prepare('SELECT row_number,external_sale_id,sku,qty,unit_price_cents,payment_method,sale_time,row_status,errors_json FROM import_rows WHERE batch_id=? ORDER BY row_number').all(batchId);
      return json(res,200,{...batch,expectedTotal:centsToAmount(batch.expected_total_cents),reconciledTotal:centsToAmount(batch.reconciled_total_cents),rows:rows.map(r=>({...r,unitPrice:centsToAmount(r.unit_price_cents),errors:JSON.parse(r.errors_json)}))});
    }

    const releaseMatch=route.match(/^\/api\/pos\/imports\/(\d+)\/release$/);
    if(releaseMatch && method==='POST') {
      const actor=requireRole(session,['branch_manager']); const batchId=Number(releaseMatch[1]);
      const batch=db.prepare(`SELECT b.*,l.code AS location_code FROM import_batches b JOIN locations l ON l.id=b.location_id WHERE b.id=?`).get(batchId);
      if(!batch) throw new HttpError(404,'Import batch not found.');
      assertLocationScope(actor,{id:batch.location_id});
      if(batch.status==='posted') return json(res,200,{batchId,status:'posted',duplicate:true,postedAt:batch.posted_at});
      if(batch.status!=='staged'||batch.invalid_rows!==0) throw new HttpError(409,'Batch is not eligible for release. Correct all rejected rows and upload a clean batch.');
      const rows=db.prepare("SELECT * FROM import_rows WHERE batch_id=? AND row_status='valid' ORDER BY row_number").all(batchId);
      const groups=new Map(); for(const row of rows){ if(!groups.has(row.external_sale_id)) groups.set(row.external_sale_id,[]); groups.get(row.external_sale_id).push(row); }
      let postedAt;
      try {
        transaction(db,()=>{
          const latest=db.prepare('SELECT status FROM import_batches WHERE id=?').get(batchId);
          if(latest.status!=='staged') throw new HttpError(409,'Import batch changed; reload before releasing.');
          let totalCents=0;
          for(const [externalSaleId,group] of groups){
            const payment=group[0].payment_method; const saleTime=group[0].sale_time;
            if(group.some(r=>r.payment_method!==payment||r.sale_time!==saleTime)) throw new HttpError(422,`Rows for sale ${externalSaleId} disagree on payment method or sale time.`);
            const result=createSale(db,{location:{id:batch.location_id,code:batch.location_code},userId:actor.id,externalSaleId,paymentMethod:payment,saleTime,lines:group.map(r=>({sku:r.sku,qty:r.qty,unitPriceCents:r.unit_price_cents})),sourceType:'POS_IMPORT',batchId});
            totalCents+=Math.round(result.total*100);
          }
          if(totalCents!==batch.reconciled_total_cents||totalCents!==batch.expected_total_cents) throw new HttpError(409,'Reconciliation total changed. Batch cannot be posted.');
          postedAt=isoNow();
          db.prepare(`UPDATE import_batches SET status='posted',released_by=?,released_at=?,posted_at=?,last_error='' WHERE id=?`).run(actor.id,postedAt,postedAt,batchId);
          audit(db,actor.id,'BRANCH_MANAGER_RELEASE_AND_AUTO_POST','pos_import',batchId,{location:batch.location_code,sales:groups.size,rows:rows.length,totalCents});
        });
      } catch(error) {
        db.prepare('UPDATE import_batches SET last_error=? WHERE id=?').run(String(error.message).slice(0,500),batchId);
        throw error;
      }
      return json(res,200,{batchId,status:'posted',releasedBy:actor.id,releasedAt:postedAt,postedAt,salesPosted:groups.size,rowsPosted:rows.length});
    }

    if(route==='/api/reports/stock' && method==='GET') {
      const actor=requireRole(session,['owner','admin','branch_manager','warehouse_manager','accountant','auditor']);
      const scoped=['branch_manager','warehouse_manager','cashier'].includes(actor.role)&&actor.locationId?actor.locationId:null;
      const rows=db.prepare(`SELECT p.sku,p.name,p.brand,p.category,p.size,p.color,l.code AS location_code,l.name AS location_name,
        COALESCE(SUM(m.qty_delta),0) AS qty,p.cost_cents,ROUND(COALESCE(SUM(m.qty_delta),0)*p.cost_cents/100.0,2) AS stock_value
        FROM products p CROSS JOIN locations l LEFT JOIN inventory_movements m ON m.sku=p.sku AND m.location_id=l.id
        WHERE p.active=1 AND l.active=1 AND (? IS NULL OR l.id=?) GROUP BY p.sku,l.id ORDER BY l.name,p.name`).all(scoped,scoped);
      return json(res,200,rows);
    }

    if(route==='/api/reports/sales' && method==='GET') {
      const actor=requireRole(session,['owner','admin','branch_manager','warehouse_manager','cashier','accountant','auditor']);
      const scoped=['branch_manager','warehouse_manager','cashier'].includes(actor.role)&&actor.locationId?actor.locationId:null;
      const rows=db.prepare(`SELECT l.code AS location_code,l.name AS location_name,COUNT(s.id) AS sale_count,COALESCE(SUM(s.total_cents),0) AS total_cents
        FROM locations l LEFT JOIN pos_sales s ON s.location_id=l.id AND date(s.sale_time)>=date('now','-30 day')
        WHERE (? IS NULL OR l.id=?) GROUP BY l.id ORDER BY l.name`).all(scoped,scoped);
      return json(res,200,rows.map(r=>({...r,total:centsToAmount(r.total_cents)})));
    }

    if(route==='/api/audit' && method==='GET') {
      requireRole(session,['owner','admin','auditor']);
      const rows=db.prepare(`SELECT a.id,a.action,a.entity_type,a.entity_id,a.details_json,a.created_at,u.full_name AS actor_name
        FROM audit_log a LEFT JOIN users u ON u.id=a.actor_id ORDER BY a.id DESC LIMIT 250`).all();
      return json(res,200,rows.map(r=>({...r,details:JSON.parse(r.details_json)})));
    }

    const handled = await handleEnterpriseApi({ db, req, res, url, session, json, readBody, transaction, audit, asText, asMoneyCents, asQty, centsToAmount, isoNow, newId, HttpError, getLocationByCode, getOnHand, requireRole, assertLocationScope });
    if (handled) return;

    throw new HttpError(404,'API endpoint not found.');
  }

  const server=http.createServer(async(req,res)=>{
    try {
      const url=new URL(req.url,'http://localhost');
      if(url.pathname.startsWith('/api/')) return await api(req,res,url);
      if(!['GET','HEAD'].includes(req.method||'GET')) throw new HttpError(405,'Method not allowed.');
      const requested=url.pathname==='/'?'index.html':decodeURIComponent(url.pathname.slice(1));
      const target=path.resolve(publicDir,requested);
      if(!target.startsWith(path.resolve(publicDir)+path.sep)) throw new HttpError(403,'Forbidden path.');
      let stat; try { stat=fs.statSync(target); } catch { throw new HttpError(404,'File not found.'); }
      if(!stat.isFile()) throw new HttpError(404,'File not found.');
      const types={'.html':'text/html; charset=utf-8','.css':'text/css; charset=utf-8','.js':'text/javascript; charset=utf-8','.svg':'image/svg+xml','.json':'application/json; charset=utf-8','.webmanifest':'application/manifest+json; charset=utf-8'};
      res.writeHead(200,{'Content-Type':types[path.extname(target)]||'application/octet-stream','Cache-Control':'no-cache','X-Content-Type-Options':'nosniff','Referrer-Policy':'same-origin','X-Frame-Options':'DENY','Permissions-Policy':'camera=(), microphone=(), geolocation=()','Content-Security-Policy':"default-src 'self'; script-src 'self'; connect-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; font-src 'self'; object-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'",'Content-Length':stat.size});
      if(req.method==='HEAD') return res.end();
      fs.createReadStream(target).pipe(res);
    } catch(error) {
      if(res.headersSent) { res.destroy(); return; }
      const status=error instanceof HttpError?error.status:500;
      if(status===500) console.error(error);
      json(res,status,{error:status===500?'Internal server error.':error.message,details:error.details});
    }
  });

  return { server, db, close:()=>new Promise(resolve=>server.close(()=>{db.close();resolve();})) };
}

if (import.meta.url === `file://${process.argv[1]}`) {
  const app=createApplication();
  const host=process.env.HOST||'127.0.0.1'; const port=Number(process.env.PORT||3000);
  app.server.listen(port,host,()=>console.log(`Stockland Garment ERP running at http://${host}:${port}`));
  const stop=()=>app.close(); process.on('SIGINT',stop); process.on('SIGTERM',stop);
}
