<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = ['shipment_lines', 'shipments', 'customer_order_lines', 'customer_orders'];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Distribution and fulfilment require PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE customer_orders (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    order_number varchar(100) NOT NULL,
    channel_code varchar(32) NOT NULL CHECK (channel_code IN ('WHOLESALE','RETAIL','RESELLER','D2C','MARKETPLACE','EXPORT','INTERNAL_TRANSFER')),
    customer_reference varchar(120),
    customer_name_snapshot varchar(255) NOT NULL,
    currency char(3) NOT NULL,
    ordered_at timestamptz NOT NULL,
    requested_delivery_date date,
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','CONFIRMED','ALLOCATED','PART_FULFILLED','FULFILLED','CANCELLED','ON_HOLD')),
    payment_terms_reference varchar(120),
    delivery_terms_reference varchar(120),
    credit_control_status varchar(24) NOT NULL DEFAULT 'NOT_CHECKED' CHECK (credit_control_status IN ('NOT_CHECKED','CLEAR','HELD','APPROVED_EXCEPTION')),
    hold_reason text,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    confirmed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    confirmed_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, order_number),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status NOT IN ('CONFIRMED','ALLOCATED','PART_FULFILLED','FULFILLED')) OR (confirmed_by IS NOT NULL AND confirmed_at IS NOT NULL)),
    CHECK (status NOT IN ('CONFIRMED','ALLOCATED','PART_FULFILLED','FULFILLED') OR credit_control_status IN ('CLEAR','APPROVED_EXCEPTION'))
);
CREATE INDEX customer_orders_channel_idx ON customer_orders (tenant_id, channel_code, status, ordered_at DESC);

CREATE TABLE customer_order_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    customer_order_id uuid NOT NULL,
    line_no integer NOT NULL CHECK (line_no > 0),
    sku_code varchar(100) NOT NULL,
    style_reference varchar(120),
    colour_code varchar(60),
    size_code varchar(40),
    description varchar(400) NOT NULL,
    ordered_quantity numeric(18,4) NOT NULL CHECK (ordered_quantity > 0),
    reserved_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (reserved_quantity >= 0),
    fulfilled_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (fulfilled_quantity >= 0),
    unit_code varchar(16) NOT NULL DEFAULT 'PCS',
    unit_price numeric(18,6) NOT NULL CHECK (unit_price >= 0),
    tax_code_reference varchar(80),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, customer_order_id, line_no),
    FOREIGN KEY (tenant_id, customer_order_id) REFERENCES customer_orders(tenant_id, id) ON DELETE RESTRICT,
    CHECK (reserved_quantity <= ordered_quantity),
    CHECK (fulfilled_quantity <= ordered_quantity)
);
CREATE INDEX customer_order_lines_sku_idx ON customer_order_lines (tenant_id, sku_code);

CREATE TABLE shipments (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    customer_order_id uuid NOT NULL,
    source_location_id uuid NOT NULL,
    shipment_number varchar(100) NOT NULL,
    shipment_mode varchar(24) NOT NULL CHECK (shipment_mode IN ('ROAD','AIR','SEA','COURIER','CUSTOMER_COLLECTION','INTERNAL_TRANSFER')),
    carrier_reference varchar(180),
    tracking_reference varchar(180),
    destination_snapshot jsonb NOT NULL,
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','PICKING','PACKED','RELEASED','DISPATCHED','IN_TRANSIT','DELIVERED','DELIVERY_EXCEPTION','CANCELLED')),
    packed_at timestamptz,
    dispatched_at timestamptz,
    delivered_at timestamptz,
    released_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    dispatched_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, shipment_number),
    FOREIGN KEY (tenant_id, customer_order_id) REFERENCES customer_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, source_location_id) REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status NOT IN ('RELEASED','DISPATCHED','IN_TRANSIT','DELIVERED')) OR (released_by IS NOT NULL AND packed_at IS NOT NULL)),
    CHECK ((status NOT IN ('DISPATCHED','IN_TRANSIT','DELIVERED')) OR (dispatched_by IS NOT NULL AND dispatched_at IS NOT NULL))
);
CREATE INDEX shipments_order_idx ON shipments (tenant_id, customer_order_id, status);
CREATE INDEX shipments_tracking_idx ON shipments (tenant_id, tracking_reference);

CREATE TABLE shipment_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    shipment_id uuid NOT NULL,
    customer_order_line_id uuid NOT NULL,
    material_lot_id uuid,
    fabric_roll_id uuid,
    trim_lot_id uuid,
    sku_code varchar(100) NOT NULL,
    quantity numeric(18,4) NOT NULL CHECK (quantity > 0),
    unit_code varchar(16) NOT NULL DEFAULT 'PCS',
    pack_reference varchar(120),
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, shipment_id) REFERENCES shipments(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, customer_order_line_id) REFERENCES customer_order_lines(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, material_lot_id) REFERENCES material_lots(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, material_lot_id, fabric_roll_id) REFERENCES fabric_rolls(tenant_id, material_lot_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, material_lot_id, trim_lot_id) REFERENCES trim_lots(tenant_id, material_lot_id, id) ON DELETE RESTRICT,
    CHECK (fabric_roll_id IS NULL OR trim_lot_id IS NULL)
);
CREATE INDEX shipment_lines_order_line_idx ON shipment_lines (tenant_id, customer_order_line_id);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY['customer_orders','customer_order_lines','shipments','shipment_lines'] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON customer_orders, customer_order_lines, shipments, shipment_lines TO app_runtime;
SQL);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
