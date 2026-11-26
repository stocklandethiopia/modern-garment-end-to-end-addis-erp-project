<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'inventory_transactions', 'trim_lots', 'fabric_rolls', 'material_lots',
        'goods_receipt_lines', 'goods_receipts', 'purchase_order_lines',
        'purchase_orders', 'supplier_quotes', 'sourcing_events', 'supplier_accounts',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Sourcing and material traceability require PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE supplier_accounts (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    supplier_code varchar(80) NOT NULL,
    legal_name varchar(255) NOT NULL,
    supplier_type varchar(32) NOT NULL CHECK (supplier_type IN ('FABRIC_MILL','TRIM_SUPPLIER','PACKAGING','CHEMICAL','SUBCONTRACTOR','CARRIER','SERVICE')),
    country_code char(2) NOT NULL,
    tin_reference varchar(100),
    licence_reference varchar(120),
    due_diligence_status varchar(24) NOT NULL DEFAULT 'REVIEW_REQUIRED' CHECK (due_diligence_status IN ('REVIEW_REQUIRED','IN_REVIEW','APPROVED','REJECTED','SUSPENDED')),
    quality_status varchar(24) NOT NULL DEFAULT 'UNASSESSED' CHECK (quality_status IN ('UNASSESSED','PROVISIONAL','APPROVED','BLOCKED')),
    approved_at timestamptz,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, supplier_code),
    CHECK ((due_diligence_status = 'APPROVED' AND approved_at IS NOT NULL AND approved_by IS NOT NULL) OR due_diligence_status <> 'APPROVED')
);
CREATE INDEX supplier_accounts_due_diligence_idx ON supplier_accounts (tenant_id, due_diligence_status, supplier_type);

CREATE TABLE sourcing_events (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    event_code varchar(80) NOT NULL,
    event_type varchar(24) NOT NULL CHECK (event_type IN ('RFQ','RFP','FRAMEWORK_REVIEW','SUBCONTRACT_QUOTE')),
    category_code varchar(60) NOT NULL,
    description text NOT NULL,
    response_due_at timestamptz,
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','ISSUED','EVALUATING','AWARDED','CANCELLED','CLOSED')),
    decision_reason text,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    issued_at timestamptz,
    decided_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    decided_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, event_code),
    CHECK ((status <> 'AWARDED') OR (decided_by IS NOT NULL AND decided_at IS NOT NULL AND decision_reason IS NOT NULL))
);
CREATE INDEX sourcing_events_status_idx ON sourcing_events (tenant_id, status, response_due_at);

CREATE TABLE supplier_quotes (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    sourcing_event_id uuid NOT NULL,
    supplier_id uuid NOT NULL,
    supplier_quote_reference varchar(120),
    currency char(3) NOT NULL,
    quoted_lead_days integer CHECK (quoted_lead_days IS NULL OR quoted_lead_days >= 0),
    valid_until date,
    price_basis varchar(80),
    commercial_terms jsonb NOT NULL DEFAULT '{}'::jsonb,
    source_document_id uuid,
    status varchar(20) NOT NULL DEFAULT 'RECEIVED' CHECK (status IN ('RECEIVED','UNDER_REVIEW','ACCEPTED','REJECTED','EXPIRED')),
    submitted_at timestamptz NOT NULL,
    reviewed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, sourcing_event_id) REFERENCES sourcing_events(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, supplier_id) REFERENCES supplier_accounts(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX supplier_quotes_event_idx ON supplier_quotes (tenant_id, sourcing_event_id, status);

CREATE TABLE purchase_orders (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    supplier_id uuid NOT NULL,
    sourcing_event_id uuid,
    order_code varchar(80) NOT NULL,
    currency char(3) NOT NULL,
    order_date date NOT NULL,
    requested_delivery_date date,
    incoterm_code varchar(12),
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','SUBMITTED','APPROVED','SENT','PART_RECEIVED','RECEIVED','CANCELLED','CLOSED')),
    change_reason text,
    submitted_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    submitted_at timestamptz,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, order_code),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, supplier_id) REFERENCES supplier_accounts(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, sourcing_event_id) REFERENCES sourcing_events(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status NOT IN ('APPROVED','SENT','PART_RECEIVED','RECEIVED','CLOSED')) OR (approved_by IS NOT NULL AND approved_at IS NOT NULL))
);
CREATE INDEX purchase_orders_supplier_idx ON purchase_orders (tenant_id, supplier_id, order_date DESC);
CREATE INDEX purchase_orders_status_idx ON purchase_orders (tenant_id, status, requested_delivery_date);

CREATE TABLE purchase_order_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    purchase_order_id uuid NOT NULL,
    line_no integer NOT NULL CHECK (line_no > 0),
    item_code varchar(100) NOT NULL,
    material_type varchar(24) NOT NULL CHECK (material_type IN ('FABRIC','TRIM','PACKAGING','CHEMICAL','SERVICE','SUBCONTRACT_PROCESS')),
    description varchar(500) NOT NULL,
    specification_reference varchar(180),
    unit_code varchar(16) NOT NULL,
    ordered_quantity numeric(18,6) NOT NULL CHECK (ordered_quantity > 0),
    accepted_quantity numeric(18,6) NOT NULL DEFAULT 0 CHECK (accepted_quantity >= 0),
    unit_price numeric(18,6) NOT NULL CHECK (unit_price >= 0),
    required_delivery_date date,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, purchase_order_id, line_no),
    FOREIGN KEY (tenant_id, purchase_order_id) REFERENCES purchase_orders(tenant_id, id) ON DELETE RESTRICT,
    CHECK (accepted_quantity <= ordered_quantity)
);
CREATE INDEX purchase_order_lines_item_idx ON purchase_order_lines (tenant_id, item_code);

CREATE TABLE goods_receipts (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    purchase_order_id uuid NOT NULL,
    destination_location_id uuid NOT NULL,
    receipt_code varchar(80) NOT NULL,
    supplier_delivery_reference varchar(120),
    received_at timestamptz NOT NULL,
    status varchar(24) NOT NULL DEFAULT 'RECEIVED' CHECK (status IN ('RECEIVED','INSPECTION_PENDING','ACCEPTED','PART_ACCEPTED','REJECTED','CANCELLED')),
    received_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    accepted_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    accepted_at timestamptz,
    discrepancy_summary text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, receipt_code),
    FOREIGN KEY (tenant_id, purchase_order_id) REFERENCES purchase_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, destination_location_id) REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status NOT IN ('ACCEPTED','PART_ACCEPTED')) OR (accepted_by IS NOT NULL AND accepted_at IS NOT NULL))
);
CREATE INDEX goods_receipts_po_idx ON goods_receipts (tenant_id, purchase_order_id, received_at DESC);

CREATE TABLE goods_receipt_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    goods_receipt_id uuid NOT NULL,
    purchase_order_line_id uuid NOT NULL,
    line_no integer NOT NULL CHECK (line_no > 0),
    quantity_received numeric(18,6) NOT NULL CHECK (quantity_received > 0),
    quantity_accepted numeric(18,6) NOT NULL DEFAULT 0 CHECK (quantity_accepted >= 0),
    quantity_rejected numeric(18,6) NOT NULL DEFAULT 0 CHECK (quantity_rejected >= 0),
    unit_code varchar(16) NOT NULL,
    inspection_required boolean NOT NULL DEFAULT true,
    discrepancy_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, goods_receipt_id, line_no),
    FOREIGN KEY (tenant_id, goods_receipt_id) REFERENCES goods_receipts(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, purchase_order_line_id) REFERENCES purchase_order_lines(tenant_id, id) ON DELETE RESTRICT,
    CHECK (quantity_accepted + quantity_rejected <= quantity_received)
);
CREATE INDEX goods_receipt_lines_po_line_idx ON goods_receipt_lines (tenant_id, purchase_order_line_id);

CREATE TABLE material_lots (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    goods_receipt_line_id uuid NOT NULL,
    supplier_id uuid NOT NULL,
    material_code varchar(100) NOT NULL,
    material_type varchar(24) NOT NULL CHECK (material_type IN ('FABRIC','TRIM','PACKAGING','CHEMICAL')),
    supplier_lot_reference varchar(120),
    supplier_dye_lot_reference varchar(120),
    country_of_origin char(2),
    shade_code varchar(80),
    specification_snapshot jsonb NOT NULL DEFAULT '{}'::jsonb,
    status varchar(24) NOT NULL DEFAULT 'QUARANTINE' CHECK (status IN ('QUARANTINE','AVAILABLE','BLOCKED','CONSUMED','CLOSED')),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, goods_receipt_line_id) REFERENCES goods_receipt_lines(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, supplier_id) REFERENCES supplier_accounts(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX material_lots_trace_idx ON material_lots (tenant_id, material_code, supplier_lot_reference);
CREATE INDEX material_lots_status_idx ON material_lots (tenant_id, status, material_type);

CREATE TABLE fabric_rolls (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    material_lot_id uuid NOT NULL,
    roll_code varchar(120) NOT NULL,
    shade_code varchar(80),
    measured_length numeric(18,6) NOT NULL CHECK (measured_length > 0),
    unit_code varchar(16) NOT NULL,
    measured_width numeric(12,4),
    gsm numeric(12,4),
    roll_attributes jsonb NOT NULL DEFAULT '{}'::jsonb,
    status varchar(20) NOT NULL DEFAULT 'QUARANTINE' CHECK (status IN ('QUARANTINE','AVAILABLE','BLOCKED','CONSUMED','CLOSED')),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, roll_code),
    UNIQUE (tenant_id, material_lot_id, id),
    FOREIGN KEY (tenant_id, material_lot_id) REFERENCES material_lots(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX fabric_rolls_shade_idx ON fabric_rolls (tenant_id, shade_code, status);

CREATE TABLE trim_lots (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    material_lot_id uuid NOT NULL,
    trim_lot_code varchar(120) NOT NULL,
    approved_artwork_reference varchar(180),
    lot_attributes jsonb NOT NULL DEFAULT '{}'::jsonb,
    status varchar(20) NOT NULL DEFAULT 'QUARANTINE' CHECK (status IN ('QUARANTINE','AVAILABLE','BLOCKED','CONSUMED','CLOSED')),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, trim_lot_code),
    UNIQUE (tenant_id, material_lot_id, id),
    FOREIGN KEY (tenant_id, material_lot_id) REFERENCES material_lots(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX trim_lots_status_idx ON trim_lots (tenant_id, status);

CREATE TABLE inventory_transactions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    material_lot_id uuid NOT NULL,
    fabric_roll_id uuid,
    trim_lot_id uuid,
    from_location_id uuid,
    to_location_id uuid,
    movement_type varchar(32) NOT NULL CHECK (movement_type IN ('RECEIPT','TRANSFER','ISSUE','RETURN','ADJUSTMENT','SCRAP','CONSUMPTION','DISPATCH','CUSTOMER_RETURN')),
    quantity numeric(18,6) NOT NULL CHECK (quantity > 0),
    unit_code varchar(16) NOT NULL,
    idempotency_key varchar(160) NOT NULL,
    source_document_type varchar(80) NOT NULL,
    source_document_id uuid NOT NULL,
    source_line_id uuid,
    reason varchar(500),
    occurred_at timestamptz NOT NULL,
    recorded_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, idempotency_key),
    FOREIGN KEY (tenant_id, material_lot_id) REFERENCES material_lots(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, material_lot_id, fabric_roll_id) REFERENCES fabric_rolls(tenant_id, material_lot_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, material_lot_id, trim_lot_id) REFERENCES trim_lots(tenant_id, material_lot_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, from_location_id) REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, to_location_id) REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT,
    CHECK (fabric_roll_id IS NULL OR trim_lot_id IS NULL),
    CHECK (from_location_id IS NOT NULL OR to_location_id IS NOT NULL),
    CHECK (from_location_id IS NULL OR to_location_id IS NULL OR from_location_id <> to_location_id)
);
CREATE INDEX inventory_transactions_lot_idx ON inventory_transactions (tenant_id, material_lot_id, occurred_at);
CREATE INDEX inventory_transactions_location_idx ON inventory_transactions (tenant_id, from_location_id, to_location_id, occurred_at);
CREATE INDEX inventory_transactions_source_idx ON inventory_transactions (tenant_id, source_document_type, source_document_id, source_line_id);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'supplier_accounts','sourcing_events','supplier_quotes','purchase_orders',
        'purchase_order_lines','goods_receipts','goods_receipt_lines','material_lots',
        'fabric_rolls','trim_lots','inventory_transactions'
    ] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON supplier_accounts, sourcing_events, supplier_quotes, purchase_orders,
    purchase_order_lines, goods_receipts, goods_receipt_lines, material_lots, fabric_rolls, trim_lots TO app_runtime;
GRANT SELECT, INSERT ON inventory_transactions TO app_runtime;
SQL);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
