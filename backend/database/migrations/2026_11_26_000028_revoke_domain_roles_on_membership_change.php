<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Domain-role lifecycle enforcement requires PostgreSQL.'); 
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.revoke_domain_roles_on_membership_change()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
    v_reason text;
    v_reason_origin text;
    v_assignment record;
    v_now timestamptz := clock_timestamp();
BEGIN
    IF OLD.status = 'ACTIVE' AND NEW.status <> 'ACTIVE' THEN
        v_reason_origin := 'TENANT_MEMBERSHIP_REVOKED';
        v_reason := NULLIF(btrim(current_setting('app.current_action_reason', true)), '');
        IF v_reason IS NULL THEN
            v_reason := 'Tenant membership revoked; domain roles ended.';
        END IF;
    ELSIF OLD.membership_role = 'MEMBER'
       AND NEW.membership_role IN ('ADMIN', 'OWNER') THEN
        v_reason_origin := 'TENANT_MEMBERSHIP_PROMOTED';
        v_reason := NULLIF(btrim(current_setting('app.current_action_reason', true)), '');
        IF v_reason IS NULL THEN
            v_reason := 'Tenant membership promoted; member domain roles ended.';
        END IF;
    ELSE
        RETURN NEW;
    END IF;

    IF v_actor_id IS NULL THEN
        RAISE EXCEPTION 'A verified actor context is required to revoke tenant domain roles.'
            USING ERRCODE = '42501';
    END IF;

    -- Membership lifecycle transitions must not leave privileged domain grants
    -- dormant and later effective again after reactivation or role demotion.
    FOR v_assignment IN
        SELECT id, role_code
        FROM public.tenant_domain_role_assignments
        WHERE tenant_id = NEW.tenant_id
          AND user_id = NEW.user_id
          AND status IN ('PENDING', 'ACTIVE')
        ORDER BY id
        FOR UPDATE
    LOOP
        UPDATE public.tenant_domain_role_assignments
        SET status = 'REVOKED',
            revoked_by = v_actor_id,
            revoked_at = v_now,
            revocation_reason = v_reason,
            updated_at = v_now
        WHERE tenant_id = NEW.tenant_id
          AND id = v_assignment.id;

        INSERT INTO public.audit_events (
            id,
            tenant_id,
            actor_user_id,
            action,
            object_type,
            object_id,
            scope,
            reason,
            correlation_id,
            occurred_at,
            created_at,
            updated_at
        ) VALUES (
            gen_random_uuid(),
            NEW.tenant_id,
            v_actor_id,
            'TENANT_DOMAIN_ROLE_REVOKED',
            'TENANT_DOMAIN_ROLE_ASSIGNMENT',
            v_assignment.id,
            jsonb_build_object(
                'user_id', NEW.user_id,
                'role_code', v_assignment.role_code,
                'reason_origin', v_reason_origin
            ),
            left(v_reason, 500),
            gen_random_uuid(),
            v_now,
            v_now,
            v_now
        );
    END LOOP;

    RETURN NEW;
END;
$$;

REVOKE ALL ON FUNCTION public.revoke_domain_roles_on_membership_change() FROM PUBLIC;

CREATE TRIGGER tenant_membership_revoke_domain_roles
AFTER UPDATE OF status, membership_role ON public.tenant_memberships
FOR EACH ROW
EXECUTE FUNCTION public.revoke_domain_roles_on_membership_change();
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS tenant_membership_revoke_domain_roles ON public.tenant_memberships;
DROP FUNCTION IF EXISTS public.revoke_domain_roles_on_membership_change();
SQL);
        }
    }
};
