<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantMembershipService
{
    public function members(string $tenantId): object
    {
        return DB::table('tenant_memberships')
            ->join('users', 'users.id', '=', 'tenant_memberships.user_id')
            ->where('tenant_memberships.tenant_id', $tenantId)
            ->orderByRaw("CASE tenant_memberships.membership_role WHEN 'OWNER' THEN 0 WHEN 'ADMIN' THEN 1 ELSE 2 END")
            ->orderBy('users.name')
            ->paginate(100, [
                'tenant_memberships.id',
                'tenant_memberships.user_id',
                'users.name',
                'users.email',
                'tenant_memberships.membership_role',
                'tenant_memberships.status',
                'tenant_memberships.joined_at',
                'tenant_memberships.revoked_at',
            ]);
    }

    public function add(string $userId, string $role, string $reason): string
    {
        return $this->call('SELECT public.add_tenant_member(?::uuid, ?, ?) AS membership_id', [$userId, $role, $reason]);
    }

    public function changeRole(string $userId, string $role, string $reason): string
    {
        return $this->call('SELECT public.change_tenant_member_role(?::uuid, ?, ?) AS membership_id', [$userId, $role, $reason]);
    }

    public function revoke(string $userId, string $reason): string
    {
        return DB::transaction(function () use ($userId, $reason): string {
            // The controlled PostgreSQL function revokes the membership. In the
            // same outer request transaction, revoke all its domain-role grants
            // so later membership reactivation cannot silently restore access.
            $membershipId = $this->call(
                'SELECT public.revoke_tenant_member(?::uuid, ?) AS membership_id',
                [$userId, $reason],
            );

            $context = DB::selectOne(
                "SELECT
                    NULLIF(current_setting('app.current_tenant_id', true), '')::text AS tenant_id,
                    NULLIF(current_setting('app.current_user_id', true), '')::text AS actor_id",
            );

            if (! is_string($context?->tenant_id) || $context->tenant_id === ''
                || ! is_string($context?->actor_id) || $context->actor_id === '') {
                throw new AuthorizationException('A verified tenant and actor context is required to revoke associated domain roles.');
            }

            $assignments = DB::table('tenant_domain_role_assignments')
                ->where('tenant_id', $context->tenant_id)
                ->where('user_id', $userId)
                ->whereIn('status', ['PENDING', 'ACTIVE'])
                ->lockForUpdate()
                ->get(['id', 'role_code']);

            $now = now();
            $revocationReason = 'Tenant membership revoked: '.trim($reason);

            foreach ($assignments as $assignment) {
                DB::table('tenant_domain_role_assignments')
                    ->where('tenant_id', $context->tenant_id)
                    ->where('id', $assignment->id)
                    ->update([
                        'status' => 'REVOKED',
                        'revoked_by' => $context->actor_id,
                        'revoked_at' => $now,
                        'revocation_reason' => $revocationReason,
                        'updated_at' => $now,
                    ]);

                DB::table('audit_events')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $context->tenant_id,
                    'actor_user_id' => $context->actor_id,
                    'action' => 'TENANT_DOMAIN_ROLE_REVOKED',
                    'object_type' => 'TENANT_DOMAIN_ROLE_ASSIGNMENT',
                    'object_id' => $assignment->id,
                    'scope' => json_encode([
                        'user_id' => $userId,
                        'role_code' => $assignment->role_code,
                        'reason_origin' => 'TENANT_MEMBERSHIP_REVOKED',
                    ], JSON_THROW_ON_ERROR),
                    'reason' => $revocationReason,
                    'correlation_id' => (string) Str::uuid(),
                    'occurred_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $membershipId;
        }, 3);
    }

    private function call(string $sql, array $bindings): string
    {
        try {
            $result = DB::selectOne($sql, $bindings);

            return (string) $result->membership_id;
        } catch (QueryException $exception) {
            $sqlState = $exception->errorInfo[0] ?? null;
            $message = $exception->getMessage();

            if ($sqlState === '42501') {
                throw new AuthorizationException($this->safeMessage($message, 'The requested membership action is not permitted.'));
            }

            if ($sqlState === '23505') {
                throw ValidationException::withMessages([
                    'email' => ['This user is already an active member of the selected tenant.'],
                ]);
            }

            if ($sqlState === 'P0002') {
                throw new AuthorizationException('The requested active membership or global user was not found.');
            }

            if ($sqlState === '22023') {
                throw ValidationException::withMessages([
                    'reason' => [$this->safeMessage($message, 'The membership change did not satisfy its validation rules.')],
                ]);
            }

            throw $exception;
        }
    }

    private function safeMessage(string $databaseMessage, string $fallback): string
    {
        $known = [
            'A verified tenant and actor context is required.',
            'Tenant owner or administrator permission is required.',
            'An administrator may add members but only an owner may grant administrator access.',
            'A revoked owner cannot be reactivated through ordinary member administration.',
            'A tenant owner or administrator permission is required.',
            'An administrator cannot reactivate or change another administrator membership.',
            'Only the tenant owner may change administrator roles.',
            'Owner membership is protected from ordinary role changes.',
            'The member already has the requested role.',
            'Use the separate leave-tenant process to revoke your own membership.',
            'Owner membership cannot be revoked through ordinary member administration.',
            'An administrator may revoke ordinary members but cannot revoke another administrator.',
        ];

        foreach ($known as $candidate) {
            if (str_contains($databaseMessage, $candidate)) {
                return $candidate;
            }
        }

        return $fallback;
    }
}
