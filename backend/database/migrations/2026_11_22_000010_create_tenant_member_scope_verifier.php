<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Tenant member scope verification requires PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.is_active_member_of_current_tenant(target_user_id uuid)
            RETURNS boolean
            LANGUAGE sql
            STABLE
            SECURITY DEFINER
            SET search_path = pg_catalog, public
            AS $$
                SELECT EXISTS (
                    SELECT 1
                    FROM public.tenant_memberships AS membership
                    WHERE membership.tenant_id =
                        NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                      AND membership.user_id = target_user_id
                      AND membership.status = 'ACTIVE'
                );
            $$;

            REVOKE ALL ON FUNCTION public.is_active_member_of_current_tenant(uuid) FROM PUBLIC;
            GRANT EXECUTE ON FUNCTION public.is_active_member_of_current_tenant(uuid) TO app_runtime;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('REVOKE EXECUTE ON FUNCTION public.is_active_member_of_current_tenant(uuid) FROM app_runtime');
        DB::unprepared('DROP FUNCTION IF EXISTS public.is_active_member_of_current_tenant(uuid)');
    }
};
