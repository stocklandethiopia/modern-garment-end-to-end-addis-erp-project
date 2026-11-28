<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'position_reporting_relations',
        'org_unit_manager_assignments',
        'job_positions',
        'org_units',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Enterprise organization structure requires PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE public.org_units (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES public.tenants(id) ON DELETE RESTRICT,
    parent_id uuid,
    legal_entity_id uuid,
    business_site_id uuid,
    unit_code varchar(80) NOT NULL,
    unit_name varchar(180) NOT NULL,
    unit_type varchar(40) NOT NULL CHECK (
        unit_type IN (
            'CORPORATE','DIVISION','DEPARTMENT','SECTION','TEAM',
            'FACTORY','DISTRIBUTION_CENTRE','DEPOT','BRANCH','STORE','OTHER'
        )
    ),
    status varchar(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','INACTIVE')),
    valid_from date NOT NULL,
    valid_to date,
    created_by uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    updated_by uuid REFERENCES public.users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, unit_code),
    FOREIGN KEY (tenant_id, parent_id)
        REFERENCES public.org_units(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, legal_entity_id)
        REFERENCES public.legal_entities(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, business_site_id, legal_entity_id)
        REFERENCES public.business_sites(tenant_id, id, legal_entity_id) ON DELETE RESTRICT,
    CHECK (valid_to IS NULL OR valid_to >= valid_from),
    CHECK (parent_id IS NULL OR parent_id <> id),
    CHECK (business_site_id IS NULL OR legal_entity_id IS NOT NULL)
);
CREATE INDEX org_units_parent_idx ON public.org_units (tenant_id, parent_id, status, unit_code);
CREATE INDEX org_units_entity_site_idx ON public.org_units (tenant_id, legal_entity_id, business_site_id, status);

CREATE TABLE public.job_positions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES public.tenants(id) ON DELETE RESTRICT,
    org_unit_id uuid NOT NULL,
    position_code varchar(80) NOT NULL,
    title varchar(180) NOT NULL,
    job_family varchar(100),
    grade varchar(80),
    position_class varchar(24) NOT NULL DEFAULT 'PROFESSIONAL' CHECK (
        position_class IN ('LEADERSHIP','MANAGEMENT','PROFESSIONAL','OPERATIONAL','SUPPORT','OTHER')
    ),
    status varchar(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','INACTIVE')),
    valid_from date NOT NULL,
    valid_to date,
    created_by uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    updated_by uuid REFERENCES public.users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, position_code),
    FOREIGN KEY (tenant_id, org_unit_id)
        REFERENCES public.org_units(tenant_id, id) ON DELETE RESTRICT,
    CHECK (valid_to IS NULL OR valid_to >= valid_from)
);
CREATE INDEX job_positions_org_unit_idx ON public.job_positions (tenant_id, org_unit_id, status, position_code);
CREATE INDEX job_positions_validity_idx ON public.job_positions (tenant_id, valid_from, valid_to, status);


CREATE TABLE public.org_unit_manager_assignments (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES public.tenants(id) ON DELETE RESTRICT,
    org_unit_id uuid NOT NULL,
    manager_position_id uuid NOT NULL,
    valid_from date NOT NULL,
    valid_to date,
    reason varchar(1000) NOT NULL,
    created_by uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, org_unit_id, manager_position_id, valid_from),
    FOREIGN KEY (tenant_id, org_unit_id)
        REFERENCES public.org_units(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, manager_position_id)
        REFERENCES public.job_positions(tenant_id, id) ON DELETE RESTRICT,
    CHECK (valid_to IS NULL OR valid_to >= valid_from)
);
CREATE INDEX org_unit_manager_assignments_lookup_idx
    ON public.org_unit_manager_assignments (tenant_id, org_unit_id, valid_from, valid_to);

CREATE TABLE public.position_reporting_relations (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES public.tenants(id) ON DELETE RESTRICT,
    subject_position_id uuid NOT NULL,
    manager_position_id uuid NOT NULL,
    reporting_type varchar(20) NOT NULL CHECK (reporting_type IN ('SOLID_LINE','FUNCTIONAL')),
    valid_from date NOT NULL,
    valid_to date,
    reason varchar(1000) NOT NULL,
    created_by uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, subject_position_id, manager_position_id, reporting_type, valid_from),
    FOREIGN KEY (tenant_id, subject_position_id)
        REFERENCES public.job_positions(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, manager_position_id)
        REFERENCES public.job_positions(tenant_id, id) ON DELETE RESTRICT,
    CHECK (subject_position_id <> manager_position_id),
    CHECK (valid_to IS NULL OR valid_to >= valid_from)
);
CREATE INDEX position_reporting_subject_idx
    ON public.position_reporting_relations (tenant_id, subject_position_id, reporting_type, valid_from, valid_to);
CREATE INDEX position_reporting_manager_idx
    ON public.position_reporting_relations (tenant_id, manager_position_id, reporting_type, valid_from, valid_to);

DO $$
DECLARE
    table_name text;
BEGIN
    FOREACH table_name IN ARRAY ARRAY['org_units','job_positions','org_unit_manager_assignments','position_reporting_relations'] LOOP
        EXECUTE format('ALTER TABLE public.%I ENABLE ROW LEVEL SECURITY', table_name);
        EXECUTE format('ALTER TABLE public.%I FORCE ROW LEVEL SECURITY', table_name);
        EXECUTE format(
            'CREATE POLICY tenant_isolation ON public.%I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)',
            table_name
        );
        EXECUTE format('REVOKE ALL ON TABLE public.%I FROM PUBLIC, app_runtime', table_name);
    END LOOP;
END $$;

GRANT SELECT, INSERT ON public.org_units, public.job_positions,
    public.org_unit_manager_assignments, public.position_reporting_relations TO app_runtime;
SQL);
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'Migration 000035 is forward-only; do not drop tenant organization structure or its reporting history.',
        );
    }
};
