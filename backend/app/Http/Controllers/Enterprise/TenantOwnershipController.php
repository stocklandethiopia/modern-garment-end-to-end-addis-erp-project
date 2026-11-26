<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\TenantOwnershipService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantOwnershipController extends Controller
{
    public function transfer(
        Request $request,
        TenantContext $tenantContext,
        TenantOwnershipService $ownership,
    ): JsonResponse {
        abort_unless($tenantContext->membershipRole() === 'OWNER', 403, 'Only the current tenant owner may transfer ownership.');

        $data = $request->validate([
            'target_user_id' => ['required', 'uuid'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $membershipId = $ownership->transfer(
            $tenantContext->tenantId(),
            (string) $request->user()->getAuthIdentifier(),
            $data['target_user_id'],
            trim($data['reason']),
        );

        return response()->json([
            'data' => [
                'tenant_id' => $tenantContext->tenantId(),
                'new_owner_user_id' => $data['target_user_id'],
                'new_owner_membership_id' => $membershipId,
                'previous_owner_user_id' => (string) $request->user()->getAuthIdentifier(),
                'status' => 'TRANSFERRED',
            ],
        ]);
    }

    public function leave(
        Request $request,
        TenantContext $tenantContext,
        TenantOwnershipService $ownership,
    ): JsonResponse {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $membershipId = $ownership->leave(
            $tenantContext->tenantId(),
            (string) $request->user()->getAuthIdentifier(),
            trim($data['reason']),
        );

        return response()->json([
            'data' => [
                'tenant_id' => $tenantContext->tenantId(),
                'membership_id' => $membershipId,
                'user_id' => (string) $request->user()->getAuthIdentifier(),
                'status' => 'LEFT',
            ],
        ]);
    }
}
