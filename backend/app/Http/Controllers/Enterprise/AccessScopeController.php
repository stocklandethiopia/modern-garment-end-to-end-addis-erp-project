<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\ScopedAccessService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccessScopeController extends Controller
{
    public function index(string $userId, TenantContext $tenantContext, ScopedAccessService $scopes): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        return response()->json([
            'data' => $scopes->forMember($tenantContext->tenantId(), $userId),
        ]);
    }

    public function setMode(
        string $userId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopes,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'scope_mode' => ['required', Rule::in(['TENANT_WIDE', 'SCOPED'])],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $scopes->setMode(
                $userId,
                $data['scope_mode'],
                trim($data['reason']),
            ),
        ]);
    }

    public function grant(
        string $userId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopes,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'scope_type' => ['required', Rule::in(['BRAND', 'SITE', 'MARKET'])],
            'scope_id' => ['nullable', 'uuid', 'required_unless:scope_type,MARKET'],
            'scope_code' => ['nullable', 'string', 'max:160', 'required_if:scope_type,MARKET'],
            'permission_code' => ['required', Rule::in(['READ', 'CREATE', 'APPROVE', 'MANAGE'])],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'grant_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $scopes->grant(
                $userId,
                $data,
            ),
        ], 201);
    }

    public function revoke(
        string $userId,
        string $grantId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopes,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $grant = $scopes->forMember($tenantContext->tenantId(), $userId)['grants']
            ->firstWhere('id', $grantId);

        abort_if($grant === null, 404);

        return response()->json([
            'data' => $scopes->revoke(
                $grantId,
                trim($data['reason']),
            ),
        ]);
    }
}
