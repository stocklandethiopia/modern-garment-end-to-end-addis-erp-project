<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Domain role assignments require PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE tenant_domain_role_assignments (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    user_id uuid NOT NULL,
    role_code varchar(80) NOT NULL CHECK (role_code IN (
        'PROCUREMENT_MANAGER','SOURCING_MANAGER','SUPPLIER_QUALITY_MANAGER','PROCUREMENT_OFFICER','PROCUREMENT_APPROVER',
        'WAREHOUSE_MANAGER','WAREHOUSE_RECEIVER','INVENTORY_MANAGER','MATERIAL_CONTROLLER','INVENTORY_OPERATOR',
        'QUALITY_INSPECTOR','QUALITY_MANAGER','QUALITY_HEAD','PROCESS_IMPROVEMENT_MANAGER','ASSIGNED_ACTION_OWNER',
        'INDUSTRIAL_ENGINEER','IE_MANAGER','PRODUCT_ENGINEERING_MANAGER','FACTORY_MANAGER',
        'PRODUCTION_PLANNER','PRODUCTION_MANAGER','PRODUCTION_SUPERVISOR','LINE_SUPERVISOR','FACTORY_OPERATOR',
        'MAINTENANCE_MANAGER','SKILLS_ASSESSOR','TRAINING_MANAGER','HR_MANAGER',
        'SALES_MANAGER','WHOLESALE_MANAGER','RETAIL_MANAGER','STORE_MANAGER','CREDIT_CONTROLLER',
        'CREDIT_MANAGER','FINANCE_MANAGER','FINANCE_DIRECTOR','FINANCIAL_CONTROLLER','CHIEF_ACCOUNTANT','ACCOUNTANT',
        'PAYROLL_MANAGER','HR_DIRECTOR','LEGAL_COUNSEL','DOCUMENT_CONTROLLER','DEPARTMENT_MANAGER',
        'BUSINESS_USER','DOMAIN_APPROVER','DOMAIN_MANAGER','PROCESS_OWNER','ORDER_FULFILMENT_MANAGER',
        'DISTRIBUTION_MANAGER','LOGISTICS_MANAGER','STORE_OPERATOR'
    )),
    scope_type varchar(16) NOT NULL DEFAULT 'TENANT' CHECK (scope_type = 'TENANT'),
    scope_id uuid,
    status varchar(16) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING','ACTIVE','REVOKED')),
    valid_from date NOT NULL,
    valid_to date,
    grant_reason text NOT NULL,
    granted_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    revoked_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    revoked_at timestamptz,
    revocation_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, user_id)
        REFERENCES tenant_memberships(tenant_id, user_id) ON DELETE RESTRICT,
    CHECK (scope_id IS NULL),
    CHECK (valid_to IS NULL OR valid_to >= valid_from),
    CHECK (
        (status = 'PENDING' AND approved_by IS NULL AND approved_at IS NULL AND revoked_by IS NULL AND revoked_at IS NULL AND revocation_reason IS NULL)
        OR
        (status = 'ACTIVE' AND approved_by IS NOT NULL AND approved_at IS NOT NULL AND revoked_by IS NULL AND revoked_at IS NULL AND revocation_reason IS NULL AND approved_by <> granted_by)
        OR
        (status = 'REVOKED' AND revoked_by IS NOT NULL AND revoked_at IS NOT NULL AND revocation_reason IS NOT NULL AND length(btrim(revocation_reason)) >= 10)
    )
);
CREATE UNIQUE INDEX tenant_domain_role_assignments_open_uq
    ON tenant_domain_role_assignments (tenant_id, user_id, role_code)
    WHERE status IN ('PENDING','ACTIVE');
CREATE INDEX tenant_domain_role_assignments_lookup_idx
    ON tenant_domain_role_assignments (tenant_id, user_id, status, valid_from, valid_to, role_code);

CREATE OR REPLACE FUNCTION guard_tenant_domain_role_assignment_mutation() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        RAISE EXCEPTION 'Domain-role assignments are audited; revoke rather than delete.'
            USING ERRCODE = '55000';
    END IF;

    IF NEW.tenant_id IS DISTINCT FROM OLD.tenant_id
       OR NEW.user_id IS DISTINCT FROM OLD.user_id
       OR NEW.role_code IS DISTINCT FROM OLD.role_code
       OR NEW.scope_type IS DISTINCT FROM OLD.scope_type
       OR NEW.scope_id IS DISTINCT FROM OLD.scope_id
       OR NEW.valid_from IS DISTINCT FROM OLD.valid_from
       OR NEW.valid_to IS DISTINCT FROM OLD.valid_to
       OR NEW.grant_reason IS DISTINCT FROM OLD.grant_reason
       OR NEW.granted_by IS DISTINCT FROM OLD.granted_by
       OR NEW.created_at IS DISTINCT FROM OLD.created_at THEN
        RAISE EXCEPTION 'Domain-role assignment identity and grant terms are immutable.'
            USING ERRCODE = '55000';
    END IF;

    IF NOT (
        (OLD.status = 'PENDING' AND NEW.status IN ('ACTIVE','REVOKED'))
        OR (OLD.status = 'ACTIVE' AND NEW.status = 'REVOKED')
    ) THEN
        RAISE EXCEPTION 'Invalid domain-role assignment state transition.'
            USING ERRCODE = '55000';
    END IF;

    RETURN NEW;
END;
$$;
CREATE TRIGGER tenant_domain_role_assignment_guard
BEFORE UPDATE OR DELETE ON tenant_domain_role_assignments
FOR EACH ROW EXECUTE FUNCTION guard_tenant_domain_role_assignment_mutation();

ALTER TABLE tenant_domain_role_assignments ENABLE ROW LEVEL SECURITY;
ALTER TABLE tenant_domain_role_assignments FORCE ROW LEVEL SECURITY;
CREATE POLICY tenant_domain_role_assignment_tenant_isolation
    ON tenant_domain_role_assignments
    USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
    WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid);

REVOKE ALL ON TABLE tenant_domain_role_assignments FROM PUBLIC, app_runtime;
GRANT SELECT, INSERT ON tenant_domain_role_assignments TO app_runtime;
GRANT UPDATE (status, approved_by, approved_at, revoked_by, revoked_at, revocation_reason, updated_at)
    ON tenant_domain_role_assignments TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS guard_tenant_domain_role_assignment_mutation() CASCADE');
        DB::statement('DROP TABLE IF EXISTS tenant_domain_role_assignments CASCADE');
    }
};
