<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'bundle_operation_events','cutting_bundles','production_execution_events',
        'production_order_operations','production_orders','process_route_steps','process_route_versions',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Manufacturing route and execution controls require PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE process_route_versions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    style_id uuid NOT NULL,
    style_revision_no integer NOT NULL CHECK (style_revision_no > 0),
    version_no integer NOT NULL CHECK (version_no > 0),
    route_code varchar(100) NOT NULL,
    route_reason text NOT NULL,
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','SUBMITTED','APPROVED','SUPERSEDED','REJECTED')),
    effective_from date,
    effective_to date,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    submitted_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    submitted_at timestamptz,
    decided_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    decided_at timestamptz,
    decision_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, id, style_id, style_revision_no),
    UNIQUE (tenant_id, style_id, style_revision_no, version_no),
    FOREIGN KEY (tenant_id, style_id, style_revision_no) REFERENCES style_revisions(tenant_id, style_id, revision_no) ON DELETE RESTRICT,
    CHECK (effective_to IS NULL OR effective_from IS NULL OR effective_to >= effective_from),
    CHECK ((status <> 'APPROVED') OR (decided_by IS NOT NULL AND decided_at IS NOT NULL))
);
CREATE INDEX process_route_versions_style_idx ON process_route_versions (tenant_id, style_id, style_revision_no, status);

CREATE TABLE process_route_steps (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    route_version_id uuid NOT NULL,
    step_no integer NOT NULL CHECK (step_no > 0),
    step_code varchar(80) NOT NULL,
    department_code varchar(40) NOT NULL CHECK (department_code IN ('PRODUCT_ENGINEERING','CUTTING','PRINTING','EMBROIDERY','SEWING','WASHING','DYEING','SPECIAL_PROCESS','FINISHING','PRESSING','PACKING','QUALITY','SUBCONTRACT')),
    operation_name varchar(180) NOT NULL,
    process_type varchar(40) NOT NULL,
    is_optional boolean NOT NULL DEFAULT false,
    condition_expression jsonb NOT NULL DEFAULT '{}'::jsonb,
    predecessor_step_no integer,
    subcontract_allowed boolean NOT NULL DEFAULT false,
    quality_gate boolean NOT NULL DEFAULT false,
    standard_minutes numeric(12,4) CHECK (standard_minutes IS NULL OR standard_minutes >= 0),
    required_skill_code varchar(80),
    work_instruction_reference varchar(180),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, route_version_id, id),
    UNIQUE (tenant_id, route_version_id, step_no),
    UNIQUE (tenant_id, route_version_id, step_code),
    FOREIGN KEY (tenant_id, route_version_id) REFERENCES process_route_versions(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, route_version_id, predecessor_step_no)
        REFERENCES process_route_steps(tenant_id, route_version_id, step_no) ON DELETE RESTRICT,
    CHECK (predecessor_step_no IS NULL OR predecessor_step_no < step_no),
    CHECK (department_code <> 'SUBCONTRACT' OR subcontract_allowed = true)
);
CREATE INDEX process_route_steps_department_idx ON process_route_steps (tenant_id, department_code, route_version_id, step_no);

CREATE TABLE production_orders (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    site_id uuid NOT NULL,
    order_code varchar(100) NOT NULL,
    style_id uuid NOT NULL,
    style_revision_no integer NOT NULL,
    tech_pack_version_id uuid NOT NULL,
    route_version_id uuid NOT NULL,
    planned_quantity numeric(18,4) NOT NULL CHECK (planned_quantity > 0),
    unit_code varchar(16) NOT NULL DEFAULT 'PCS',
    planned_start_date date,
    planned_end_date date,
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','RELEASED','IN_PROGRESS','ON_HOLD','COMPLETED','CANCELLED')),
    hold_reason text,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    released_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    released_at timestamptz,
    started_at timestamptz,
    completed_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, order_code),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, site_id) REFERENCES business_sites(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, style_id, style_revision_no) REFERENCES style_revisions(tenant_id, style_id, revision_no) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, tech_pack_version_id, style_id, style_revision_no)
        REFERENCES tech_pack_versions(tenant_id, id, style_id, style_revision_no) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, route_version_id, style_id, style_revision_no)
        REFERENCES process_route_versions(tenant_id, id, style_id, style_revision_no) ON DELETE RESTRICT,
    CHECK (planned_end_date IS NULL OR planned_start_date IS NULL OR planned_end_date >= planned_start_date),
    CHECK ((status NOT IN ('RELEASED','IN_PROGRESS','ON_HOLD','COMPLETED')) OR (released_by IS NOT NULL AND released_at IS NOT NULL))
);
CREATE INDEX production_orders_status_idx ON production_orders (tenant_id, status, planned_start_date);
CREATE INDEX production_orders_style_idx ON production_orders (tenant_id, style_id, style_revision_no);

CREATE TABLE production_order_operations (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    production_order_id uuid NOT NULL,
    route_version_id uuid NOT NULL,
    route_step_id uuid NOT NULL,
    step_no integer NOT NULL,
    step_code varchar(80) NOT NULL,
    department_code varchar(40) NOT NULL,
    operation_name varchar(180) NOT NULL,
    planned_quantity numeric(18,4) NOT NULL CHECK (planned_quantity > 0),
    good_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (good_quantity >= 0),
    defective_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (defective_quantity >= 0),
    status varchar(24) NOT NULL DEFAULT 'QUEUED' CHECK (status IN ('QUEUED','READY','IN_PROGRESS','BLOCKED','COMPLETED','SKIPPED')),
    started_at timestamptz,
    completed_at timestamptz,
    updated_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, production_order_id, step_no),
    FOREIGN KEY (tenant_id, production_order_id) REFERENCES production_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, route_version_id, route_step_id) REFERENCES process_route_steps(tenant_id, route_version_id, id) ON DELETE RESTRICT,
    CHECK (good_quantity + defective_quantity <= planned_quantity)
);
CREATE INDEX production_operations_queue_idx ON production_order_operations (tenant_id, department_code, status, step_no);

CREATE TABLE production_execution_events (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    production_order_id uuid NOT NULL,
    operation_id uuid NOT NULL,
    event_type varchar(24) NOT NULL CHECK (event_type IN ('START','OUTPUT','DEFECT','DOWNTIME','BLOCK','UNBLOCK','COMPLETE','SKIP')),
    event_key varchar(160) NOT NULL,
    good_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (good_quantity >= 0),
    defective_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (defective_quantity >= 0),
    downtime_minutes numeric(12,2) NOT NULL DEFAULT 0 CHECK (downtime_minutes >= 0),
    reason_code varchar(80),
    reason_detail text,
    operator_reference varchar(120),
    workstation_reference varchar(120),
    source_device_reference varchar(160),
    recorded_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    occurred_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, event_key),
    FOREIGN KEY (tenant_id, production_order_id) REFERENCES production_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, operation_id) REFERENCES production_order_operations(tenant_id, id) ON DELETE RESTRICT,
    CHECK (good_quantity + defective_quantity > 0 OR downtime_minutes > 0 OR event_type IN ('START','BLOCK','UNBLOCK','COMPLETE','SKIP'))
);
CREATE INDEX production_events_order_idx ON production_execution_events (tenant_id, production_order_id, occurred_at);
CREATE INDEX production_events_operation_idx ON production_execution_events (tenant_id, operation_id, occurred_at);

CREATE TABLE cutting_bundles (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    production_order_id uuid NOT NULL,
    fabric_roll_id uuid NOT NULL,
    bundle_code varchar(120) NOT NULL,
    cut_plan_reference varchar(120),
    marker_reference varchar(120),
    panel_quantity integer NOT NULL CHECK (panel_quantity > 0),
    size_breakdown jsonb NOT NULL DEFAULT '{}'::jsonb,
    shade_code varchar(80),
    status varchar(24) NOT NULL DEFAULT 'CUT' CHECK (status IN ('CUT','INSPECTED','RELEASED_TO_SEWING','IN_SEWING','COMPLETED','QUARANTINED','SCRAPPED')),
    cut_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    cut_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, bundle_code),
    FOREIGN KEY (tenant_id, production_order_id) REFERENCES production_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, fabric_roll_id) REFERENCES fabric_rolls(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX cutting_bundles_order_idx ON cutting_bundles (tenant_id, production_order_id, status);

CREATE TABLE bundle_operation_events (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    bundle_id uuid NOT NULL,
    event_key varchar(160) NOT NULL,
    operation_code varchar(80) NOT NULL,
    department_code varchar(40) NOT NULL,
    event_type varchar(24) NOT NULL CHECK (event_type IN ('SCAN_IN','OPERATION_COMPLETE','DEFECT','REWORK','TRANSFER','HOLD','RELEASE')),
    quantity integer NOT NULL DEFAULT 1 CHECK (quantity > 0),
    device_reference varchar(160),
    operator_reference varchar(120),
    reason_code varchar(80),
    occurred_at timestamptz NOT NULL,
    recorded_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, event_key),
    FOREIGN KEY (tenant_id, bundle_id) REFERENCES cutting_bundles(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX bundle_operation_events_idx ON bundle_operation_events (tenant_id, bundle_id, occurred_at);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'process_route_versions','process_route_steps','production_orders',
        'production_order_operations','production_execution_events','cutting_bundles','bundle_operation_events'
    ] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON process_route_versions, process_route_steps, production_orders,
    production_order_operations, cutting_bundles TO app_runtime;
GRANT SELECT, INSERT ON production_execution_events, bundle_operation_events TO app_runtime;
SQL);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
