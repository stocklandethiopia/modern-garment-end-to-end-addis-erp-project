<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'quality_release_decisions', 'quality_corrective_actions', 'quality_nonconformances',
        'quality_inspections', 'quality_defect_catalog',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Quality release and CAPA require PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE quality_defect_catalog (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    defect_code varchar(80) NOT NULL,
    defect_name varchar(180) NOT NULL,
    defect_family varchar(40) NOT NULL CHECK (defect_family IN ('FABRIC','TRIM','CUTTING','PRINT','EMBROIDERY','SEWING','WASH','FINISHING','PACKING','MEASUREMENT','DOCUMENTATION','SAFETY','OTHER')),
    default_severity varchar(16) NOT NULL CHECK (default_severity IN ('LOW','MEDIUM','HIGH','CRITICAL')),
    requires_containment boolean NOT NULL DEFAULT false,
    description text,
    active boolean NOT NULL DEFAULT true,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, defect_code)
);
CREATE INDEX quality_defect_catalog_active_idx ON quality_defect_catalog (tenant_id, active, defect_family);

CREATE TABLE quality_inspections (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    inspection_code varchar(100) NOT NULL,
    inspection_type varchar(32) NOT NULL CHECK (inspection_type IN ('INCOMING_MATERIAL','FIRST_PIECE','INLINE','END_LINE','FINAL','PACKING','PRE_SHIPMENT','CUSTOMER_RETURN','PROCESS_AUDIT')),
    subject_type varchar(80) NOT NULL,
    subject_id uuid NOT NULL,
    sample_quantity numeric(18,4) NOT NULL CHECK (sample_quantity > 0),
    accepted_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (accepted_quantity >= 0),
    rejected_quantity numeric(18,4) NOT NULL DEFAULT 0 CHECK (rejected_quantity >= 0),
    result_status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (result_status IN ('DRAFT','IN_PROGRESS','PASS','PASS_WITH_DEVIATION','FAIL','CONTAINED','CANCELLED')),
    inspection_standard_reference varchar(180),
    sampling_plan_reference varchar(180),
    measurements jsonb NOT NULL DEFAULT '{}'::jsonb,
    observations jsonb NOT NULL DEFAULT '[]'::jsonb,
    inspected_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    inspected_at timestamptz,
    reviewed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at timestamptz,
    review_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, inspection_code),
    CHECK (accepted_quantity + rejected_quantity <= sample_quantity),
    CHECK ((result_status IN ('PASS','PASS_WITH_DEVIATION','FAIL','CONTAINED')) OR inspected_at IS NULL OR inspected_at IS NOT NULL),
    CHECK ((result_status NOT IN ('PASS','PASS_WITH_DEVIATION')) OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))
);
CREATE INDEX quality_inspections_subject_idx ON quality_inspections (tenant_id, subject_type, subject_id, inspected_at);
CREATE INDEX quality_inspections_status_idx ON quality_inspections (tenant_id, result_status, inspection_type);

CREATE TABLE quality_nonconformances (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    inspection_id uuid NOT NULL,
    defect_catalog_id uuid,
    nonconformance_code varchar(100) NOT NULL,
    defect_description text NOT NULL,
    severity varchar(16) NOT NULL CHECK (severity IN ('LOW','MEDIUM','HIGH','CRITICAL')),
    affected_quantity numeric(18,4) NOT NULL DEFAULT 1 CHECK (affected_quantity > 0),
    affected_reference varchar(180),
    containment_status varchar(24) NOT NULL DEFAULT 'REQUIRED' CHECK (containment_status IN ('REQUIRED','IN_PROGRESS','CONTAINED','NOT_REQUIRED','RELEASED_BY_AUTHORITY')),
    disposition varchar(24) NOT NULL DEFAULT 'PENDING' CHECK (disposition IN ('PENDING','USE_AS_IS','REWORK','REPAIR','SORT','RETURN_TO_SUPPLIER','SCRAP','CUSTOMER_CONCESSION')),
    status varchar(24) NOT NULL DEFAULT 'OPEN' CHECK (status IN ('OPEN','UNDER_REVIEW','CAPA_REQUIRED','RESOLVED','CLOSED')),
    containment_notes text,
    root_cause_category varchar(80),
    root_cause_analysis text,
    opened_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    resolved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    resolved_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, nonconformance_code),
    FOREIGN KEY (tenant_id, inspection_id) REFERENCES quality_inspections(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, defect_catalog_id) REFERENCES quality_defect_catalog(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status NOT IN ('RESOLVED','CLOSED')) OR (resolved_by IS NOT NULL AND resolved_at IS NOT NULL))
);
CREATE INDEX quality_nonconformance_status_idx ON quality_nonconformances (tenant_id, status, severity);
CREATE INDEX quality_nonconformance_inspection_idx ON quality_nonconformances (tenant_id, inspection_id);

CREATE TABLE quality_corrective_actions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    nonconformance_id uuid NOT NULL,
    action_code varchar(100) NOT NULL,
    action_type varchar(20) NOT NULL CHECK (action_type IN ('CORRECTIVE','PREVENTIVE','CONTAINMENT','SYSTEMIC')),
    action_description text NOT NULL,
    assigned_to_user_id uuid REFERENCES users(id) ON DELETE RESTRICT,
    due_date date,
    status varchar(24) NOT NULL DEFAULT 'OPEN' CHECK (status IN ('OPEN','IN_PROGRESS','IMPLEMENTED','EFFECTIVENESS_REVIEW','EFFECTIVE','INEFFECTIVE','CANCELLED')),
    implementation_evidence jsonb NOT NULL DEFAULT '[]'::jsonb,
    implemented_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    implemented_at timestamptz,
    effectiveness_criteria text,
    effectiveness_result text,
    verified_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    verified_at timestamptz,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, action_code),
    FOREIGN KEY (tenant_id, nonconformance_id) REFERENCES quality_nonconformances(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status NOT IN ('IMPLEMENTED','EFFECTIVENESS_REVIEW','EFFECTIVE','INEFFECTIVE')) OR (implemented_by IS NOT NULL AND implemented_at IS NOT NULL)),
    CHECK ((status NOT IN ('EFFECTIVE','INEFFECTIVE')) OR (verified_by IS NOT NULL AND verified_at IS NOT NULL))
);
CREATE INDEX quality_capa_due_idx ON quality_corrective_actions (tenant_id, status, due_date);

CREATE TABLE quality_release_decisions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    subject_type varchar(80) NOT NULL,
    subject_id uuid NOT NULL,
    release_stage varchar(32) NOT NULL CHECK (release_stage IN ('MATERIAL','PRE_PRODUCTION','PRODUCTION_OPERATION','FINISHED_GOODS','SHIPMENT','CUSTOMER_RETURN')),
    decision varchar(24) NOT NULL CHECK (decision IN ('RELEASE','RELEASE_WITH_DEVIATION','HOLD','REJECT','REINSPECT')),
    reason text NOT NULL,
    linked_inspection_id uuid,
    linked_nonconformance_id uuid,
    conditions jsonb NOT NULL DEFAULT '[]'::jsonb,
    decided_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    decided_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, linked_inspection_id) REFERENCES quality_inspections(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, linked_nonconformance_id) REFERENCES quality_nonconformances(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX quality_release_subject_idx ON quality_release_decisions (tenant_id, subject_type, subject_id, decided_at);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'quality_defect_catalog','quality_inspections','quality_nonconformances',
        'quality_corrective_actions','quality_release_decisions'
    ] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON quality_defect_catalog, quality_inspections,
    quality_nonconformances, quality_corrective_actions TO app_runtime;
GRANT SELECT, INSERT ON quality_release_decisions TO app_runtime;
SQL);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
