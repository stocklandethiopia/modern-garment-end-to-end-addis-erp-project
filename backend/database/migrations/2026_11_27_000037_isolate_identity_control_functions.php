<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Control-function privilege isolation requires PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_catalog.pg_roles WHERE rolname = 'app_control') THEN
        RAISE EXCEPTION 'The dedicated app_control login must be provisioned before this migration.';
    END IF;
END $$;

-- Global platform administration APIs require an actor assertion supplied by
-- the authenticated Laravel request over the separate app_control connection.
-- app_runtime cannot execute these functions, even if it sets arbitrary custom GUCs.
REVOKE ALL ON FUNCTION public.platform_list_tenants() FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_change_tenant_status(uuid, text, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_set_tenant_entitlements(uuid, text, text, jsonb, jsonb, date, date, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_current_operator_roles() FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_list_operator_grants() FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_request_operator_grant(text, text, date, date, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_approve_operator_grant(uuid, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.platform_revoke_operator_grant(uuid, text) FROM PUBLIC, app_runtime;

GRANT EXECUTE ON FUNCTION public.platform_list_tenants() TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_change_tenant_status(uuid, text, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_set_tenant_entitlements(uuid, text, text, jsonb, jsonb, date, date, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_current_operator_roles() TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_list_operator_grants() TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_request_operator_grant(text, text, date, date, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_approve_operator_grant(uuid, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.platform_revoke_operator_grant(uuid, text) TO app_control;

-- Tenant scoped-access mutation APIs follow the same isolated execution path.
REVOKE ALL ON FUNCTION public.set_tenant_member_scope_mode(uuid, text, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.grant_tenant_access_scope(uuid, text, uuid, text, text, date, date, text) FROM PUBLIC, app_runtime;
REVOKE ALL ON FUNCTION public.revoke_tenant_access_scope(uuid, text) FROM PUBLIC, app_runtime;

GRANT EXECUTE ON FUNCTION public.set_tenant_member_scope_mode(uuid, text, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.grant_tenant_access_scope(uuid, text, uuid, text, text, date, date, text) TO app_control;
GRANT EXECUTE ON FUNCTION public.revoke_tenant_access_scope(uuid, text) TO app_control;

-- No direct table privileges are granted to app_control. The API role can only
-- invoke the specific SECURITY DEFINER functions listed above.
REVOKE ALL ON TABLE public.platform_operator_grants FROM app_control;
REVOKE ALL ON TABLE public.platform_tenant_entitlements FROM app_control;
REVOKE ALL ON TABLE public.platform_audit_events FROM app_control;
REVOKE ALL ON TABLE public.tenant_access_scope_modes FROM app_control;
REVOKE ALL ON TABLE public.tenant_access_scopes FROM app_control;
SQL);
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Security migration 000037 is forward-only; do not restore privileged SECURITY DEFINER execution to app_runtime.'
        );
    }
};
