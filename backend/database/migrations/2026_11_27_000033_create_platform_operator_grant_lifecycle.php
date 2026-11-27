<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Platform operator grant lifecycle requires PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.platform_expire_operator_grants(expiry_actor_id uuid)
RETURNS integer
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    expired_record record;
    expired_count integer := 0;
    event_time timestamptz := clock_timestamp();
    expiry_reason text;
BEGIN
    IF expiry_actor_id IS NULL THEN
        RAISE EXCEPTION 'Platform audit actor is required for grant expiry.'
            USING ERRCODE = '42501';
    END IF;

    FOR expired_record IN
        SELECT grant_record.id, grant_record.user_id, grant_record.role_code,
               grant_record.status, grant_record.valid_from, grant_record.valid_to
          FROM public.platform_operator_grants grant_record
         WHERE grant_record.status IN ('PENDING', 'ACTIVE')
           AND grant_record.valid_to IS NOT NULL
           AND grant_record.valid_to < CURRENT_DATE
         ORDER BY grant_record.id
         FOR UPDATE
    LOOP
        expiry_reason := CASE expired_record.status
            WHEN 'PENDING' THEN 'Pending platform operator request expired without approval.'
            ELSE 'Approved platform operator grant reached its approved end date.'
        END;

        UPDATE public.platform_operator_grants
           SET status = 'EXPIRED',
               updated_at = event_time
         WHERE id = expired_record.id;

        INSERT INTO public.platform_audit_events (
            actor_user_id, tenant_id, action, object_type, object_id,
            before_state, after_state, reason, occurred_at
        ) VALUES (
            expiry_actor_id, NULL, 'PLATFORM_OPERATOR_GRANT_EXPIRED',
            'PLATFORM_OPERATOR_GRANT', expired_record.id,
            jsonb_build_object(
                'user_id', expired_record.user_id,
                'role_code', expired_record.role_code,
                'status', expired_record.status,
                'valid_from', expired_record.valid_from,
                'valid_to', expired_record.valid_to
            ),
            jsonb_build_object('status', 'EXPIRED'),
            expiry_reason,
            event_time
        );
        expired_count := expired_count + 1;
    END LOOP;

    RETURN expired_count;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_current_operator_roles()
RETURNS TABLE (role_code varchar)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid := NULLIF(current_setting('app.current_user_id', true), '')::uuid;
BEGIN
    IF actor_id IS NULL THEN
        RAISE EXCEPTION 'Authenticated platform identity context is required.'
            USING ERRCODE = '42501';
    END IF;

    RETURN QUERY
    SELECT grant_record.role_code
      FROM public.platform_operator_grants grant_record
      JOIN public.users actor ON actor.id = grant_record.user_id
     WHERE grant_record.user_id = actor_id
       AND actor.status = 'ACTIVE'
       AND grant_record.status = 'ACTIVE'
       AND grant_record.valid_from <= CURRENT_DATE
       AND (grant_record.valid_to IS NULL OR grant_record.valid_to >= CURRENT_DATE)
     ORDER BY grant_record.role_code;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_list_operator_grants()
RETURNS TABLE (
    grant_id uuid,
    user_id uuid,
    user_name varchar,
    user_email varchar,
    role_code varchar,
    grant_status varchar,
    valid_from date,
    valid_to date,
    grant_reason varchar,
    granted_by uuid,
    grantor_name varchar,
    approved_by uuid,
    approver_name varchar,
    approved_at timestamptz,
    revoked_by uuid,
    revoker_name varchar,
    revoked_at timestamptz,
    revocation_reason varchar,
    created_at timestamptz,
    updated_at timestamptz
)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid;
BEGIN
    PERFORM pg_advisory_xact_lock(hashtextextended('platform-operator-grant-roster', 0));
    actor_id := public.require_platform_operator('PLATFORM_ADMIN');
    PERFORM public.platform_expire_operator_grants(actor_id);

    RETURN QUERY
    SELECT grant_record.id, grant_record.user_id, target_user.name, target_user.email,
           grant_record.role_code, grant_record.status, grant_record.valid_from,
           grant_record.valid_to, grant_record.grant_reason, grant_record.granted_by,
           grantor.name, grant_record.approved_by, approver.name, grant_record.approved_at,
           grant_record.revoked_by, revoker.name, grant_record.revoked_at,
           grant_record.revocation_reason, grant_record.created_at, grant_record.updated_at
      FROM public.platform_operator_grants grant_record
      JOIN public.users target_user ON target_user.id = grant_record.user_id
      JOIN public.users grantor ON grantor.id = grant_record.granted_by
      LEFT JOIN public.users approver ON approver.id = grant_record.approved_by
      LEFT JOIN public.users revoker ON revoker.id = grant_record.revoked_by
     ORDER BY grant_record.created_at DESC, grant_record.id;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_request_operator_grant(
    target_user_email text,
    target_role_code text,
    effective_from date,
    effective_to date,
    action_reason text
)
RETURNS TABLE (grant_id uuid, grant_status varchar)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid;
    target_user_id uuid;
    start_date date := COALESCE(effective_from, CURRENT_DATE);
    new_grant_id uuid;
    event_time timestamptz := clock_timestamp();
BEGIN
    PERFORM pg_advisory_xact_lock(hashtextextended('platform-operator-grant-roster', 0));
    actor_id := public.require_platform_operator('PLATFORM_ADMIN');
    PERFORM public.platform_expire_operator_grants(actor_id);
    PERFORM public.require_platform_operator('PLATFORM_ADMIN');

    IF target_role_code NOT IN ('PLATFORM_ADMIN', 'PLATFORM_SUPPORT') THEN
        RAISE EXCEPTION 'Unsupported platform operator role.'
            USING ERRCODE = '22023';
    END IF;
    IF target_user_email IS NULL OR btrim(target_user_email) = '' THEN
        RAISE EXCEPTION 'A target account email is required.'
            USING ERRCODE = '22023';
    END IF;
    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 500 THEN
        RAISE EXCEPTION 'A grant reason of 10 to 500 characters is required.'
            USING ERRCODE = '22023';
    END IF;
    IF start_date < CURRENT_DATE
       OR effective_to IS NULL
       OR effective_to < start_date
       OR effective_to > start_date + 365 THEN
        RAISE EXCEPTION 'Platform grant validity must be explicit, current and no longer than 365 days.'
            USING ERRCODE = '22023';
    END IF;

    SELECT actor.id INTO target_user_id
      FROM public.users actor
     WHERE lower(actor.email) = lower(btrim(target_user_email))
       AND actor.status = 'ACTIVE'
     FOR SHARE;

    IF target_user_id IS NULL THEN
        RAISE EXCEPTION 'An active target identity was not found.'
            USING ERRCODE = 'P0002';
    END IF;
    IF target_user_id = actor_id THEN
        RAISE EXCEPTION 'An operator cannot request platform access for their own identity.'
            USING ERRCODE = '22023';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM public.platform_operator_grants existing
         WHERE existing.user_id = target_user_id
           AND existing.role_code = target_role_code
           AND existing.status IN ('PENDING', 'ACTIVE')
    ) THEN
        RAISE EXCEPTION 'A pending or active grant already exists for this account and role.'
            USING ERRCODE = '22023';
    END IF;

    INSERT INTO public.platform_operator_grants (
        user_id, role_code, status, valid_from, valid_to,
        grant_reason, granted_by, created_at, updated_at
    ) VALUES (
        target_user_id, target_role_code, 'PENDING', start_date, effective_to,
        btrim(action_reason), actor_id, event_time, event_time
    )
    RETURNING id INTO new_grant_id;

    INSERT INTO public.platform_audit_events (
        actor_user_id, tenant_id, action, object_type, object_id,
        before_state, after_state, reason, occurred_at
    ) VALUES (
        actor_id, NULL, 'PLATFORM_OPERATOR_GRANT_REQUESTED',
        'PLATFORM_OPERATOR_GRANT', new_grant_id, '{}'::jsonb,
        jsonb_build_object(
            'user_id', target_user_id,
            'role_code', target_role_code,
            'status', 'PENDING',
            'valid_from', start_date,
            'valid_to', effective_to
        ),
        btrim(action_reason), event_time
    );

    RETURN QUERY SELECT new_grant_id, 'PENDING'::varchar;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_approve_operator_grant(
    target_grant_id uuid,
    action_reason text
)
RETURNS TABLE (grant_id uuid, grant_status varchar)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid;
    grant_record public.platform_operator_grants%ROWTYPE;
    event_time timestamptz := clock_timestamp();
BEGIN
    PERFORM pg_advisory_xact_lock(hashtextextended('platform-operator-grant-roster', 0));
    actor_id := public.require_platform_operator('PLATFORM_ADMIN');
    PERFORM public.platform_expire_operator_grants(actor_id);
    PERFORM public.require_platform_operator('PLATFORM_ADMIN');

    SELECT * INTO grant_record
      FROM public.platform_operator_grants existing
     WHERE existing.id = target_grant_id
     FOR UPDATE;

    IF NOT FOUND OR grant_record.status <> 'PENDING' THEN
        RAISE EXCEPTION 'A pending platform operator grant was not found.'
            USING ERRCODE = 'P0002';
    END IF;
    IF grant_record.granted_by = actor_id OR grant_record.user_id = actor_id THEN
        RAISE EXCEPTION 'The grant requester and the grant recipient cannot approve this request.'
            USING ERRCODE = '22023';
    END IF;
    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 500 THEN
        RAISE EXCEPTION 'An approval reason of 10 to 500 characters is required.'
            USING ERRCODE = '22023';
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM public.users target_user
         WHERE target_user.id = grant_record.user_id
           AND target_user.status = 'ACTIVE'
    ) THEN
        RAISE EXCEPTION 'The target identity is no longer active.'
            USING ERRCODE = '22023';
    END IF;
    IF grant_record.valid_to IS NOT NULL AND grant_record.valid_to < CURRENT_DATE THEN
        RAISE EXCEPTION 'An expired platform operator grant cannot be approved.'
            USING ERRCODE = '22023';
    END IF;

    UPDATE public.platform_operator_grants
       SET status = 'ACTIVE',
           approved_by = actor_id,
           approved_at = event_time,
           updated_at = event_time
     WHERE id = target_grant_id;

    INSERT INTO public.platform_audit_events (
        actor_user_id, tenant_id, action, object_type, object_id,
        before_state, after_state, reason, occurred_at
    ) VALUES (
        actor_id, NULL, 'PLATFORM_OPERATOR_GRANT_APPROVED',
        'PLATFORM_OPERATOR_GRANT', target_grant_id,
        jsonb_build_object('status', 'PENDING'),
        jsonb_build_object(
            'user_id', grant_record.user_id,
            'role_code', grant_record.role_code,
            'status', 'ACTIVE',
            'valid_from', grant_record.valid_from,
            'valid_to', grant_record.valid_to
        ),
        btrim(action_reason), event_time
    );

    RETURN QUERY SELECT target_grant_id, 'ACTIVE'::varchar;
END;
$$;

CREATE OR REPLACE FUNCTION public.platform_revoke_operator_grant(
    target_grant_id uuid,
    action_reason text
)
RETURNS TABLE (grant_id uuid, grant_status varchar)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    actor_id uuid;
    grant_record public.platform_operator_grants%ROWTYPE;
    other_admin_count integer;
    event_time timestamptz := clock_timestamp();
BEGIN
    PERFORM pg_advisory_xact_lock(hashtextextended('platform-operator-grant-roster', 0));
    actor_id := public.require_platform_operator('PLATFORM_ADMIN');
    PERFORM public.platform_expire_operator_grants(actor_id);
    PERFORM public.require_platform_operator('PLATFORM_ADMIN');

    SELECT * INTO grant_record
      FROM public.platform_operator_grants existing
     WHERE existing.id = target_grant_id
     FOR UPDATE;

    IF NOT FOUND OR grant_record.status NOT IN ('PENDING', 'ACTIVE') THEN
        RAISE EXCEPTION 'A pending or active platform operator grant was not found.'
            USING ERRCODE = 'P0002';
    END IF;
    IF grant_record.user_id = actor_id THEN
        RAISE EXCEPTION 'An operator cannot revoke their own platform grant.'
            USING ERRCODE = '22023';
    END IF;
    IF action_reason IS NULL OR length(btrim(action_reason)) < 10 OR length(action_reason) > 500 THEN
        RAISE EXCEPTION 'A revocation reason of 10 to 500 characters is required.'
            USING ERRCODE = '22023';
    END IF;

    IF grant_record.role_code = 'PLATFORM_ADMIN'
       AND grant_record.status = 'ACTIVE'
       AND grant_record.valid_from <= CURRENT_DATE
       AND (grant_record.valid_to IS NULL OR grant_record.valid_to >= CURRENT_DATE) THEN
        SELECT count(*) INTO other_admin_count
          FROM public.platform_operator_grants other_grant
         WHERE other_grant.user_id <> grant_record.user_id
           AND other_grant.role_code = 'PLATFORM_ADMIN'
           AND other_grant.status = 'ACTIVE'
           AND other_grant.valid_from <= CURRENT_DATE
           AND (other_grant.valid_to IS NULL OR other_grant.valid_to >= CURRENT_DATE);

        IF other_admin_count < 1 THEN
            RAISE EXCEPTION 'The last active platform administrator grant cannot be revoked.'
                USING ERRCODE = '22023';
        END IF;
    END IF;

    UPDATE public.platform_operator_grants
       SET status = 'REVOKED',
           revoked_by = actor_id,
           revoked_at = event_time,
           revocation_reason = btrim(action_reason),
           updated_at = event_time
     WHERE id = target_grant_id;

    INSERT INTO public.platform_audit_events (
        actor_user_id, tenant_id, action, object_type, object_id,
        before_state, after_state, reason, occurred_at
    ) VALUES (
        actor_id, NULL, 'PLATFORM_OPERATOR_GRANT_REVOKED',
        'PLATFORM_OPERATOR_GRANT', target_grant_id,
        jsonb_build_object(
            'user_id', grant_record.user_id,
            'role_code', grant_record.role_code,
            'status', grant_record.status
        ),
        jsonb_build_object('status', 'REVOKED', 'revoked_by', actor_id),
        btrim(action_reason), event_time
    );

    RETURN QUERY SELECT target_grant_id, 'REVOKED'::varchar;
END;
$$;

REVOKE ALL ON FUNCTION public.platform_expire_operator_grants(uuid) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_current_operator_roles() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_list_operator_grants() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_request_operator_grant(text, text, date, date, text) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_approve_operator_grant(uuid, text) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.platform_revoke_operator_grant(uuid, text) FROM PUBLIC;

GRANT EXECUTE ON FUNCTION public.platform_current_operator_roles() TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_list_operator_grants() TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_request_operator_grant(text, text, date, date, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_approve_operator_grant(uuid, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_revoke_operator_grant(uuid, text) TO app_control;
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
REVOKE EXECUTE ON FUNCTION public.platform_current_operator_roles() FROM app_control;
REVOKE EXECUTE ON FUNCTION public.platform_list_operator_grants() FROM app_control;
REVOKE EXECUTE ON FUNCTION public.platform_request_operator_grant(text, text, date, date, text) FROM app_control;
REVOKE EXECUTE ON FUNCTION public.platform_approve_operator_grant(uuid, text) FROM app_control;
REVOKE EXECUTE ON FUNCTION public.platform_revoke_operator_grant(uuid, text) FROM app_control;

DROP FUNCTION IF EXISTS public.platform_revoke_operator_grant(uuid, text);
DROP FUNCTION IF EXISTS public.platform_approve_operator_grant(uuid, text);
DROP FUNCTION IF EXISTS public.platform_request_operator_grant(text, text, date, date, text);
DROP FUNCTION IF EXISTS public.platform_list_operator_grants();
DROP FUNCTION IF EXISTS public.platform_current_operator_roles();
DROP FUNCTION IF EXISTS public.platform_expire_operator_grants(uuid);
SQL);
    }
};
