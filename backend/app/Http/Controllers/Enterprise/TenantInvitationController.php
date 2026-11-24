<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\TenantInvitationService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantInvitationController extends Controller
{
    public function index(TenantContext $context, TenantInvitationService $invitations): JsonResponse
    {
        abort_unless($context->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        return response()->json(['data' => $invitations->invitations($context->tenantId())]);
    }

    public function store(Request $request, TenantContext $context, TenantInvitationService $invitations): JsonResponse
    {
        abort_unless($context->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'membership_role' => ['required', 'in:MEMBER,ADMIN'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $tenant = DB::table('tenants')->where('id', $context->tenantId())->first(['name']);
        $result = $invitations->invite(
            email: mb_strtolower(trim($data['email'])),
            role: $data['membership_role'],
            reason: trim($data['reason']),
            tenantId: $context->tenantId(),
            tenantName: (string) $tenant->name,
            inviterName: (string) $request->user()->name,
            actorId: (string) $request->user()->id,
        );

        return response()->json(['data' => $result, 'message' => 'Invitation recorded; email delivery is processed after the database transaction commits.'], 202);
    }

    public function resend(
        string $invitationId,
        Request $request,
        TenantContext $context,
        TenantInvitationService $invitations,
    ): JsonResponse {
        abort_unless($context->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);
        $invitation = DB::table('tenant_invitations')
            ->where('tenant_id', $context->tenantId())
            ->where('id', $invitationId)
            ->whereIn('status', ['PENDING', 'DELIVERY_FAILED'])
            ->first(['email_normalized', 'membership_role']);

        abort_if($invitation === null, 404, 'A resendable invitation was not found.');
        $tenant = DB::table('tenants')->where('id', $context->tenantId())->first(['name']);

        $result = $invitations->resend(
            invitationId: $invitationId,
            email: $invitation->email_normalized,
            role: $invitation->membership_role,
            tenantId: $context->tenantId(),
            tenantName: (string) $tenant->name,
            inviterName: (string) $request->user()->name,
            actorId: (string) $request->user()->id,
            reason: trim($data['reason']),
        );

        return response()->json(['data' => $result, 'message' => 'A replacement single-use invitation has been queued for delivery.'], 202);
    }

    public function revoke(
        string $invitationId,
        Request $request,
        TenantContext $context,
        TenantInvitationService $invitations,
    ): JsonResponse {
        abort_unless($context->canAdministerTenant(), 403, 'Tenant administrator permission is required.');
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);
        $invitations->revoke($invitationId, trim($data['reason']));

        return response()->json(['data' => ['id' => $invitationId, 'status' => 'REVOKED']]);
    }

    public function register(Request $request, TenantInvitationService $invitations): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'regex:/^[a-f0-9]{64}$/'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'password' => ['required', 'string', Password::min(16)->letters()->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $result = $invitations->acceptForNewUser(
            token: $data['token'],
            email: mb_strtolower(trim($data['email'])),
            name: trim($data['name']),
            passwordHash: Hash::make($data['password']),
            userId: (string) Str::uuid(),
        );

        return response()->json([
            'data' => [
                'status' => 'ACCEPTED',
                'tenant_id' => $result['tenant_id'],
                'user_id' => $result['user_id'],
                'message' => 'Email verified and global account created. Sign in to open the invited workspace.',
            ],
        ], 201);
    }

    public function acceptForExistingUser(Request $request, TenantInvitationService $invitations): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'regex:/^[a-f0-9]{64}$/']]);
        $result = $invitations->acceptForExistingUser($data['token'], (string) $request->user()->id);

        return response()->json([
            'data' => ['status' => 'ACCEPTED', 'tenant_id' => $result['tenant_id'], 'user_id' => $result['user_id']],
        ]);
    }
}
