<?php

declare(strict_types=1);

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Tenant access-scope mutation controls require PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
-- The runtime may read scope records, but it must not mutate the scope mode,
-- grant ledger or revocation history directly. All mutations go through the
-- SECURITY DEFINER functions below so authorization and audit are atomic.
REVOKE ALL ON TABLE public.tenant_access_scope_modes FROM PUBLIC, app_runtime;
REVOKE ALL ON TABLE public.tenant_access_scopes FROM PUBLIC, app_runtime;
GRANT SELECT ON TABLE public.tenant_access_scope_modes TO app_runtime;
GRANT SELECT ON TABLE public.tenant_access_scopes TO app_runtime;

CREATE OR REPLACE FUNCTION public.set_tenant_member_scope_mode(
    target_user_id uuid,
    requested_mode text,
    change_reason text
)
RETURNS TABLE (user_id uuid, scope_mode varchar)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
    actor_role text;
    target_role text;
    previous_mode varchar(16);
    normalized_mode text := upper(btrim(requested_mode));
    normalized_reason text := btrim(change_reason);
    event_time timestamptz := clock_timestamp();
BEGIN
    IF actor_id IS NULL OR tenant_id IS NULL THEN
        RAISE EXCEPTION 'Authenticated tenant and actor context is required.'
            USING ERRCODE = '42501';
    END IF;

    IF target_user_id IS NULL OR target_user_id = actor_id THEN
        RAISE EXCEPTION 'An administrator cannot change their own member scope mode.'
            USING ERRCODE = '42501';
    END IF;

    IF normalized_mode NOT IN ('TENANT_WIDE', 'SCOPED') THEN
        RAISE EXCEPTION 'Unsupported tenant access scope mode.'
            USING ERRCODE = '22023';
    END IF;

    IF normalized_reason IS NULL OR char_length(normalized_reason) < 10
       OR char_length(normalized_reason) > 500 THEN
        RAISE EXCEPTION 'Scope-mode reason must contain 10 to 500 characters.'
            USING ERRCODE = '22023';
    END IF;

    PERFORM 1 FROM public.tenants
     WHERE id = tenant_id AND status = 'ACTIVE';
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Active tenant context is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT membership.membership_role
      INTO actor_role
      FROM public.tenant_memberships membership
      JOIN public.users actor ON actor.id = membership.user_id
     WHERE membership.tenant_id = tenant_id
       AND membership.user_id = actor_id
       AND membership.status = 'ACTIVE'
       AND actor.status = 'ACTIVE'
     FOR UPDATE OF membership;

    IF NOT FOUND OR actor_role NOT IN ('OWNER', 'ADMIN') THEN
        RAISE EXCEPTION 'Tenant owner or administrator permission is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT membership.membership_role
      INTO target_role
      FROM public.tenant_memberships membership
      JOIN public.users target ON target.id = membership.user_id
     WHERE membership.tenant_id = tenant_id
       AND membership.user_id = target_user_id
       AND membership.status = 'ACTIVE'
       AND target.status = 'ACTIVE'
     FOR UPDATE OF membership;

    IF NOT FOUND OR target_role <> 'MEMBER' THEN
        RAISE EXCEPTION 'An active ordinary tenant member is required.'
            USING ERRCODE = '42501';
    END IF;

    PERFORM pg_advisory_xact_lock(
        hashtextextended(tenant_id::text || ':access-mode:' || target_user_id::text, 0)
    );

    SELECT mode_record.scope_mode
      INTO previous_mode
      FROM public.tenant_access_scope_modes mode_record
     WHERE mode_record.tenant_id = tenant_id
       AND mode_record.user_id = target_user_id
     FOR UPDATE;

    INSERT INTO public.tenant_access_scope_modes (
        id, tenant_id, user_id, scope_mode, changed_by, change_reason,
        created_at, updated_at
    ) VALUES (
        gen_random_uuid(), tenant_id, target_user_id, normalized_mode,
        actor_id, normalized_reason, event_time, event_time
    )
    ON CONFLICT (tenant_id, user_id) DO UPDATE
       SET scope_mode = EXCLUDED.scope_mode,
           changed_by = EXCLUDED.changed_by,
           change_reason = EXCLUDED.change_reason,
           updated_at = EXCLUDED.updated_at;

    INSERT INTO public.audit_events (
        id, tenant_id, actor_user_id, action, object_type, object_id,
        scope, reason, correlation_id, occurred_at, created_at, updated_at
    ) VALUES (
        gen_random_uuid(), tenant_id, actor_id,
        'TENANT_MEMBER_SCOPE_MODE_CHANGED', 'TENANT_MEMBER', target_user_id,
        jsonb_build_object(
            'previous_scope_mode', previous_mode,
            'scope_mode', normalized_mode
        ),
        normalized_reason, gen_random_uuid(), event_time, event_time, event_time
    );

    RETURN QUERY SELECT target_user_id, normalized_mode::varchar;
END;
$$;

CREATE OR REPLACE FUNCTION public.grant_tenant_access_scope(
    target_user_id uuid,
    requested_scope_type text,
    requested_scope_id uuid,
    requested_scope_code text,
    requested_permission text,
    requested_valid_from date,
    requested_valid_to date,
    grant_reason text
)
RETURNS SETOF public.tenant_access_scopes
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
    actor_role text;
    target_role text;
    normalized_type text := upper(btrim(requested_scope_type));
    normalized_permission text := upper(btrim(requested_permission));
    normalized_code text := NULLIF(upper(btrim(requested_scope_code)), '');
    normalized_reason text := btrim(grant_reason);
    effective_from date := COALESCE(requested_valid_from, CURRENT_DATE);
    effective_to date := requested_valid_to;
    new_grant public.tenant_access_scopes%ROWTYPE;
    event_time timestamptz := clock_timestamp();
BEGIN
    IF actor_id IS NULL OR tenant_id IS NULL THEN
        RAISE EXCEPTION 'Authenticated tenant and actor context is required.'
            USING ERRCODE = '42501';
    END IF;

    IF target_user_id IS NULL OR target_user_id = actor_id THEN
        RAISE EXCEPTION 'A tenant administrator cannot grant scoped access to their own identity.'
            USING ERRCODE = '42501';
    END IF;

    IF normalized_type NOT IN ('BRAND', 'SITE', 'MARKET') THEN
        RAISE EXCEPTION 'Unsupported tenant access scope type.'
            USING ERRCODE = '22023';
    END IF;

    IF normalized_permission NOT IN ('READ', 'CREATE', 'APPROVE', 'MANAGE') THEN
        RAISE EXCEPTION 'Unsupported tenant access permission.'
            USING ERRCODE = '22023';
    END IF;

    IF normalized_reason IS NULL OR char_length(normalized_reason) < 10
       OR char_length(normalized_reason) > 500 THEN
        RAISE EXCEPTION 'Access-scope grant reason must contain 10 to 500 characters.'
            USING ERRCODE = '22023';
    END IF;

    IF effective_to IS NOT NULL AND effective_to < effective_from THEN
        RAISE EXCEPTION 'Access-scope validity dates are invalid.'
            USING ERRCODE = '22023';
    END IF;

    IF actor_id IS NULL OR tenant_id IS NULL THEN
        RAISE EXCEPTION 'Authenticated tenant and actor context is required.'
            USING ERRCODE = '42501';
    END IF;

    PERFORM 1 FROM public.tenants
     WHERE id = tenant_id AND status = 'ACTIVE';
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Active tenant context is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT membership.membership_role
      INTO actor_role
      FROM public.tenant_memberships membership
      JOIN public.users actor ON actor.id = membership.user_id
     WHERE membership.tenant_id = tenant_id
       AND membership.user_id = actor_id
       AND membership.status = 'ACTIVE'
       AND actor.status = 'ACTIVE'
     FOR UPDATE OF membership;

    IF NOT FOUND OR actor_role NOT IN ('OWNER', 'ADMIN') THEN
        RAISE EXCEPTION 'Tenant owner or administrator permission is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT membership.membership_role
      INTO target_role
      FROM public.tenant_memberships membership
      JOIN public.users target ON target.id = membership.user_id
     WHERE membership.tenant_id = tenant_id
       AND membership.user_id = target_user_id
       AND membership.status = 'ACTIVE'
       AND target.status = 'ACTIVE'
     FOR UPDATE OF membership;

    IF NOT FOUND OR target_role <> 'MEMBER' THEN
        RAISE EXCEPTION 'An active ordinary tenant member is required.'
            USING ERRCODE = '42501';
    END IF;

    IF normalized_type = 'BRAND' THEN
        IF requested_scope_id IS NULL OR normalized_code IS NOT NULL
           OR NOT EXISTS (
               SELECT 1 FROM public.brands
                WHERE tenant_id = grant_tenant_access_scope.tenant_id
                  AND id = requested_scope_id
           ) THEN
            RAISE EXCEPTION 'Brand scope target must exist in this tenant.'
                USING ERRCODE = '22023';
        END IF;
    ELSIF normalized_type = 'SITE' THEN
        IF requested_scope_id IS NULL OR normalized_code IS NOT NULL
           OR NOT EXISTS (
               SELECT 1 FROM public.business_sites
                WHERE tenant_id = grant_tenant_access_scope.tenant_id
                  AND id = requested_scope_id
           ) THEN
            RAISE EXCEPTION 'Site scope target must exist in this tenant.'
                USING ERRCODE = '22023';
        END IF;
    ELSE
        IF requested_scope_id IS NOT NULL OR normalized_code IS NULL THEN
            RAISE EXCEPTION 'Market scope requires a market code and no scope ID.'
                USING ERRCODE = '22023';
        END IF;
    END IF;

    PERFORM pg_advisory_xact_lock(
        hashtextextended(
            tenant_id::text || ':access-grant:' || target_user_id::text || ':' ||
            normalized_type || ':' || COALESCE(requested_scope_id::text, normalized_code) || ':' ||
            normalized_permission,
            0
        )
    );

    IF EXISTS (
        SELECT 1
          FROM public.tenant_access_scopes existing
         WHERE existing.tenant_id = tenant_id
           AND existing.user_id = target_user_id
           AND existing.scope_type = normalized_type
           AND existing.scope_id IS NOT DISTINCT FROM requested_scope_id
           AND existing.scope_code IS NOT DISTINCT FROM normalized_code
           AND existing.permission_code = normalized_permission
           AND existing.status = 'ACTIVE'
           AND (effective_to IS NULL OR existing.valid_from <= effective_to)
           AND (existing.valid_to IS NULL OR existing.valid_to >= effective_from)
    ) THEN
        RAISE EXCEPTION 'An overlapping active access grant already exists for this member, scope and permission.'
            USING ERRCODE = '23505';
    END IF;

    INSERT INTO public.tenant_access_scopes (
        id, tenant_id, user_id, scope_type, scope_id, scope_code,
        permission_code, valid_from, valid_to, status, grant_reason,
        granted_by, revoked_by, revoked_at, revocation_reason,
        created_at, updated_at
    ) VALUES (
        gen_random_uuid(), tenant_id, target_user_id, normalized_type,
        requested_scope_id, normalized_code, normalized_permission,
        effective_from, effective_to, 'ACTIVE', normalized_reason,
        actor_id, NULL, NULL, NULL, event_time, event_time
    )
    RETURNING * INTO new_grant;

    INSERT INTO public.audit_events (
        id, tenant_id, actor_user_id, action, object_type, object_id,
        scope, reason, correlation_id, occurred_at, created_at, updated_at
    ) VALUES (
        gen_random_uuid(), tenant_id, actor_id,
        'TENANT_ACCESS_SCOPE_GRANTED', 'ACCESS_SCOPE', new_grant.id,
        jsonb_build_object(
            'user_id', target_user_id,
            'scope_type', normalized_type,
            'scope_id', requested_scope_id,
            'scope_code', normalized_code,
            'permission_code', normalized_permission,
            'valid_from', effective_from,
            'valid_to', effective_to
        ),
        normalized_reason, gen_random_uuid(), event_time, event_time, event_time
    );

    RETURN NEXT new_grant;
END;
$$;

CREATE OR REPLACE FUNCTION public.revoke_tenant_access_scope(
    grant_id uuid,
    revocation_reason text
)
RETURNS SETOF public.tenant_access_scopes
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
    actor_role text;
    normalized_reason text := btrim(revocation_reason);
    grant_record public.tenant_access_scopes%ROWTYPE;
    updated_grant public.tenant_access_scopes%ROWTYPE;
    event_time timestamptz := clock_timestamp();
BEGIN
    IF actor_id IS NULL OR tenant_id IS NULL THEN
        RAISE EXCEPTION 'Authenticated tenant and actor context is required.'
            USING ERRCODE = '42501';
    END IF;

    IF normalized_reason IS NULL OR char_length(normalized_reason) < 10
       OR char_length(normalized_reason) > 500 THEN
        RAISE EXCEPTION 'Access-scope revocation reason must contain 10 to 500 characters.'
            USING ERRCODE = '22023';
    END IF;

    PERFORM 1 FROM public.tenants
     WHERE id = tenant_id AND status = 'ACTIVE';
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Active tenant context is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT membership.membership_role
      INTO actor_role
      FROM public.tenant_memberships membership
      JOIN public.users actor ON actor.id = membership.user_id
     WHERE membership.tenant_id = tenant_id
       AND membership.user_id = actor_id
       AND membership.status = 'ACTIVE'
       AND actor.status = 'ACTIVE'
     FOR UPDATE OF membership;

    IF NOT FOUND OR actor_role NOT IN ('OWNER', 'ADMIN') THEN
        RAISE EXCEPTION 'Tenant owner or administrator permission is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT *
      INTO grant_record
      FROM public.tenant_access_scopes existing
     WHERE existing.tenant_id = tenant_id
       AND existing.id = grant_id
     FOR UPDATE;

    IF NOT FOUND OR grant_record.status <> 'ACTIVE' THEN
        RAISE EXCEPTION 'Only an active access-scope grant can be revoked.'
            USING ERRCODE = 'P0002';
    END IF;

    UPDATE public.tenant_access_scopes
       SET status = 'REVOKED',
           revoked_by = actor_id,
           revoked_at = event_time,
           revocation_reason = normalized_reason,
           updated_at = event_time
     WHERE tenant_id = tenant_id
       AND id = grant_id
    RETURNING * INTO updated_grant;

    INSERT INTO public.audit_events (
        id, tenant_id, actor_user_id, action, object_type, object_id,
        scope, reason, correlation_id, occurred_at, created_at, updated_at
    ) VALUES (
        gen_random_uuid(), tenant_id, actor_id,
        'TENANT_ACCESS_SCOPE_REVOKED', 'ACCESS_SCOPE', grant_id,
        jsonb_build_object(
            'user_id', grant_record.user_id,
            'scope_type', grant_record.scope_type,
            'scope_id', grant_record.scope_id,
            'scope_code', grant_record.scope_code,
            'permission_code', grant_record.permission_code,
            'valid_from', grant_record.valid_from,
            'valid_to', grant_record.valid_to
        ),
        normalized_reason, gen_random_uuid(), event_time, event_time, event_time
    );

    RETURN NEXT updated_grant;
END;
$$;

REVOKE ALL ON FUNCTION public.set_tenant_member_scope_mode(uuid, text, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.grant_tenant_access_scope(uuid, text, uuid, text, text, date, date, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.revoke_tenant_access_scope(uuid, text) FROM PUBLIC, app_runtime;
GRANT EXECUTE ON FUNCTION public.set_tenant_member_scope_mode(uuid, text, text) TO app_runtime;
GRANT EXECUTE ON FUNCTION public.grant_tenant_access_scope(uuid, text, uuid, text, text, date, date, text) TO app_runtime;
GRANT EXECUTE ON FUNCTION public.revoke_tenant_access_scope(uuid, text) TO app_runtime;
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
REVOKE ALL ON FUNCTION public.set_tenant_member_scope_mode(uuid, text, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.grant_tenant_access_scope(uuid, text, uuid, text, text, date, date, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.revoke_tenant_access_scope(uuid, text) FROM PUBLIC, app_runtime;
DROP FUNCTION IF EXISTS public.set_tenant_member_scope_mode(uuid, text, text);
DROP FUNCTION IF EXISTS public.grant_tenant_access_scope(uuid, text, uuid, text, text, date, date, text);
DROP FUNCTION IF EXISTS public.revoke_tenant_access_scope(uuid, text);

-- Restore the immediately preceding grant model only as part of an explicit
-- full rollback of this migration and its paired application-code change.
GRANT SELECT, INSERT, UPDATE ON TABLE public.tenant_access_scope_modes TO app_runtime;
GRANT SELECT, INSERT ON TABLE public.tenant_access_scopes TO app_runtime;
GRANT UPDATE (status, revoked_by, revoked_at, revocation_reason, updated_at)
    ON TABLE public.tenant_access_scopes TO app_runtime;
SQL);
    }
};
