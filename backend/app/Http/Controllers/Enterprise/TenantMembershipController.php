<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\TenantMembershipService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantMembershipController extends Controller
{
    public function index(TenantContext $tenantContext, TenantMembershipService $memberships): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        return response()->json([
            'data' => $memberships->members($tenantContext->tenantId()),
        ]);
    }

    public function store(
        Request $request,
        TenantContext $tenantContext,
        TenantMembershipService $memberships,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'membership_role' => ['required', Rule::in(['MEMBER', 'ADMIN'])],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $normalizedEmail = mb_strtolower(trim($data['email']));
        $user = DB::table('users')
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->where('status', 'ACTIVE')
            ->first(['id']);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => ['No active global account exists for this email. Create or verify the user identity through the controlled platform identity process first.'],
            ]);
        }

        $membershipId = $memberships->add(
            (string) $user->id,
            $data['membership_role'],
            trim($data['reason']),
        );

        return response()->json([
            'data' => [
                'membership_id' => $membershipId,
                'user_id' => (string) $user->id,
                'email' => $normalizedEmail,
                'membership_role' => $data['membership_role'],
                'status' => 'ACTIVE',
            ],
        ], 201);
    }

    public function updateRole(
        string $userId,
        Request $request,
        TenantContext $tenantContext,
        TenantMembershipService $memberships,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'membership_role' => ['required', Rule::in(['MEMBER', 'ADMIN'])],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $membershipId = $memberships->changeRole($userId, $data['membership_role'], trim($data['reason']));

        return response()->json([
            'data' => [
                'membership_id' => $membershipId,
                'user_id' => $userId,
                'membership_role' => $data['membership_role'],
            ],
        ]);
    }

    public function revoke(
        string $userId,
        Request $request,
        TenantContext $tenantContext,
        TenantMembershipService $memberships,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $membershipId = $memberships->revoke($userId, trim($data['reason']));

        return response()->json([
            'data' => [
                'membership_id' => $membershipId,
                'user_id' => $userId,
                'status' => 'REVOKED',
            ],
        ]);
    }
}
