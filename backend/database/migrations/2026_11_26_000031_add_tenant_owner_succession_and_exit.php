<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Tenant ownership succession requires PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
DO $$
DECLARE
    v_invalid_count bigint;
BEGIN
    SELECT COUNT(*)
      INTO v_invalid_count
      FROM public.tenants tenant
     WHERE (
        SELECT COUNT(*)
          FROM public.tenant_memberships membership
         WHERE membership.tenant_id = tenant.id
           AND membership.status = 'ACTIVE'
           AND membership.membership_role = 'OWNER'
     ) <> 1;

    IF v_invalid_count > 0 THEN
        RAISE EXCEPTION 'Cannot enable owner invariant: % existing tenants do not have exactly one active OWNER membership.', v_invalid_count
            USING ERRCODE = '23514';
    END IF;
END;
$$;

CREATE UNIQUE INDEX tenant_memberships_single_active_owner_uq
    ON public.tenant_memberships (tenant_id)
    WHERE status = 'ACTIVE' AND membership_role = 'OWNER';

CREATE OR REPLACE FUNCTION public.assert_tenant_has_one_active_owner(target_tenant_id uuid)
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_owner_count bigint;
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM public.tenants WHERE id = target_tenant_id
    ) THEN
        RETURN;
    END IF;

    SELECT COUNT(*)
      INTO v_owner_count
      FROM public.tenant_memberships
     WHERE tenant_id = target_tenant_id
       AND status = 'ACTIVE'
       AND membership_role = 'OWNER';

    IF v_owner_count <> 1 THEN
        RAISE EXCEPTION 'Every existing tenant must have exactly one active OWNER membership; tenant % has %.',
            target_tenant_id, v_owner_count
            USING ERRCODE = '23514';
    END IF;
END;
$$;

REVOKE ALL ON FUNCTION public.assert_tenant_has_one_active_owner(uuid) FROM PUBLIC;

CREATE OR REPLACE FUNCTION public.check_tenant_membership_owner_invariant()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_tenant_id uuid;
BEGIN
    IF TG_OP = 'DELETE' THEN
        v_tenant_id := OLD.tenant_id;
    ELSE
        v_tenant_id := NEW.tenant_id;
    END IF;

    PERFORM public.assert_tenant_has_one_active_owner(v_tenant_id);
    RETURN NULL;
END;
$$;

CREATE OR REPLACE FUNCTION public.check_new_tenant_owner_invariant()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
BEGIN
    PERFORM public.assert_tenant_has_one_active_owner(NEW.id);
    RETURN NULL;
END;
$$;

REVOKE ALL ON FUNCTION public.check_tenant_membership_owner_invariant() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.check_new_tenant_owner_invariant() FROM PUBLIC;

CREATE CONSTRAINT TRIGGER tenant_memberships_exactly_one_active_owner
AFTER INSERT OR UPDATE OR DELETE ON public.tenant_memberships
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE FUNCTION public.check_tenant_membership_owner_invariant();

CREATE CONSTRAINT TRIGGER tenants_created_with_active_owner
AFTER INSERT ON public.tenants
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE FUNCTION public.check_new_tenant_owner_invariant();

CREATE OR REPLACE FUNCTION public.transfer_tenant_ownership(
    target_user_id uuid,
    action_reason text
)
RETURNS uuid
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
    v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    v_actor_membership_id uuid;
    v_target_membership_id uuid;
    v_actor_role text;
    v_target_role text;
    v_membership record;
    v_correlation_id uuid := gen_random_uuid();
    v_now timestamptz := clock_timestamp();
BEGIN
    IF v_tenant_id IS NULL OR v_actor_id IS NULL THEN
        RAISE EXCEPTION 'A verified tenant and actor context is required.'
            USING ERRCODE = '42501';
    END IF;

    IF target_user_id IS NULL OR target_user_id = v_actor_id THEN
        RAISE EXCEPTION 'Ownership must be transferred to a different active tenant member.'
            USING ERRCODE = '22023';
    END IF;

    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 2000 THEN
        RAISE EXCEPTION 'An ownership-transfer reason of 10 to 2000 characters is required.'
            USING ERRCODE = '22023';
    END IF;

    -- Serialize all ownership succession operations for this tenant.
    PERFORM pg_advisory_xact_lock(
        hashtextextended(v_tenant_id::text || ':owner-succession', 0)
    );

    -- Lock the actor and target membership/identity rows in one stable order.
    FOR v_membership IN
        SELECT membership.id, membership.user_id, membership.membership_role
          FROM public.tenant_memberships membership
          JOIN public.users identity ON identity.id = membership.user_id
         WHERE membership.tenant_id = v_tenant_id
           AND membership.user_id IN (v_actor_id, target_user_id)
           AND membership.status = 'ACTIVE'
           AND identity.status = 'ACTIVE'
         ORDER BY membership.user_id
         FOR UPDATE OF membership, identity
    LOOP
        IF v_membership.user_id = v_actor_id THEN
            v_actor_membership_id := v_membership.id;
            v_actor_role := v_membership.membership_role;
        ELSIF v_membership.user_id = target_user_id THEN
            v_target_membership_id := v_membership.id;
            v_target_role := v_membership.membership_role;
        END IF;
    END LOOP;

    IF v_actor_membership_id IS NULL OR v_actor_role IS DISTINCT FROM 'OWNER' THEN
        RAISE EXCEPTION 'Only the current active tenant OWNER may transfer ownership.'
            USING ERRCODE = '42501';
    END IF;

    IF v_target_membership_id IS NULL OR v_target_role NOT IN ('MEMBER', 'ADMIN') THEN
        RAISE EXCEPTION 'Ownership may only be transferred to an active MEMBER or ADMIN of the same tenant.'
            USING ERRCODE = '22023';
    END IF;

    -- Demote the current owner first to satisfy the immediate unique partial
    -- index. Both mutations remain atomic in this same transaction, while the
    -- deferred constraint trigger checks that one owner exists at commit.
    UPDATE public.tenant_memberships
       SET membership_role = 'ADMIN',
           updated_at = v_now
     WHERE tenant_id = v_tenant_id
       AND id = v_actor_membership_id
       AND status = 'ACTIVE'
       AND membership_role = 'OWNER';

    IF NOT FOUND THEN
        RAISE EXCEPTION 'The current owner membership changed during succession; retry the request.'
            USING ERRCODE = '40001';
    END IF;

    UPDATE public.tenant_memberships
       SET membership_role = 'OWNER',
           updated_at = v_now
     WHERE tenant_id = v_tenant_id
       AND id = v_target_membership_id
       AND status = 'ACTIVE'
       AND membership_role = v_target_role;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'The proposed successor membership changed during succession; retry the request.'
            USING ERRCODE = '40001';
    END IF;

    INSERT INTO public.audit_events (
        id, tenant_id, actor_user_id, action, object_type, object_id,
        scope, reason, correlation_id, occurred_at, created_at, updated_at
    ) VALUES (
        gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_OWNERSHIP_TRANSFERRED',
        'TENANT', v_tenant_id,
        jsonb_build_object(
            'previous_owner_user_id', v_actor_id,
            'previous_owner_membership_id', v_actor_membership_id,
            'new_owner_user_id', target_user_id,
            'new_owner_membership_id', v_target_membership_id,
            'previous_owner_new_role', 'ADMIN',
            'new_owner_previous_role', v_target_role
        ),
        left(btrim(action_reason), 500), v_correlation_id, v_now, v_now, v_now
    );

    RETURN v_target_membership_id;
END;
$$;

CREATE OR REPLACE FUNCTION public.leave_tenant(
    action_reason text
)
RETURNS uuid
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
    v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    v_membership_id uuid;
    v_membership_role text;
    v_now timestamptz := clock_timestamp();
BEGIN
    IF v_tenant_id IS NULL OR v_actor_id IS NULL THEN
        RAISE EXCEPTION 'A verified tenant and actor context is required.'
            USING ERRCODE = '42501';
    END IF;

    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 2000 THEN
        RAISE EXCEPTION 'A leave reason of 10 to 2000 characters is required.'
            USING ERRCODE = '22023';
    END IF;

    -- Serialize with owner succession so a concurrent owner transfer/leave
    -- cannot create an ownerless tenant.
    PERFORM pg_advisory_xact_lock(
        hashtextextended(v_tenant_id::text || ':owner-succession', 0)
    );

    SELECT id, membership_role
      INTO v_membership_id, v_membership_role
      FROM public.tenant_memberships
     WHERE tenant_id = v_tenant_id
       AND user_id = v_actor_id
       AND status = 'ACTIVE'
     FOR UPDATE;

    IF v_membership_id IS NULL THEN
        RAISE EXCEPTION 'An active tenant membership was not found.'
            USING ERRCODE = 'P0002';
    END IF;

    IF v_membership_role = 'OWNER' THEN
        RAISE EXCEPTION 'Transfer tenant ownership to another active member before leaving.'
            USING ERRCODE = '42501';
    END IF;

    UPDATE public.tenant_memberships
       SET status = 'REVOKED',
           revoked_at = v_now,
           updated_at = v_now
     WHERE tenant_id = v_tenant_id
       AND id = v_membership_id
       AND status = 'ACTIVE';

    IF NOT FOUND THEN
        RAISE EXCEPTION 'The tenant membership changed while leaving; retry the request.'
            USING ERRCODE = '40001';
    END IF;

    INSERT INTO public.audit_events (
        id, tenant_id, actor_user_id, action, object_type, object_id,
        scope, reason, correlation_id, occurred_at, created_at, updated_at
    ) VALUES (
        gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_MEMBER_LEFT',
        'TENANT_MEMBERSHIP', v_membership_id,
        jsonb_build_object('user_id', v_actor_id, 'previous_role', v_membership_role),
        left(btrim(action_reason), 500), gen_random_uuid(), v_now, v_now, v_now
    );

    RETURN v_membership_id;
END;
$$;

REVOKE ALL ON FUNCTION public.transfer_tenant_ownership(uuid, text) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.leave_tenant(text) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.transfer_tenant_ownership(uuid, text) TO app_runtime;
GRANT EXECUTE ON FUNCTION public.leave_tenant(text) TO app_runtime;
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
REVOKE EXECUTE ON FUNCTION public.transfer_tenant_ownership(uuid, text) FROM app_runtime;
REVOKE EXECUTE ON FUNCTION public.leave_tenant(text) FROM app_runtime;
DROP FUNCTION IF EXISTS public.transfer_tenant_ownership(uuid, text);
DROP FUNCTION IF EXISTS public.leave_tenant(text);
DROP TRIGGER IF EXISTS tenants_created_with_active_owner ON public.tenants;
DROP TRIGGER IF EXISTS tenant_memberships_exactly_one_active_owner ON public.tenant_memberships;
DROP FUNCTION IF EXISTS public.check_new_tenant_owner_invariant();
DROP FUNCTION IF EXISTS public.check_tenant_membership_owner_invariant();
DROP FUNCTION IF EXISTS public.assert_tenant_has_one_active_owner(uuid);
DROP INDEX IF EXISTS public.tenant_memberships_single_active_owner_uq;
SQL);
    }
};
