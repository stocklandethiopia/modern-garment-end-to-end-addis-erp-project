<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Secure tenant invitations require PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
            CREATE TABLE public.tenant_invitations (
                id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
                tenant_id uuid NOT NULL REFERENCES public.tenants(id) ON DELETE RESTRICT,
                email_normalized varchar(254) NOT NULL,
                membership_role varchar(24) NOT NULL CHECK (membership_role IN ('MEMBER', 'ADMIN')),
                status varchar(24) NOT NULL DEFAULT 'PENDING'
                    CHECK (status IN ('PENDING', 'DELIVERY_FAILED', 'ACCEPTED', 'REVOKED', 'EXPIRED')),
                token_hash char(64) NOT NULL UNIQUE,
                invited_by uuid NOT NULL REFERENCES public.users(id) ON DELETE RESTRICT,
                accepted_by uuid NULL REFERENCES public.users(id) ON DELETE RESTRICT,
                reason varchar(2000) NOT NULL,
                expires_at timestamptz NOT NULL,
                sent_at timestamptz NULL,
                accepted_at timestamptz NULL,
                revoked_at timestamptz NULL,
                delivery_attempts integer NOT NULL DEFAULT 1 CHECK (delivery_attempts >= 1),
                last_delivery_error_code varchar(80) NULL,
                created_at timestamptz NOT NULL DEFAULT clock_timestamp(),
                updated_at timestamptz NOT NULL DEFAULT clock_timestamp(),
                CHECK (email_normalized = lower(btrim(email_normalized))),
                CHECK (length(email_normalized) BETWEEN 3 AND 254),
                CHECK (token_hash ~ '^[0-9a-f]{64}$'),
                CHECK (length(btrim(reason)) >= 10)
            );

            CREATE INDEX tenant_invitations_tenant_status_expiry_idx
                ON public.tenant_invitations(tenant_id, status, expires_at DESC);
            CREATE UNIQUE INDEX tenant_invitations_one_open_per_tenant_email_idx
                ON public.tenant_invitations(tenant_id, email_normalized)
                WHERE status IN ('PENDING', 'DELIVERY_FAILED');

            ALTER TABLE public.tenant_invitations ENABLE ROW LEVEL SECURITY;
            ALTER TABLE public.tenant_invitations FORCE ROW LEVEL SECURITY;

            CREATE POLICY tenant_invitations_tenant_admin_read
                ON public.tenant_invitations
                FOR SELECT
                USING (
                    tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                    AND EXISTS (
                        SELECT 1 FROM public.tenant_memberships m
                        WHERE m.tenant_id = tenant_invitations.tenant_id
                          AND m.user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid
                          AND m.status = 'ACTIVE'
                          AND m.membership_role IN ('OWNER', 'ADMIN')
                    )
                );

            REVOKE ALL ON public.tenant_invitations FROM PUBLIC, app_runtime;
            GRANT SELECT ON public.tenant_invitations TO app_runtime;

            CREATE OR REPLACE FUNCTION public.create_tenant_invitation(
                target_email text, requested_role text, new_token_hash text,
                invitation_expires_at timestamptz, action_reason text
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
                v_email text := lower(btrim(target_email));
                v_invitation_id uuid;
            BEGIN
                IF v_tenant_id IS NULL OR v_actor_id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'A verified tenant and actor context is required.';
                END IF;

                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;

                IF v_actor_role IS NULL OR v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;
                IF requested_role NOT IN ('MEMBER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'Only MEMBER or ADMIN invitations may be issued.';
                END IF;
                IF v_actor_role = 'ADMIN' AND requested_role <> 'MEMBER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Only the tenant owner may invite an administrator.';
                END IF;
                IF length(v_email) NOT BETWEEN 3 AND 254 OR position('@' IN v_email) < 2 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'A valid invitation email is required.';
                END IF;
                IF new_token_hash !~ '^[0-9a-f]{64}$'
                   OR invitation_expires_at <= clock_timestamp()
                   OR invitation_expires_at > clock_timestamp() + interval '7 days' THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'Invitation token or expiry is invalid.';
                END IF;
                IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 2000 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'A reason of 10 to 2000 characters is required.';
                END IF;

                PERFORM pg_advisory_xact_lock(hashtextextended(v_tenant_id::text || ':invitation:' || v_email, 0));

                UPDATE public.tenant_invitations
                SET status = 'EXPIRED', token_hash = repeat('0', 32) || replace(id::text, '-', ''), updated_at = clock_timestamp()
                WHERE tenant_id = v_tenant_id AND email_normalized = v_email
                  AND status IN ('PENDING', 'DELIVERY_FAILED') AND expires_at <= clock_timestamp();

                IF EXISTS (
                    SELECT 1 FROM public.users u
                    JOIN public.tenant_memberships m ON m.user_id = u.id
                    WHERE lower(u.email) = v_email AND m.tenant_id = v_tenant_id AND m.status = 'ACTIVE'
                ) THEN
                    RAISE EXCEPTION USING ERRCODE = '23505', MESSAGE = 'This email already has active access to the selected tenant.';
                END IF;

                INSERT INTO public.tenant_invitations (
                    id, tenant_id, email_normalized, membership_role, status, token_hash,
                    invited_by, reason, expires_at, delivery_attempts, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_email, requested_role, 'PENDING', new_token_hash,
                    v_actor_id, btrim(action_reason), invitation_expires_at, 1, clock_timestamp(), clock_timestamp()
                )
                RETURNING id INTO v_invitation_id;

                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id,
                    scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_INVITATION_ISSUED',
                    'TENANT_INVITATION', v_invitation_id,
                    jsonb_build_object('email', v_email, 'membership_role', requested_role, 'expires_at', invitation_expires_at),
                    btrim(action_reason), clock_timestamp(), clock_timestamp(), clock_timestamp()
                );

                RETURN v_invitation_id;
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.rotate_tenant_invitation(
                invitation_id uuid, new_token_hash text, invitation_expires_at timestamptz, action_reason text
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
                v_invitation public.tenant_invitations%ROWTYPE;
            BEGIN
                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;
                IF v_tenant_id IS NULL OR v_actor_id IS NULL OR v_actor_role IS NULL OR v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;

                SELECT * INTO v_invitation FROM public.tenant_invitations
                WHERE id = invitation_id AND tenant_id = v_tenant_id FOR UPDATE;
                IF v_invitation.id IS NULL OR v_invitation.status NOT IN ('PENDING', 'DELIVERY_FAILED') THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'A resendable tenant invitation was not found.';
                END IF;
                IF v_actor_role = 'ADMIN' AND v_invitation.membership_role <> 'MEMBER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Only the tenant owner may resend an administrator invitation.';
                END IF;
                IF new_token_hash !~ '^[0-9a-f]{64}$' OR invitation_expires_at <= clock_timestamp()
                   OR invitation_expires_at > clock_timestamp() + interval '7 days'
                   OR action_reason IS NULL OR length(btrim(action_reason)) < 10 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'Replacement token, expiry or reason is invalid.';
                END IF;

                UPDATE public.tenant_invitations
                SET token_hash = new_token_hash, expires_at = invitation_expires_at, status = 'PENDING',
                    sent_at = NULL, delivery_attempts = delivery_attempts + 1,
                    last_delivery_error_code = NULL, updated_at = clock_timestamp()
                WHERE id = invitation_id AND tenant_id = v_tenant_id;

                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id, scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_INVITATION_RESENT', 'TENANT_INVITATION', invitation_id,
                    jsonb_build_object('email', v_invitation.email_normalized, 'delivery_attempt', v_invitation.delivery_attempts + 1),
                    btrim(action_reason), clock_timestamp(), clock_timestamp(), clock_timestamp()
                );
                RETURN invitation_id;
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.mark_tenant_invitation_sent(invitation_id uuid)
            RETURNS void
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = pg_catalog, public
            AS $$
            DECLARE
                v_tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
                v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
                v_actor_role text;
            BEGIN
                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;
                IF v_tenant_id IS NULL OR v_actor_id IS NULL OR v_actor_role IS NULL OR v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;
                UPDATE public.tenant_invitations
                SET sent_at = clock_timestamp(), last_delivery_error_code = NULL, updated_at = clock_timestamp()
                WHERE id = invitation_id AND tenant_id = v_tenant_id AND status = 'PENDING';
                IF NOT FOUND THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'A pending tenant invitation was not found.';
                END IF;
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.mark_tenant_invitation_delivery_failed(invitation_id uuid, error_code text)
            RETURNS void
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = pg_catalog, public
            AS $$
            DECLARE
                v_tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
                v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
                v_actor_role text;
                v_email text;
            BEGIN
                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;
                IF v_tenant_id IS NULL OR v_actor_id IS NULL OR v_actor_role IS NULL OR v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;
                UPDATE public.tenant_invitations
                SET status = 'DELIVERY_FAILED', last_delivery_error_code = left(coalesce(error_code, 'MAIL_DELIVERY_FAILED'), 80),
                    updated_at = clock_timestamp()
                WHERE id = invitation_id AND tenant_id = v_tenant_id AND status = 'PENDING'
                RETURNING email_normalized INTO v_email;
                IF v_email IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'A pending tenant invitation was not found.';
                END IF;
                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id, scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_INVITATION_DELIVERY_FAILED', 'TENANT_INVITATION', invitation_id,
                    jsonb_build_object('email', v_email, 'error_code', left(coalesce(error_code, 'MAIL_DELIVERY_FAILED'), 80)),
                    'Email delivery failed; a replacement single-use token may be issued by an authorized tenant administrator.',
                    clock_timestamp(), clock_timestamp(), clock_timestamp()
                );
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.revoke_tenant_invitation(invitation_id uuid, action_reason text)
            RETURNS void
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = pg_catalog, public
            AS $$
            DECLARE
                v_tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
                v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
                v_actor_role text;
                v_invitation public.tenant_invitations%ROWTYPE;
            BEGIN
                SELECT membership_role INTO v_actor_role
                FROM public.tenant_memberships
                WHERE tenant_id = v_tenant_id AND user_id = v_actor_id AND status = 'ACTIVE'
                FOR SHARE;
                IF v_tenant_id IS NULL OR v_actor_id IS NULL OR v_actor_role IS NULL OR v_actor_role NOT IN ('OWNER', 'ADMIN') THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Tenant owner or administrator permission is required.';
                END IF;
                SELECT * INTO v_invitation FROM public.tenant_invitations
                WHERE id = invitation_id AND tenant_id = v_tenant_id FOR UPDATE;
                IF v_invitation.id IS NULL OR v_invitation.status NOT IN ('PENDING', 'DELIVERY_FAILED') THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'A revocable tenant invitation was not found.';
                END IF;
                IF v_actor_role = 'ADMIN' AND v_invitation.membership_role <> 'MEMBER' THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'Only the tenant owner may revoke an administrator invitation.';
                END IF;
                IF action_reason IS NULL OR length(btrim(action_reason)) < 10 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'A reason of at least 10 characters is required.';
                END IF;
                UPDATE public.tenant_invitations
                SET status = 'REVOKED', revoked_at = clock_timestamp(), token_hash = repeat('0', 32) || replace(id::text, '-', ''), updated_at = clock_timestamp()
                WHERE id = invitation_id AND tenant_id = v_tenant_id;
                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id, scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_tenant_id, v_actor_id, 'TENANT_INVITATION_REVOKED', 'TENANT_INVITATION', invitation_id,
                    jsonb_build_object('email', v_invitation.email_normalized, 'membership_role', v_invitation.membership_role),
                    btrim(action_reason), clock_timestamp(), clock_timestamp(), clock_timestamp()
                );
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.accept_tenant_invitation_for_new_user(
                supplied_token_hash text, supplied_email text, new_user_id uuid, new_user_name text, new_password_hash text
            )
            RETURNS jsonb
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = pg_catalog, public
            AS $$
            DECLARE
                v_invitation public.tenant_invitations%ROWTYPE;
                v_email text := lower(btrim(supplied_email));
                v_membership_id uuid;
                v_now timestamptz := clock_timestamp();
            BEGIN
                IF supplied_token_hash !~ '^[0-9a-f]{64}$' OR length(v_email) NOT BETWEEN 3 AND 254
                   OR length(btrim(new_user_name)) NOT BETWEEN 2 AND 160 OR length(new_password_hash) NOT BETWEEN 60 AND 255 THEN
                    RAISE EXCEPTION USING ERRCODE = '22023', MESSAGE = 'Invitation registration details are invalid.';
                END IF;
                PERFORM pg_advisory_xact_lock(hashtextextended('global-email:' || v_email, 0));
                SELECT * INTO v_invitation FROM public.tenant_invitations
                WHERE token_hash = supplied_token_hash AND email_normalized = v_email
                  AND status = 'PENDING' AND expires_at > v_now
                FOR UPDATE;
                IF v_invitation.id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'Invitation is invalid, expired, already used or revoked.';
                END IF;
                IF EXISTS (SELECT 1 FROM public.users WHERE lower(email) = v_email) THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0003', MESSAGE = 'An account already exists for this email; sign in and accept the invitation.';
                END IF;
                INSERT INTO public.users (id, name, email, password, status, email_verified_at, created_at, updated_at)
                VALUES (new_user_id, btrim(new_user_name), v_email, new_password_hash, 'ACTIVE', v_now, v_now, v_now);
                v_membership_id := gen_random_uuid();
                INSERT INTO public.tenant_memberships (
                    id, tenant_id, user_id, membership_role, status, joined_at, revoked_at, created_at, updated_at
                ) VALUES (
                    v_membership_id, v_invitation.tenant_id, new_user_id, v_invitation.membership_role, 'ACTIVE', v_now, NULL, v_now, v_now
                );
                UPDATE public.tenant_invitations
                SET status = 'ACCEPTED', accepted_by = new_user_id, accepted_at = v_now,
                    token_hash = repeat('0', 32) || replace(id::text, '-', ''), updated_at = v_now
                WHERE id = v_invitation.id;
                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id, scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_invitation.tenant_id, new_user_id, 'TENANT_INVITATION_ACCEPTED', 'TENANT_INVITATION', v_invitation.id,
                    jsonb_build_object('membership_id', v_membership_id, 'membership_role', v_invitation.membership_role, 'identity_created', true),
                    'Invitee proved control of the invited email and created a verified global identity.',
                    v_now, v_now, v_now
                );
                RETURN jsonb_build_object('tenant_id', v_invitation.tenant_id, 'membership_id', v_membership_id, 'user_id', new_user_id);
            END;
            $$;

            CREATE OR REPLACE FUNCTION public.accept_tenant_invitation_for_existing_user(supplied_token_hash text, target_user_id uuid)
            RETURNS jsonb
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = pg_catalog, public
            AS $$
            DECLARE
                v_actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
                v_user public.users%ROWTYPE;
                v_invitation public.tenant_invitations%ROWTYPE;
                v_membership_id uuid;
                v_now timestamptz := clock_timestamp();
            BEGIN
                IF v_actor_id IS NULL OR v_actor_id <> target_user_id THEN
                    RAISE EXCEPTION USING ERRCODE = '42501', MESSAGE = 'An authenticated invitee identity is required.';
                END IF;
                SELECT * INTO v_user FROM public.users WHERE id = target_user_id AND status = 'ACTIVE' FOR UPDATE;
                IF v_user.id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'An active global user account was not found.';
                END IF;
                SELECT * INTO v_invitation FROM public.tenant_invitations
                WHERE token_hash = supplied_token_hash AND email_normalized = lower(v_user.email)
                  AND status = 'PENDING' AND expires_at > v_now
                FOR UPDATE;
                IF v_invitation.id IS NULL THEN
                    RAISE EXCEPTION USING ERRCODE = 'P0002', MESSAGE = 'Invitation is invalid, expired, already used or revoked.';
                END IF;
                IF EXISTS (
                    SELECT 1 FROM public.tenant_memberships
                    WHERE tenant_id = v_invitation.tenant_id AND user_id = target_user_id AND status = 'ACTIVE'
                ) THEN
                    RAISE EXCEPTION USING ERRCODE = '23505', MESSAGE = 'This account already has active access to the invited tenant.';
                END IF;
                v_membership_id := gen_random_uuid();
                INSERT INTO public.tenant_memberships (
                    id, tenant_id, user_id, membership_role, status, joined_at, revoked_at, created_at, updated_at
                ) VALUES (
                    v_membership_id, v_invitation.tenant_id, target_user_id, v_invitation.membership_role, 'ACTIVE', v_now, NULL, v_now, v_now
                );
                UPDATE public.users SET email_verified_at = coalesce(email_verified_at, v_now), updated_at = v_now WHERE id = target_user_id;
                UPDATE public.tenant_invitations
                SET status = 'ACCEPTED', accepted_by = target_user_id, accepted_at = v_now,
                    token_hash = repeat('0', 32) || replace(id::text, '-', ''), updated_at = v_now
                WHERE id = v_invitation.id;
                INSERT INTO public.audit_events (
                    id, tenant_id, actor_user_id, action, object_type, object_id, scope, reason, occurred_at, created_at, updated_at
                ) VALUES (
                    gen_random_uuid(), v_invitation.tenant_id, target_user_id, 'TENANT_INVITATION_ACCEPTED', 'TENANT_INVITATION', v_invitation.id,
                    jsonb_build_object('membership_id', v_membership_id, 'membership_role', v_invitation.membership_role, 'identity_created', false),
                    'Authenticated invitee proved control of the invited email and accepted the tenant membership.',
                    v_now, v_now, v_now
                );
                RETURN jsonb_build_object('tenant_id', v_invitation.tenant_id, 'membership_id', v_membership_id, 'user_id', target_user_id);
            END;
            $$;

            REVOKE ALL ON FUNCTION public.create_tenant_invitation(text, text, text, timestamptz, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.rotate_tenant_invitation(uuid, text, timestamptz, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.mark_tenant_invitation_sent(uuid) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.mark_tenant_invitation_delivery_failed(uuid, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.revoke_tenant_invitation(uuid, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.accept_tenant_invitation_for_new_user(text, text, uuid, text, text) FROM PUBLIC;
            REVOKE ALL ON FUNCTION public.accept_tenant_invitation_for_existing_user(text, uuid) FROM PUBLIC;
            GRANT EXECUTE ON FUNCTION public.create_tenant_invitation(text, text, text, timestamptz, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.rotate_tenant_invitation(uuid, text, timestamptz, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.mark_tenant_invitation_sent(uuid) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.mark_tenant_invitation_delivery_failed(uuid, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.revoke_tenant_invitation(uuid, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.accept_tenant_invitation_for_new_user(text, text, uuid, text, text) TO app_runtime;
            GRANT EXECUTE ON FUNCTION public.accept_tenant_invitation_for_existing_user(text, uuid) TO app_runtime;
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                REVOKE EXECUTE ON FUNCTION public.create_tenant_invitation(text, text, text, timestamptz, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.rotate_tenant_invitation(uuid, text, timestamptz, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.mark_tenant_invitation_sent(uuid) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.mark_tenant_invitation_delivery_failed(uuid, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.revoke_tenant_invitation(uuid, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.accept_tenant_invitation_for_new_user(text, text, uuid, text, text) FROM app_runtime;
                REVOKE EXECUTE ON FUNCTION public.accept_tenant_invitation_for_existing_user(text, uuid) FROM app_runtime;
                DROP FUNCTION IF EXISTS public.accept_tenant_invitation_for_existing_user(text, uuid);
                DROP FUNCTION IF EXISTS public.accept_tenant_invitation_for_new_user(text, text, uuid, text, text);
                DROP FUNCTION IF EXISTS public.revoke_tenant_invitation(uuid, text);
                DROP FUNCTION IF EXISTS public.mark_tenant_invitation_delivery_failed(uuid, text);
                DROP FUNCTION IF EXISTS public.mark_tenant_invitation_sent(uuid);
                DROP FUNCTION IF EXISTS public.rotate_tenant_invitation(uuid, text, timestamptz, text);
                DROP FUNCTION IF EXISTS public.create_tenant_invitation(text, text, text, timestamptz, text);
                DROP TABLE IF EXISTS public.tenant_invitations;
            SQL);
        }
    }
};
