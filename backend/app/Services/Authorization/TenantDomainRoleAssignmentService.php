<?php

namespace App\Services\Authorization;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantDomainRoleAssignmentService
{
    public function forTenant(string $tenantId, ?string $userId = null): object
    {
        return DB::table('tenant_domain_role_assignments')
            ->where('tenant_id', $tenantId)
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->paginate(100);
    }

    public function create(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $actor = $this->activeMembership($tenantId, $actorId);
            if ($actor === null || ! in_array($actor->membership_role, ['OWNER', 'ADMIN'], true)) {
                throw ValidationException::withMessages(['actor' => 'Tenant owner or administrator permission is required.']);
            }

            $target = $this->activeMembership($tenantId, $data['user_id']);
            if ($target === null || $target->membership_role !== 'MEMBER') {
                throw ValidationException::withMessages(['user_id' => 'A domain role may only be requested for an active ordinary tenant member.']);
            }
            if (trim($data['grant_reason']) === '') {
                throw ValidationException::withMessages(['grant_reason' => 'A reason is required for domain-role assignment.']);
            }

            DB::selectOne(
                'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$tenantId.':domain-role:'.$data['user_id'].':'.$data['role_code']],
            );

            $validFrom = $data['valid_from'] ?? now()->toDateString();
            $validTo = $data['valid_to'] ?? null;

            // Only overlapping pending/active effective periods block a new grant.
            // An expired assignment remains in the audit history but must not
            // prevent a new, non-overlapping role period from being requested.
            $existing = DB::table('tenant_domain_role_assignments')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $data['user_id'])
                ->where('role_code', $data['role_code'])
                ->whereIn('status', ['PENDING', 'ACTIVE'])
                ->whereDate('valid_from', '<=', $validTo ?? '9999-12-31')
                ->where(function ($query) use ($validFrom): void {
                    $query->whereNull('valid_to')
                        ->orWhereDate('valid_to', '>=', $validFrom);
                })
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages(['role_code' => 'An overlapping pending or active assignment already exists for this role.']);
            }

            $id = (string) Str::uuid();
            DB::table('tenant_domain_role_assignments')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'user_id' => $data['user_id'],
                'role_code' => $data['role_code'],
                'scope_type' => 'TENANT',
                'scope_id' => null,
                'status' => 'PENDING',
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'grant_reason' => $data['grant_reason'],
                'granted_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actorId, 'TENANT_DOMAIN_ROLE_REQUESTED', $id, [
                'user_id' => $data['user_id'],
                'role_code' => $data['role_code'],
                'status' => 'PENDING',
            ], $data['grant_reason']);

            return DB::table('tenant_domain_role_assignments')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function approve(string $tenantId, string $actorId, string $assignmentId, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $assignmentId, $reason): object {
            // Read the immutable recipient key first, without locking the
            // assignment. Membership rows must be locked before the assignment
            // row to match the membership-change trigger's lock order.
            $snapshot = DB::table('tenant_domain_role_assignments')
                ->where('tenant_id', $tenantId)
                ->where('id', $assignmentId)
                ->first();

            if ($snapshot === null || $snapshot->status !== 'PENDING') {
                throw ValidationException::withMessages(['assignment' => 'Only a pending role assignment can be approved.']);
            }
            if ($snapshot->granted_by === $actorId || $snapshot->user_id === $actorId) {
                throw ValidationException::withMessages(['conflict_of_duties' => 'The requester or role recipient cannot approve this assignment.']);
            }
            if (trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'An approval reason is required.']);
            }

            // Always acquire the two membership locks in a stable user-id order.
            $memberIds = array_values(array_unique([$actorId, $snapshot->user_id]));
            sort($memberIds, SORT_STRING);
            $memberships = [];
            foreach ($memberIds as $memberId) {
                $memberships[$memberId] = $this->activeMembership($tenantId, $memberId);
            }

            $actor = $memberships[$actorId] ?? null;
            if ($actor === null || $actor->membership_role !== 'OWNER') {
                throw ValidationException::withMessages(['approver' => 'Only the tenant owner can approve domain-role assignments.']);
            }

            $target = $memberships[$snapshot->user_id] ?? null;
            if ($target === null || $target->membership_role !== 'MEMBER') {
                throw ValidationException::withMessages(['user_id' => 'Only an active ordinary tenant member can receive a domain role.']);
            }

            $assignment = DB::table('tenant_domain_role_assignments')
                ->where('tenant_id', $tenantId)
                ->where('id', $assignmentId)
                ->lockForUpdate()
                ->first();

            if ($assignment === null || $assignment->status !== 'PENDING'
                || $assignment->user_id !== $snapshot->user_id
                || $assignment->granted_by !== $snapshot->granted_by) {
                throw ValidationException::withMessages(['assignment' => 'The pending assignment changed before approval. Refresh and review it again.']);
            }

            DB::table('tenant_domain_role_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->update([
                'status' => 'ACTIVE',
                'approved_by' => $actorId,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actorId, 'TENANT_DOMAIN_ROLE_APPROVED', $assignmentId, [
                'user_id' => $assignment->user_id,
                'role_code' => $assignment->role_code,
            ], $reason);

            return DB::table('tenant_domain_role_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->first();
        }, 3);
    }

    public function revoke(string $tenantId, string $actorId, string $assignmentId, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $assignmentId, $reason): object {
            $actor = $this->activeMembership($tenantId, $actorId);
            if ($actor === null || ! in_array($actor->membership_role, ['OWNER', 'ADMIN'], true)) {
                throw ValidationException::withMessages(['actor' => 'Tenant owner or administrator permission is required.']);
            }
            if (trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'A revocation reason is required.']);
            }

            $assignment = DB::table('tenant_domain_role_assignments')->where('tenant_id', $tenantId)
                ->where('id', $assignmentId)->lockForUpdate()->first();
            if ($assignment === null || ! in_array($assignment->status, ['PENDING', 'ACTIVE'], true)) {
                throw ValidationException::withMessages(['assignment' => 'Only a pending or active assignment can be revoked.']);
            }

            DB::table('tenant_domain_role_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->update([
                'status' => 'REVOKED',
                'revoked_by' => $actorId,
                'revoked_at' => now(),
                'revocation_reason' => $reason,
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actorId, 'TENANT_DOMAIN_ROLE_REVOKED', $assignmentId, [
                'user_id' => $assignment->user_id,
                'role_code' => $assignment->role_code,
            ], $reason);

            return DB::table('tenant_domain_role_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->first();
        }, 3);
    }

    private function activeMembership(string $tenantId, string $userId): ?object
    {
        $row = DB::selectOne(
            'SELECT public.lock_active_tenant_membership(?::uuid, ?::uuid) AS membership_role',
            [$tenantId, $userId],
        );

        if (! is_string($row?->membership_role)) {
            return null;
        }

        return (object) ['membership_role' => $row->membership_role];
    }

    private function audit(string $tenantId, string $actorId, string $action, string $objectId, array $scope, string $reason): void
    {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => 'TENANT_DOMAIN_ROLE_ASSIGNMENT',
            'object_id' => $objectId,
            'scope' => json_encode($scope, JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
