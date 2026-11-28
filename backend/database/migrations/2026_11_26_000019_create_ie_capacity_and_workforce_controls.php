<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'incentive_calculation_drafts', 'incentive_policy_versions', 'workforce_assignments',
        'workforce_skill_records', 'production_plan_lines', 'production_plan_versions',
        'capacity_calendar_slots', 'work_shift_definitions', 'work_centres', 'operation_standard_versions',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('IE and workforce control requires PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE operation_standard_versions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    style_id uuid NOT NULL,
    style_revision_no integer NOT NULL,
    route_version_id uuid NOT NULL,
    route_step_id uuid NOT NULL,
    version_no integer NOT NULL CHECK (version_no > 0),
    standard_minutes numeric(12,4) NOT NULL CHECK (standard_minutes > 0),
    observed_cycle_count integer NOT NULL CHECK (observed_cycle_count > 0),
    rating_basis varchar(80) NOT NULL,
    allowance_basis varchar(80) NOT NULL,
    study_evidence jsonb NOT NULL DEFAULT '{}'::jsonb,
    method_revision_reference varchar(120) NOT NULL,
    ergonomic_risk_review varchar(24) NOT NULL DEFAULT 'PENDING' CHECK (ergonomic_risk_review IN ('PENDING','ACCEPTED','MITIGATION_REQUIRED')),
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','SUBMITTED','APPROVED','SUPERSEDED','REJECTED')),
    effective_from date,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    decision_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, style_id, style_revision_no, route_step_id, version_no),
    FOREIGN KEY (tenant_id, style_id, style_revision_no) REFERENCES style_revisions(tenant_id, style_id, revision_no) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, route_version_id, route_step_id) REFERENCES process_route_steps(tenant_id, route_version_id, id) ON DELETE RESTRICT,
    CHECK ((status <> 'APPROVED') OR (approved_by IS NOT NULL AND approved_at IS NOT NULL AND ergonomic_risk_review = 'ACCEPTED'))
);
CREATE INDEX operation_standard_lookup_idx ON operation_standard_versions (tenant_id, style_id, style_revision_no, status, effective_from);

CREATE TABLE work_centres (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    site_id uuid NOT NULL,
    work_centre_code varchar(80) NOT NULL,
    department_code varchar(40) NOT NULL CHECK (department_code IN ('CUTTING','PRINTING','EMBROIDERY','SEWING','WASHING','DYEING','SPECIAL_PROCESS','FINISHING','PRESSING','PACKING','QUALITY','SAMPLE_ROOM','OTHER')),
    work_centre_type varchar(32) NOT NULL CHECK (work_centre_type IN ('LINE','CELL','MACHINE_GROUP','PROCESS_AREA','SAMPLE_ROOM','SUBCONTRACT')),
    name varchar(180) NOT NULL,
    machine_family varchar(100),
    capabilities jsonb NOT NULL DEFAULT '{}'::jsonb,
    capacity_unit varchar(24) NOT NULL DEFAULT 'MINUTES',
    status varchar(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','INACTIVE','MAINTENANCE','BLOCKED')),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, work_centre_code),
    FOREIGN KEY (tenant_id, site_id) REFERENCES business_sites(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX work_centres_department_idx ON work_centres (tenant_id, site_id, department_code, status);

CREATE TABLE work_shift_definitions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    site_id uuid NOT NULL,
    shift_code varchar(40) NOT NULL,
    shift_name varchar(120) NOT NULL,
    starts_at time NOT NULL,
    ends_at time NOT NULL,
    unpaid_break_minutes integer NOT NULL DEFAULT 0 CHECK (unpaid_break_minutes >= 0),
    active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, site_id, shift_code),
    FOREIGN KEY (tenant_id, site_id) REFERENCES business_sites(tenant_id, id) ON DELETE RESTRICT,
    CHECK (starts_at <> ends_at)
);

CREATE TABLE capacity_calendar_slots (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    work_centre_id uuid NOT NULL,
    shift_id uuid NOT NULL,
    work_date date NOT NULL,
    available_minutes integer NOT NULL CHECK (available_minutes >= 0),
    planned_maintenance_minutes integer NOT NULL DEFAULT 0 CHECK (planned_maintenance_minutes >= 0),
    reserved_minutes integer NOT NULL DEFAULT 0 CHECK (reserved_minutes >= 0),
    status varchar(20) NOT NULL DEFAULT 'OPEN' CHECK (status IN ('OPEN','LOCKED','CLOSED')),
    capacity_basis_reference varchar(180),
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, work_centre_id, shift_id, work_date),
    FOREIGN KEY (tenant_id, work_centre_id) REFERENCES work_centres(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, shift_id) REFERENCES work_shift_definitions(tenant_id, id) ON DELETE RESTRICT,
    CHECK (planned_maintenance_minutes + reserved_minutes <= available_minutes)
);
CREATE INDEX capacity_calendar_date_idx ON capacity_calendar_slots (tenant_id, work_date, status);

CREATE TABLE production_plan_versions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    plan_code varchar(80) NOT NULL,
    version_no integer NOT NULL CHECK (version_no > 0),
    horizon_start date NOT NULL,
    horizon_end date NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','SIMULATED','SUBMITTED','APPROVED','SUPERSEDED','CANCELLED')),
    planning_basis jsonb NOT NULL DEFAULT '{}'::jsonb,
    assumptions text,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, plan_code, version_no),
    CHECK (horizon_end >= horizon_start),
    CHECK ((status <> 'APPROVED') OR (approved_by IS NOT NULL AND approved_at IS NOT NULL))
);
CREATE INDEX production_plan_horizon_idx ON production_plan_versions (tenant_id, horizon_start, horizon_end, status);

CREATE TABLE production_plan_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    plan_version_id uuid NOT NULL,
    production_order_id uuid NOT NULL,
    operation_id uuid NOT NULL,
    work_centre_id uuid NOT NULL,
    capacity_slot_id uuid,
    sequence_no integer NOT NULL CHECK (sequence_no > 0),
    planned_quantity numeric(18,4) NOT NULL CHECK (planned_quantity > 0),
    planned_minutes numeric(14,4) NOT NULL CHECK (planned_minutes > 0),
    planned_start_at timestamptz NOT NULL,
    planned_end_at timestamptz NOT NULL,
    bottleneck_flag boolean NOT NULL DEFAULT false,
    schedule_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, plan_version_id, sequence_no),
    FOREIGN KEY (tenant_id, plan_version_id) REFERENCES production_plan_versions(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, production_order_id) REFERENCES production_orders(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, operation_id) REFERENCES production_order_operations(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, work_centre_id) REFERENCES work_centres(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, capacity_slot_id) REFERENCES capacity_calendar_slots(tenant_id, id) ON DELETE RESTRICT,
    CHECK (planned_end_at > planned_start_at)
);
CREATE INDEX production_plan_lines_capacity_idx ON production_plan_lines (tenant_id, work_centre_id, planned_start_at, planned_end_at);

CREATE TABLE workforce_skill_records (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    worker_reference varchar(120) NOT NULL,
    skill_code varchar(100) NOT NULL,
    skill_grade varchar(40) NOT NULL,
    assessment_result varchar(20) NOT NULL CHECK (assessment_result IN ('COMPETENT','DEVELOPING','NOT_COMPETENT','EXPIRED')),
    assessment_evidence jsonb NOT NULL DEFAULT '{}'::jsonb,
    assessed_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    assessed_at timestamptz NOT NULL,
    valid_from date NOT NULL,
    valid_until date,
    authorized_operation_scope jsonb NOT NULL DEFAULT '[]'::jsonb,
    status varchar(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','SUSPENDED','EXPIRED','REVOKED')),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
    CHECK (valid_until IS NULL OR valid_until >= valid_from),
    CHECK ((status <> 'ACTIVE') OR assessment_result = 'COMPETENT')
);
CREATE INDEX workforce_skill_lookup_idx ON workforce_skill_records (tenant_id, worker_reference, skill_code, status, valid_until);

CREATE TABLE workforce_assignments (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    operation_id uuid NOT NULL,
    work_centre_id uuid NOT NULL,
    shift_id uuid NOT NULL,
    skill_record_id uuid NOT NULL,
    worker_reference varchar(120) NOT NULL,
    assignment_status varchar(20) NOT NULL DEFAULT 'PLANNED' CHECK (assignment_status IN ('PLANNED','CONFIRMED','STARTED','COMPLETED','CANCELLED')),
    planned_start_at timestamptz NOT NULL,
    planned_end_at timestamptz NOT NULL,
    actual_start_at timestamptz,
    actual_end_at timestamptz,
    planned_minutes numeric(12,2) NOT NULL CHECK (planned_minutes > 0),
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, operation_id) REFERENCES production_order_operations(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, work_centre_id) REFERENCES work_centres(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, shift_id) REFERENCES work_shift_definitions(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, skill_record_id) REFERENCES workforce_skill_records(tenant_id, id) ON DELETE RESTRICT,
    CHECK (planned_end_at > planned_start_at),
    CHECK (actual_end_at IS NULL OR actual_start_at IS NULL OR actual_end_at > actual_start_at),
    CHECK (assignment_status NOT IN ('CONFIRMED','STARTED','COMPLETED') OR approved_by IS NOT NULL)
);
CREATE INDEX workforce_assignments_worker_idx ON workforce_assignments (tenant_id, worker_reference, planned_start_at, planned_end_at);

CREATE TABLE incentive_policy_versions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    policy_code varchar(80) NOT NULL,
    version_no integer NOT NULL CHECK (version_no > 0),
    policy_name varchar(180) NOT NULL,
    eligible_role_codes jsonb NOT NULL DEFAULT '[]'::jsonb,
    measurement_basis jsonb NOT NULL,
    quality_gate jsonb NOT NULL,
    safety_gate jsonb NOT NULL,
    grievance_and_appeal_rules text NOT NULL,
    effective_from date,
    effective_to date,
    status varchar(20) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','SUBMITTED','APPROVED','SUPERSEDED','SUSPENDED')),
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, policy_code, version_no),
    CHECK (effective_to IS NULL OR effective_from IS NULL OR effective_to >= effective_from),
    CHECK ((status <> 'APPROVED') OR (approved_by IS NOT NULL AND approved_at IS NOT NULL))
);

CREATE TABLE incentive_calculation_drafts (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    policy_version_id uuid NOT NULL,
    worker_reference varchar(120) NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    currency char(3) NOT NULL,
    calculation_components jsonb NOT NULL,
    calculated_amount numeric(18,4) NOT NULL CHECK (calculated_amount >= 0),
    exceptions jsonb NOT NULL DEFAULT '[]'::jsonb,
    status varchar(20) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','UNDER_REVIEW','APPROVED_FOR_PAYROLL','REJECTED','VOID')),
    reviewed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at timestamptz,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, policy_version_id) REFERENCES incentive_policy_versions(tenant_id, id) ON DELETE RESTRICT,
    CHECK (period_end >= period_start),
    CHECK (status <> 'APPROVED_FOR_PAYROLL' OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))
);
CREATE INDEX incentive_drafts_worker_period_idx ON incentive_calculation_drafts (tenant_id, worker_reference, period_start, period_end, status);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'operation_standard_versions','work_centres','work_shift_definitions','capacity_calendar_slots',
        'production_plan_versions','production_plan_lines','workforce_skill_records','workforce_assignments',
        'incentive_policy_versions','incentive_calculation_drafts'
    ] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON operation_standard_versions, work_centres, work_shift_definitions,
    capacity_calendar_slots, production_plan_versions, production_plan_lines, workforce_skill_records,
    workforce_assignments, incentive_policy_versions, incentive_calculation_drafts TO app_runtime;
SQL);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
