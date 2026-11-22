<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenantId = $request->header('X-Tenant-Id');

        if ($user === null) {
            return response()->json(['message' => 'Authentication is required.'], 401);
        }

        if (! is_string($tenantId) || ! Str::isUuid($tenantId)) {
            return response()->json(['message' => 'A valid X-Tenant-Id header is required.'], 400);
        }

        $connection = DB::connection('pgsql');
        $connection->beginTransaction();

        try {
            // Transaction-local context prevents pooled-connection tenant leakage.
            $connection->select("select set_config('app.current_user_id', ?, true)", [$user->getAuthIdentifier()]);
            $connection->select("select set_config('app.current_tenant_id', ?, true)", [$tenantId]);

            $membership = $connection->table('tenant_memberships')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $user->getAuthIdentifier())
                ->where('status', 'ACTIVE')
                ->first();

            $tenantIsActive = $connection->table('tenants')
                ->where('id', $tenantId)
                ->where('status', 'ACTIVE')
                ->exists();

            if ($membership === null || ! $tenantIsActive) {
                $connection->rollBack();

                return response()->json(['message' => 'Tenant access is not authorized.'], 403);
            }

            app(TenantContext::class)->set($tenantId, $membership->membership_role);

            $response = $next($request);
            $connection->commit();
            app(TenantContext::class)->clear();

            return $response;
        } catch (Throwable $exception) {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            app(TenantContext::class)->clear();

            throw $exception;
        }
    }
}
