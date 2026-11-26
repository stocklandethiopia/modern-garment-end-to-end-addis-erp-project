<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = ['finished_goods_reservations', 'finished_goods_transactions', 'finished_goods_lots'];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Finished-goods inventory requires PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE finished_goods_lots (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    production_order_id uuid NOT NULL,
    final_quality_inspection_id uuid NOT NULL,
    lot_code varchar(120) NOT NULL,
    sku_code varchar(100) NOT NULL,
    style_id uuid NOT NULL,
    style_revision_no integer NOT NULL,
    colour_code varchar(60),
    size_code varchar(40),
    finished_quantity numeric(18,4) NOT NULL CHECK (finished_quantity > 0),
    unit_code varchar(16) NOT NULL DEFAULT 'PCS',
    status varchar(24) NOT NULL DEFAULT 'QUARANTINE' CHECK (status IN ('QUARANTINE','AVAILABLE','BLOCKED','PART_RESERVED','RESERVED','DEPLETED','CLOSED')),
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    received_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, lot_code),
    FOREIGN KEY (tenant_id, production_order_id) REFERENCES production_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, final_quality_inspection_id) REFERENCES quality_inspections(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, style_id, style_revision_no) REFERENCES style_revisions(tenant_id, style_id, revision_no) ON DELETE RESTRICT
);
CREATE INDEX finished_goods_sku_idx ON finished_goods_lots (tenant_id, sku_code, colour_code, size_code, status);
CREATE INDEX finished_goods_order_idx ON finished_goods_lots (tenant_id, production_order_id, status);

CREATE TABLE finished_goods_transactions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    finished_goods_lot_id uuid NOT NULL,
    from_location_id uuid,
    to_location_id uuid,
    transaction_type varchar(24) NOT NULL CHECK (transaction_type IN ('RECEIPT','TRANSFER','QUALITY_RELEASE','DISPATCH','CUSTOMER_RETURN','SCRAP','ADJUSTMENT')),
    quantity numeric(18,4) NOT NULL CHECK (quantity > 0),
    unit_code varchar(16) NOT NULL DEFAULT 'PCS',
    idempotency_key varchar(180) NOT NULL,
    source_document_type varchar(80) NOT NULL,
    source_document_id uuid NOT NULL,
    source_line_id uuid,
    reason text,
    occurred_at timestamptz NOT NULL,
    recorded_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, idempotency_key),
    FOREIGN KEY (tenant_id, finished_goods_lot_id) REFERENCES finished_goods_lots(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, from_location_id) REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, to_location_id) REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT,
    CHECK (from_location_id IS NOT NULL OR to_location_id IS NOT NULL),
    CHECK (from_location_id IS NULL OR to_location_id IS NULL OR from_location_id <> to_location_id)
);
CREATE INDEX finished_goods_ledger_lot_idx ON finished_goods_transactions (tenant_id, finished_goods_lot_id, occurred_at);
CREATE INDEX finished_goods_ledger_location_idx ON finished_goods_transactions (tenant_id, from_location_id, to_location_id, occurred_at);

CREATE TABLE finished_goods_reservations (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    customer_order_line_id uuid NOT NULL,
    finished_goods_lot_id uuid NOT NULL,
    reservation_key varchar(180) NOT NULL,
    quantity numeric(18,4) NOT NULL CHECK (quantity > 0),
    unit_code varchar(16) NOT NULL DEFAULT 'PCS',
    status varchar(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','FULFILLED','RELEASED','CANCELLED','EXPIRED')),
    reserved_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    reserved_at timestamptz NOT NULL,
    fulfilled_at timestamptz,
    released_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, reservation_key),
    FOREIGN KEY (tenant_id, customer_order_line_id) REFERENCES customer_order_lines(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, finished_goods_lot_id) REFERENCES finished_goods_lots(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status <> 'FULFILLED') OR fulfilled_at IS NOT NULL),
    CHECK ((status <> 'RELEASED') OR released_at IS NOT NULL)
);
CREATE INDEX finished_goods_reservations_active_idx ON finished_goods_reservations (tenant_id, finished_goods_lot_id, status);
CREATE INDEX finished_goods_reservations_order_idx ON finished_goods_reservations (tenant_id, customer_order_line_id, status);

ALTER TABLE shipment_lines
    ADD COLUMN finished_goods_lot_id uuid;
ALTER TABLE shipment_lines
    ADD CONSTRAINT shipment_lines_finished_goods_lot_fk
    FOREIGN KEY (tenant_id, finished_goods_lot_id)
    REFERENCES finished_goods_lots(tenant_id, id) ON DELETE RESTRICT;
ALTER TABLE shipment_lines
    ADD CONSTRAINT shipment_lines_exactly_one_trace_item_ck
    CHECK (
        num_nonnulls(material_lot_id, finished_goods_lot_id) <= 1
        AND (fabric_roll_id IS NULL OR material_lot_id IS NOT NULL)
        AND (trim_lot_id IS NULL OR material_lot_id IS NOT NULL)
    );

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY['finished_goods_lots','finished_goods_transactions','finished_goods_reservations'] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON finished_goods_lots, finished_goods_reservations TO app_runtime;
GRANT SELECT, INSERT ON finished_goods_transactions TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE shipment_lines DROP CONSTRAINT IF EXISTS shipment_lines_exactly_one_trace_item_ck');
        DB::statement('ALTER TABLE shipment_lines DROP CONSTRAINT IF EXISTS shipment_lines_finished_goods_lot_fk');
        DB::statement('ALTER TABLE shipment_lines DROP COLUMN IF EXISTS finished_goods_lot_id');
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
