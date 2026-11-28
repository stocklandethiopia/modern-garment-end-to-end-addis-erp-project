<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Locked tenant membership lookup requires PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.lock_active_tenant_membership(
    target_tenant_id uuid,
    target_user_id uuid
)
RETURNS text
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_context_tenant_id uuid := NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
    v_membership_role text;
BEGIN
    IF v_context_tenant_id IS NULL OR v_context_tenant_id <> target_tenant_id THEN
        RAISE EXCEPTION 'A verified matching tenant context is required.'
            USING ERRCODE = '42501';
    END IF;

    SELECT membership.membership_role
      INTO v_membership_role
      FROM public.tenant_memberships membership
      JOIN public.users identity
        ON identity.id = membership.user_id
     WHERE membership.tenant_id = target_tenant_id
       AND membership.user_id = target_user_id
       AND membership.status = 'ACTIVE'
       AND identity.status = 'ACTIVE'
     FOR UPDATE OF membership, identity;

    RETURN v_membership_role;
END;
$$;

REVOKE ALL ON FUNCTION public.lock_active_tenant_membership(uuid, uuid) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.lock_active_tenant_membership(uuid, uuid) TO app_runtime;
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
REVOKE EXECUTE ON FUNCTION public.lock_active_tenant_membership(uuid, uuid) FROM app_runtime;
DROP FUNCTION IF EXISTS public.lock_active_tenant_membership(uuid, uuid);
SQL);
        }
    }
};
