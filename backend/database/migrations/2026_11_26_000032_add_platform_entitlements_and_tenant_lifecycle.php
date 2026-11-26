<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Platform administration and tenant entitlements require PostgreSQL.');
        }

        Schema::table('tenants', function ($table): void {
            $table->timestampTz('status_changed_at')->nullable();
            $table->string('status_change_reason', 500)->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->unsignedBigInteger('lifecycle_version')->default(1);
        });

        DB::unprepared(<<<'SQL'
CREATE TABLE public.platform_operator_grants (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    role_code varchar(40) NOT NULL CHECK (role_code IN ('PLATFORM_ADMIN', 'PLATFORM_SUPPORT')),
    status varchar(20) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'ACTIVE', 'REVOKED', 'EXPIRED')),
    valid_from date NOT NULL,
    valid_to date NULL,
    grant_reason varchar(500) NOT NULL,
    granted_by uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    approved_by uuid NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    approved_at timestamptz NULL,
    revoked_by uuid NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    revoked_at timestamptz NULL,
    revocation_reason varchar(500) NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    CHECK (valid_to IS NULL OR valid_to >= valid_from),
    CHECK (status <> 'ACTIVE' OR (approved_by IS NOT NULL AND approved_at IS NOT NULL AND approved_by <> granted_by))
);

CREATE UNIQUE INDEX platform_operator_grants_active_role_uq
    ON public.platform_operator_grants (user_id, role_code)
    WHERE status IN ('PENDING', 'ACTIVE');

CREATE INDEX platform_operator_grants_actor_lookup_idx
    ON public.platform_operator_grants (user_id, status, role_code, valid_from, valid_to);

CREATE TABLE public.platform_tenant_entitlements (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL UNIQUE REFERENCES public.tenants(id) ON DELETE RESTRICT,
    plan_code varchar(32) NOT NULL DEFAULT 'UNASSIGNED'
        CHECK (plan_code IN ('UNASSIGNED', 'STARTER', 'BUSINESS', 'ENTERPRISE', 'CUSTOM')),
    entitlement_status varchar(24) NOT NULL DEFAULT 'REVIEW_REQUIRED'
        CHECK (entitlement_status IN ('REVIEW_REQUIRED', 'ACTIVE', 'SUSPENDED', 'EXPIRED')),
    status_before_tenant_suspend varchar(24) NULL
        CHECK (status_before_tenant_suspend IS NULL OR status_before_tenant_suspend IN ('REVIEW_REQUIRED', 'ACTIVE', 'EXPIRED')),
    feature_flags jsonb NOT NULL DEFAULT '{}'::jsonb
        CHECK (jsonb_typeof(feature_flags) = 'object'),
    limits jsonb NOT NULL DEFAULT '{}'::jsonb
        CHECK (jsonb_typeof(limits) = 'object'),
    valid_from date NULL,
    valid_to date NULL,
    updated_by uuid NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    update_reason varchar(500) NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    CHECK (valid_to IS NULL OR valid_from IS NULL OR valid_to >= valid_from)
);

CREATE TABLE public.platform_audit_events (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    actor_user_id uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
    tenant_id uuid NULL REFERENCES public.tenants(id) ON DELETE RESTRICT,
    action varchar(100) NOT NULL,
    object_type varchar(100) NOT NULL,
    object_id uuid NULL,
    before_state jsonb NOT NULL DEFAULT '{}'::jsonb,
    after_state jsonb NOT NULL DEFAULT '{}'::jsonb,
    reason varchar(500) NOT NULL,
    correlation_id uuid NOT NULL DEFAULT gen_random_uuid(),
    occurred_at timestamptz NOT NULL DEFAULT now(),
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX platform_audit_events_tenant_time_idx
    ON public.platform_audit_events (tenant_id, occurred_at DESC);
CREATE INDEX platform_audit_events_actor_time_idx
    ON public.platform_audit_events (actor_user_id, occurred_at DESC);

-- Existing tenants receive an explicit unassigned/review-required entitlement.
-- No commercial plan, feature flag or licence is guessed during migration.
INSERT INTO public.platform_tenant_entitlements (tenant_id, plan_code, entitlement_status)
SELECT id, 'UNASSIGNED', 'REVIEW_REQUIRED'
FROM public.tenants
ON CONFLICT (tenant_id) DO NOTHING;

CREATE OR REPLACE FUNCTION public.create_default_platform_entitlement()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
BEGIN
    INSERT INTO public.platform_tenant_entitlements (tenant_id, plan_code, entitlement_status)
    VALUES (NEW.id, 'UNASSIGNED', 'REVIEW_REQUIRED')
    ON CONFLICT (tenant_id) DO NOTHING;
    RETURN NEW;
END;
$$;

CREATE TRIGGER tenants_create_default_platform_entitlement
AFTER INSERT ON public.tenants
FOR EACH ROW
EXECUTE FUNCTION public.create_default_platform_entitlement();

-- Global operator grants are self-readable only; there is no application write
-- path. Initial and subsequent grants are provisioned by the DBA after separate
-- approval, never by a tenant owner or by the operator's own HTTP request.
ALTER TABLE public.platform_operator_grants ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.platform_operator_grants FORCE ROW LEVEL SECURITY;
CREATE POLICY platform_operator_self_read
ON public.platform_operator_grants
FOR SELECT
USING (user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid);

ALTER TABLE public.platform_tenant_entitlements ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.platform_tenant_entitlements FORCE ROW LEVEL SECURITY;
CREATE POLICY platform_entitlement_current_tenant_read
ON public.platform_tenant_entitlements
FOR SELECT
USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid);

ALTER TABLE public.platform_audit_events ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.platform_audit_events FORCE ROW LEVEL SECURITY;
-- No direct runtime table grants or policy are provided for platform audit.
REVOKE ALL ON public.platform_operator_grants, public.platform_tenant_entitlements, public.platform_audit_events FROM PUBLIC, app_runtime;
GRANT SELECT ON public.platform_operator_grants, public.platform_tenant_entitlements TO app_runtime;

CREATE OR REPLACE FUNCTION public.require_platform_operator(required_role text)
RETURNS uuid
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    v_user_active boolean;
BEGIN
    IF v_actor_id IS NULL THEN
        RAISE EXCEPTION 'Authenticated platform operator context is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT status = 'ACTIVE'
      INTO v_user_active
      FROM public.users
     WHERE id = v_actor_id;

    IF v_user_active IS DISTINCT FROM true THEN
        RAISE EXCEPTION 'Platform operator identity is not active.'
            USING ERRCODE = '42501';
    END IF;

    IF NOT EXISTS (
        SELECT 1
          FROM public.platform_operator_grants grant_record
         WHERE grant_record.user_id = v_actor_id
           AND grant_record.role_code = required_role
           AND grant_record.status = 'ACTIVE'
           AND grant_record.valid_from <= CURRENT_DATE
           AND (grant_record.valid_to IS NULL OR grant_record.valid_to >= CURRENT_DATE)
    ) THEN
        RAISE EXCEPTION 'Required platform operator role is not assigned.'
            USING ERRCODE = '42501';
    END IF;

    RETURN v_actor_id;
END;
$$;

REVOKE ALL ON FUNCTION public.require_platform_operator(text) FROM PUBLIC;

CREATE OR REPLACE FUNCTION public.platform_list_tenants()
RETURNS TABLE (
    tenant_id uuid,
    tenant_code varchar,
    tenant_name varchar,
    tenant_status varchar,
    plan_code varchar,
    entitlement_status varchar,
    valid_from date,
    valid_to date,
    lifecycle_version bigint
)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_actor_id uuid;
BEGIN
    BEGIN
        v_actor_id := public.require_platform_operator('PLATFORM_ADMIN');
    EXCEPTION WHEN insufficient_privilege THEN
        v_actor_id := public.require_platform_operator('PLATFORM_SUPPORT');
    END;

    -- Opening the global tenant directory is privileged metadata access.
    -- Retain an immutable read event for both administrator and read-only support.
    INSERT INTO public.platform_audit_events (
        actor_user_id, tenant_id, action, object_type, object_id,
        before_state, after_state, reason
    ) VALUES (
        v_actor_id, NULL, 'PLATFORM_TENANT_REGISTER_VIEWED',
        'PLATFORM_TENANT_REGISTER', NULL, '{}'::jsonb,
        jsonb_build_object('tenant_count', (SELECT COUNT(*) FROM public.tenants)),
        'Platform tenant register read through the authorized platform API.'
    );

    RETURN QUERY
    SELECT tenant.id, tenant.code, tenant.name, tenant.status,
           entitlement.plan_code, entitlement.entitlement_status,
           entitlement.valid_from, entitlement.valid_to, tenant.lifecycle_version
      FROM public.tenants tenant
      LEFT JOIN public.platform_tenant_entitlements entitlement
        ON entitlement.tenant_id = tenant.id
     ORDER BY tenant.code;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_change_tenant_status(
    target_tenant_id uuid,
    target_status text,
    action_reason text
)
RETURNS TABLE (tenant_id uuid, previous_status varchar, current_status varchar, lifecycle_version bigint)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_actor_id uuid;
    v_before_status varchar;
    v_entitlement_before varchar;
    v_now timestamptz := clock_timestamp();
    v_correlation_id uuid := gen_random_uuid();
BEGIN
    v_actor_id := public.require_platform_operator('PLATFORM_ADMIN');

    IF target_status NOT IN ('ACTIVE', 'SUSPENDED', 'CLOSED') THEN
        RAISE EXCEPTION 'Tenant lifecycle target must be ACTIVE, SUSPENDED or CLOSED.'
            USING ERRCODE = '22023';
    END IF;
    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 500 THEN
        RAISE EXCEPTION 'A tenant lifecycle reason of 10 to 500 characters is required.'
            USING ERRCODE = '22023';
    END IF;

    SELECT tenant.status INTO v_before_status
      FROM public.tenants tenant
     WHERE tenant.id = target_tenant_id
     FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Tenant was not found.'
            USING ERRCODE = 'P0002';
    END IF;
    IF v_before_status = target_status THEN
        RAISE EXCEPTION 'Tenant already has the requested lifecycle status.'
            USING ERRCODE = '22023';
    END IF;
    IF v_before_status = 'CLOSED' THEN
        RAISE EXCEPTION 'A CLOSED tenant cannot be reactivated; use a formally approved recovery process.'
            USING ERRCODE = '42501';
    END IF;
    IF v_before_status = 'ACTIVE' AND target_status NOT IN ('SUSPENDED', 'CLOSED')
       OR v_before_status = 'SUSPENDED' AND target_status NOT IN ('ACTIVE', 'CLOSED') THEN
        RAISE EXCEPTION 'Tenant lifecycle transition is not allowed.'
            USING ERRCODE = '22023';
    END IF;

    SELECT entitlement.entitlement_status INTO v_entitlement_before
      FROM public.platform_tenant_entitlements entitlement
     WHERE entitlement.tenant_id = target_tenant_id
     FOR UPDATE;

    UPDATE public.tenants AS tenant
       SET status = target_status,
           status_changed_at = v_now,
           status_change_reason = left(btrim(action_reason), 500),
           closed_at = CASE WHEN target_status = 'CLOSED' THEN v_now ELSE tenant.closed_at END,
           lifecycle_version = tenant.lifecycle_version + 1,
           updated_at = v_now
     WHERE tenant.id = target_tenant_id;

    IF target_status IN ('SUSPENDED', 'CLOSED') THEN
        UPDATE public.platform_tenant_entitlements AS entitlement
           SET status_before_tenant_suspend = CASE
                   WHEN entitlement.entitlement_status = 'SUSPENDED' THEN entitlement.status_before_tenant_suspend
                   ELSE entitlement.entitlement_status
               END,
               entitlement_status = 'SUSPENDED',
               updated_by = v_actor_id,
               update_reason = left(btrim(action_reason), 500),
               updated_at = v_now
         WHERE entitlement.tenant_id = target_tenant_id;
    ELSE
        UPDATE public.platform_tenant_entitlements AS entitlement
           SET entitlement_status = COALESCE(entitlement.status_before_tenant_suspend, 'REVIEW_REQUIRED'),
               status_before_tenant_suspend = NULL,
               updated_by = v_actor_id,
               update_reason = left(btrim(action_reason), 500),
               updated_at = v_now
         WHERE entitlement.tenant_id = target_tenant_id;
    END IF;

    INSERT INTO public.platform_audit_events (
        actor_user_id, tenant_id, action, object_type, object_id,
        before_state, after_state, reason, correlation_id, occurred_at
    ) VALUES (
        v_actor_id, target_tenant_id, 'TENANT_LIFECYCLE_CHANGED', 'TENANT', target_tenant_id,
        jsonb_build_object('status', v_before_status, 'entitlement_status', v_entitlement_before),
        jsonb_build_object('status', target_status, 'lifecycle_version', (
            SELECT tenant.lifecycle_version
              FROM public.tenants tenant
             WHERE tenant.id = target_tenant_id
        )),
        left(btrim(action_reason), 500), v_correlation_id, v_now
    );

    RETURN QUERY
    SELECT tenant.id, v_before_status, tenant.status, tenant.lifecycle_version
      FROM public.tenants tenant
     WHERE tenant.id = target_tenant_id;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_set_tenant_entitlements(
    target_tenant_id uuid,
    target_plan_code text,
    target_entitlement_status text,
    target_feature_flags jsonb,
    target_limits jsonb,
    effective_from date,
    effective_to date,
    action_reason text
)
RETURNS TABLE (tenant_id uuid, plan_code varchar, entitlement_status varchar, valid_from date, valid_to date)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_actor_id uuid;
    v_tenant_status varchar;
    v_before jsonb;
    v_after jsonb;
    v_now timestamptz := clock_timestamp();
BEGIN
    v_actor_id := public.require_platform_operator('PLATFORM_ADMIN');

    IF target_plan_code NOT IN ('STARTER', 'BUSINESS', 'ENTERPRISE', 'CUSTOM') THEN
        RAISE EXCEPTION 'Plan code is invalid.'
            USING ERRCODE = '22023';
    END IF;
    IF target_entitlement_status NOT IN ('ACTIVE', 'REVIEW_REQUIRED', 'SUSPENDED', 'EXPIRED') THEN
        RAISE EXCEPTION 'Entitlement status is invalid.'
            USING ERRCODE = '22023';
    END IF;
    IF jsonb_typeof(target_feature_flags) <> 'object' OR jsonb_typeof(target_limits) <> 'object' THEN
        RAISE EXCEPTION 'Feature flags and limits must be JSON objects.'
            USING ERRCODE = '22023';
    END IF;
    IF EXISTS (
        SELECT 1 FROM jsonb_each(target_feature_flags)
        WHERE jsonb_typeof(value) <> 'boolean'
    ) THEN
        RAISE EXCEPTION 'Each feature flag value must be boolean.'
            USING ERRCODE = '22023';
    END IF;
    IF EXISTS (
        SELECT 1 FROM jsonb_each(target_limits)
        WHERE jsonb_typeof(value) <> 'number'
           OR (value #>> '{}')::numeric < 0
           OR trunc((value #>> '{}')::numeric) <> (value #>> '{}')::numeric
    ) THEN
        RAISE EXCEPTION 'Each entitlement limit must be a non-negative integer.'
            USING ERRCODE = '22023';
    END IF;
    IF effective_to IS NOT NULL AND (effective_from IS NULL OR effective_to < effective_from) THEN
        RAISE EXCEPTION 'Entitlement effective dates are invalid.'
            USING ERRCODE = '22023';
    END IF;
    IF target_entitlement_status = 'ACTIVE'
       AND (effective_from IS NULL OR effective_from > CURRENT_DATE
            OR (effective_to IS NOT NULL AND effective_to < CURRENT_DATE)) THEN
        RAISE EXCEPTION 'Active entitlements must be effective on the current date.'
            USING ERRCODE = '22023';
    END IF;
    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 500 THEN
        RAISE EXCEPTION 'An entitlement change reason of 10 to 500 characters is required.'
            USING ERRCODE = '22023';
    END IF;

    SELECT tenant.status INTO v_tenant_status
      FROM public.tenants tenant
     WHERE tenant.id = target_tenant_id
     FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Tenant was not found.'
            USING ERRCODE = 'P0002';
    END IF;
    IF v_tenant_status <> 'ACTIVE' AND target_entitlement_status = 'ACTIVE' THEN
        RAISE EXCEPTION 'An inactive tenant cannot receive active entitlements.'
            USING ERRCODE = '22023';
    END IF;

    SELECT jsonb_build_object(
        'plan_code', entitlement.plan_code,
        'entitlement_status', entitlement.entitlement_status,
        'feature_flags', entitlement.feature_flags,
        'limits', entitlement.limits,
        'valid_from', entitlement.valid_from,
        'valid_to', entitlement.valid_to
    ) INTO v_before
      FROM public.platform_tenant_entitlements entitlement
     WHERE entitlement.tenant_id = target_tenant_id
     FOR UPDATE;

    INSERT INTO public.platform_tenant_entitlements (
        tenant_id, plan_code, entitlement_status, feature_flags, limits,
        valid_from, valid_to, updated_by, update_reason, updated_at
    ) VALUES (
        target_tenant_id, target_plan_code, target_entitlement_status, target_feature_flags, target_limits,
        effective_from, effective_to, v_actor_id, left(btrim(action_reason), 500), v_now
    )
    ON CONFLICT ON CONSTRAINT platform_tenant_entitlements_tenant_id_key DO UPDATE
       SET plan_code = EXCLUDED.plan_code,
           entitlement_status = EXCLUDED.entitlement_status,
           status_before_tenant_suspend = NULL,
           feature_flags = EXCLUDED.feature_flags,
           limits = EXCLUDED.limits,
           valid_from = EXCLUDED.valid_from,
           valid_to = EXCLUDED.valid_to,
           updated_by = EXCLUDED.updated_by,
           update_reason = EXCLUDED.update_reason,
           updated_at = EXCLUDED.updated_at;

    SELECT jsonb_build_object(
        'plan_code', entitlement.plan_code,
        'entitlement_status', entitlement.entitlement_status,
        'feature_flags', entitlement.feature_flags,
        'limits', entitlement.limits,
        'valid_from', entitlement.valid_from,
        'valid_to', entitlement.valid_to
    ) INTO v_after
      FROM public.platform_tenant_entitlements entitlement
     WHERE entitlement.tenant_id = target_tenant_id;

    INSERT INTO public.platform_audit_events (
        actor_user_id, tenant_id, action, object_type, object_id,
        before_state, after_state, reason, occurred_at
    ) VALUES (
        v_actor_id, target_tenant_id, 'TENANT_ENTITLEMENTS_CHANGED', 'TENANT_ENTITLEMENT',
        target_tenant_id, COALESCE(v_before, '{}'::jsonb), v_after,
        left(btrim(action_reason), 500), v_now
    );

    RETURN QUERY
    SELECT entitlement.tenant_id, entitlement.plan_code, entitlement.entitlement_status,
           entitlement.valid_from, entitlement.valid_to
      FROM public.platform_tenant_entitlements entitlement
     WHERE entitlement.tenant_id = target_tenant_id;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_get_current_entitlement(target_tenant_id uuid)
RETURNS TABLE (
    tenant_id uuid,
    plan_code varchar,
    entitlement_status varchar,
    feature_flags jsonb,
    limits jsonb,
    valid_from date,
    valid_to date
)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_context_tenant uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
BEGIN
    IF v_context_tenant IS NULL OR v_context_tenant <> target_tenant_id THEN
        RAISE EXCEPTION 'A matching tenant context is required.'
            USING ERRCODE = '42501';
    END IF;

    RETURN QUERY
    SELECT entitlement.tenant_id, entitlement.plan_code, entitlement.entitlement_status,
           entitlement.feature_flags, entitlement.limits, entitlement.valid_from, entitlement.valid_to
      FROM public.platform_tenant_entitlements entitlement
     WHERE entitlement.tenant_id = target_tenant_id;
END;
$$;

CREATE OR REPLACE FUNCTION public.prevent_platform_audit_mutation()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    RAISE EXCEPTION 'platform_audit_events are append-only; write a compensating event instead';
END;
$$;

CREATE TRIGGER platform_audit_events_append_only
BEFORE UPDATE OR DELETE ON public.platform_audit_events
FOR EACH ROW EXECUTE FUNCTION public.prevent_platform_audit_mutation();

REVOKE ALL ON FUNCTION public.create_default_platform_entitlement() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_list_tenants() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_change_tenant_status(uuid, text, text) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_set_tenant_entitlements(uuid, text, text, jsonb, jsonb, date, date, text) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_get_current_entitlement(uuid) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.prevent_platform_audit_mutation() FROM PUBLIC;

GRANT EXECUTE ON FUNCTION public.platform_list_tenants() TO app_runtime;
GRANT EXECUTE ON FUNCTION public.platform_change_tenant_status(uuid, text, text) TO app_runtime;
GRANT EXECUTE ON FUNCTION public.platform_set_tenant_entitlements(uuid, text, text, jsonb, jsonb, date, date, text) TO app_runtime;
GRANT EXECUTE ON FUNCTION public.platform_get_current_entitlement(uuid) TO app_runtime;
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM public.platform_operator_grants)
       OR EXISTS (
          SELECT 1 FROM public.platform_tenant_entitlements
           WHERE plan_code <> 'UNASSIGNED' OR entitlement_status <> 'REVIEW_REQUIRED'
       )
       OR EXISTS (SELECT 1 FROM public.platform_audit_events) THEN
        RAISE EXCEPTION 'Platform grants, configured entitlements or platform audit history exist; migration rollback is blocked to preserve governance evidence.'
            USING ERRCODE = '55000';
    END IF;
END;
$$;

REVOKE EXECUTE ON FUNCTION public.platform_list_tenants() FROM app_runtime;
REVOKE EXECUTE ON FUNCTION public.platform_change_tenant_status(uuid, text, text) FROM app_runtime;
REVOKE EXECUTE ON FUNCTION public.platform_set_tenant_entitlements(uuid, text, text, jsonb, jsonb, date, date, text) FROM app_runtime;
REVOKE EXECUTE ON FUNCTION public.platform_get_current_entitlement(uuid) FROM app_runtime;
DROP TRIGGER IF EXISTS platform_audit_events_append_only ON public.platform_audit_events;
DROP FUNCTION IF EXISTS public.prevent_platform_audit_mutation();
DROP FUNCTION IF EXISTS public.platform_get_current_entitlement(uuid);
DROP FUNCTION IF EXISTS public.platform_set_tenant_entitlements(uuid, text, text, jsonb, jsonb, date, date, text);
DROP FUNCTION IF EXISTS public.platform_change_tenant_status(uuid, text, text);
DROP FUNCTION IF EXISTS public.platform_list_tenants();
DROP FUNCTION IF EXISTS public.require_platform_operator(text);
DROP TRIGGER IF EXISTS tenants_create_default_platform_entitlement ON public.tenants;
DROP FUNCTION IF EXISTS public.create_default_platform_entitlement();
DROP TABLE public.platform_audit_events;
DROP TABLE public.platform_tenant_entitlements;
DROP TABLE public.platform_operator_grants;
ALTER TABLE public.tenants
    DROP COLUMN lifecycle_version,
    DROP COLUMN closed_at,
    DROP COLUMN status_change_reason,
    DROP COLUMN status_changed_at;
SQL);
    }
};
