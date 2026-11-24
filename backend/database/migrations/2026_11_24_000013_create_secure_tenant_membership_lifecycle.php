<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Tenant membership lifecycle requires PostgreSQL.');
        }

        // These functions are owned by the controlled migration role. The API
        // runtime receives EXECUTE only; it never receives direct membership
        // INSERT/UPDATE/DELETE privileges. Each function validates the current
        // transaction-local tenant and actor and writes an immutable audit event.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.add_tenant_member(
                target_user_id uuid,
                requested_role text,
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
                v_actor_role text;
                v_target_status text;
                v_existing_id uuid;
                v_existing_role text;
                v_existing_status text;
                v_membership_id uuid;
                v_action text;
            BEGIN
                IF v_tenant_id IS NULL OR v_actor_id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'A verified tenant and actor context is required.';
                END IF;

                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id
                  AND user_id = v_actor_id
                  AND status = 'ACTIVE'
                FOR SHARE;

                IF v_actor_role IS NULL OR v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;

                IF requested_role NOT IN ('MEMBER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'Only MEMBER or ADMIN can be assigned through member administration.';
                END IF;

                IF v_actor_role = 'ADMIN' AND requested_role <> 'MEMBER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'An administrator may add members but only an owner may grant administrator access.';
                END IF;

                IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 2000 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'A reason of 10 to 2000 characters is required.';
                END IF;

                SELECT status INTO v_target_status
                FROM public.users
                WHERE id = target_user_id
                FOR SHARE;

                IF v_target_status IS DISTINCT FROM 'ACTIVE' THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'An active global user account was not found.';
                END IF;

                PERFORM pg_advisory_xact_lock(
                    hashtextextended(v_tenant_id::text || ':membership:' || target_user_id::text, 0)
                );

                SELECT id, membership_role, status
                INTO v_existing_id, v_existing_role, v_existing_status
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id
                  AND user_id = target_user_id
                FOR UPDATE;

                IF v_existing_status = 'ACTIVE' THEN
                    RAISE EXCEPTION USING ERRCODE = '23505', MESSAGE = 'This user is already an active member of the selected tenant.';
                END IF;

                IF v_existing_role = 'OWNER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'A revoked owner cannot be reactivated through ordinary member administration.';
                END IF;

                IF v_actor_role = 'ADMIN' AND v_existing_role IS NOT NULL AND v_existing_role <> 'MEMBER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'An administrator cannot reactivate or change another administrator membership.';
                END IF;

                IF v_existing_id IS NULL THEN
                    v_membership_id := gen_random_uuid();
                    INSERT INTO public.tenant_memberships (
                        id, tenant_id, user_id, membership_role, status, joined_at, revoked_at, created_at, updated_at
                    ) VALUES (
                        v_membership_id, v_tenant_id, target_user_id, requested_role, 'ACTIVE', clock_timestamp(), NULL,
                        clock_timestamp(), clock_timestamp()
                    );
                    v_action := 'TENANT_MEMBER_ADDED';
                ELSE
                    v_membership_id := v_existing_id;
                    UPDATE public.tenant_memberships
                    SET membership_role = requested_role,
                        status = 'ACTIVE',
                        joined_at = clock_timestamp(),
                        revoked_at = NULL,
                        updated_at = clock_timestamp()
                    WHERE tenant_id = v_tenant_id
                      AND id = v_existing_id;
                    v_action := 'TENANT_MEMBER_REACTIVATED';
                END IF;

                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id,
                    scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, v_action, 'TENANT_MEMBERSHIP', v_membership_id,
                    jsonb_build_object('target_user_id', target_user_id, 'membership_role', requested_role),
                    btrim(action_reason), clock_timestamp(), clock_timestamp(), clock_timestamp()
                );

                RETURN v_membership_id;
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.change_tenant_member_role(
                target_user_id uuid,
                requested_role text,
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
                v_actor_role text;
                v_membership_id uuid;
                v_current_role text;
            BEGIN
                IF v_tenant_id IS NULL OR v_actor_id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'A verified tenant and actor context is required.';
                END IF;

                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;

                IF v_actor_role IS DISTINCT FROM 'OWNER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Only the tenant owner may change administrator roles.';
                END IF;

                IF requested_role NOT IN ('MEMBER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'Only MEMBER or ADMIN may be assigned through this operation.';
                END IF;

                IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 2000 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'A reason of 10 to 2000 characters is required.';
                END IF;

                SELECT id, membership_role INTO v_membership_id, v_current_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = target_user_id AND status = 'ACTIVE'
                FOR UPDATE;

                IF v_membership_id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'An active tenant membership was not found.';
                END IF;

                IF v_current_role = 'OWNER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Owner membership is protected from ordinary role changes.';
                END IF;

                IF v_current_role = requested_role THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'The member already has the requested role.';
                END IF;

                UPDATE public.tenant_memberships
                SET membership_role = requested_role, updated_at = clock_timestamp()
                WHERE tenant_id = v_tenant_id AND id = v_membership_id;

                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id,
                    scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_MEMBER_ROLE_CHANGED',
                    'TENANT_MEMBERSHIP', v_membership_id,
                    jsonb_build_object('target_user_id', target_user_id, 'previous_role', v_current_role, 'new_role', requested_role),
                    btrim(action_reason), clock_timestamp(), clock_timestamp(), clock_timestamp()
                );

                RETURN v_membership_id;
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.revoke_tenant_member(
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
                v_actor_role text;
                v_membership_id uuid;
                v_target_role text;
            BEGIN
                IF v_tenant_id IS NULL OR v_actor_id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'A verified tenant and actor context is required.';
                END IF;

                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;

                IF v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;

                IF target_user_id = v_actor_id THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Use the separate leave-tenant process to revoke your own membership.';
                END IF;

                IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 2000 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'A reason of 10 to 2000 characters is required.';
                END IF;

                SELECT id, membership_role INTO v_membership_id, v_target_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = target_user_id AND status = 'ACTIVE'
                FOR UPDATE;

                IF v_membership_id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'An active tenant membership was not found.';
                END IF;

                IF v_target_role = 'OWNER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Owner membership cannot be revoked through ordinary member administration.';
                END IF;

                IF v_actor_role = 'ADMIN' AND v_target_role <> 'MEMBER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'An administrator may revoke ordinary members but cannot revoke another administrator.';
                END IF;

                UPDATE public.tenant_memberships
                SET status = 'REVOKED', revoked_at = clock_timestamp(), updated_at = clock_timestamp()
                WHERE tenant_id = v_tenant_id AND id = v_membership_id;

                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id,
                    scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_MEMBER_REVOKED',
                    'TENANT_MEMBERSHIP', v_membership_id,
                    jsonb_build_object('target_user_id', target_user_id, 'previous_role', v_target_role),
                    btrim(action_reason), clock_timestamp(), clock_timestamp(), clock_timestamp()
                );

                RETURN v_membership_id;
            END;
            $$;

            REVOKE ALL ON FUNCTION public.add_tenant_member(uuid, text, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.change_tenant_member_role(uuid, text, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.revoke_tenant_member(uuid, text) FROM PUBLIC;
            GRANT EXECUTE ON FUNCTION public.add_tenant_member(uuid, text, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.change_tenant_member_role(uuid, text, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.revoke_tenant_member(uuid, text) TO app_runtime;
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                REVOKE EXECUTE ON FUNCTION public.add_tenant_member(uuid, text, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.change_tenant_member_role(uuid, text, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.revoke_tenant_member(uuid, text) FROM app_runtime;
                DROP FUNCTION IF EXISTS public.revoke_tenant_member(uuid, text);
                DROP FUNCTION IF EXISTS public.change_tenant_member_role(uuid, text, text);
                DROP FUNCTION IF EXISTS public.add_tenant_member(uuid, text, text);
            SQL);
        }
    }
};
