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
        return DB::transaction(function () use ($userId, $role, $reason): string {
            DB::selectOne("SELECT set_config('app.current_action_reason', ?, true)", [$reason]);

            return $this->call(
                'SELECT public.change_tenant_member_role(?::uuid, ?, ?) AS membership_id',
                [$userId, $role, $reason],
            );
        }, 3);
    }

    public function revoke(string $userId, string $reason): string
    {
        return DB::transaction(function () use ($userId, $reason): string {
            DB::selectOne("SELECT set_config('app.current_action_reason', ?, true)", [$reason]);

            return $this->call(
                'SELECT public.revoke_tenant_member(?::uuid, ?) AS membership_id',
                [$userId, $reason],
            );
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
