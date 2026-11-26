<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantOwnershipService
{
    public function transfer(string $tenantId, string $actorId, string $targetUserId, string $reason): string
    {
        return DB::transaction(function () use ($tenantId, $actorId, $targetUserId, $reason): string {
            DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$tenantId]);
            DB::selectOne("SELECT set_config('app.current_user_id', ?, true)", [$actorId]);
            DB::selectOne("SELECT set_config('app.current_action_reason', ?, true)", [$reason]);

            return $this->call(
                'SELECT public.transfer_tenant_ownership(?::uuid, ?) AS membership_id',
                [$targetUserId, $reason],
            );
        }, 3);
    }

    public function leave(string $tenantId, string $actorId, string $reason): string
    {
        return DB::transaction(function () use ($tenantId, $actorId, $reason): string {
            DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$tenantId]);
            DB::selectOne("SELECT set_config('app.current_user_id', ?, true)", [$actorId]);
            DB::selectOne("SELECT set_config('app.current_action_reason', ?, true)", [$reason]);

            return $this->call(
                'SELECT public.leave_tenant(?) AS membership_id',
                [$reason],
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
                $known = [
                    'Only the current active tenant OWNER may transfer ownership.',
                    'Transfer tenant ownership to another active member before leaving.',
                    'A verified tenant and actor context is required.',
                ];

                foreach ($known as $candidate) {
                    if (str_contains($message, $candidate)) {
                        throw new AuthorizationException($candidate);
                    }
                }

                throw new AuthorizationException('The requested tenant ownership action is not permitted.');
            }

            if (in_array($sqlState, ['22023', '23514'], true)) {
                $safeMessage = str_contains($message, 'active MEMBER or ADMIN')
                    ? 'Ownership may only be transferred to an active MEMBER or ADMIN of this tenant.'
                    : (str_contains($message, '10 to 2000 characters')
                        ? 'A reason of 10 to 2000 characters is required.'
                        : 'The tenant ownership invariant would be violated.');
                throw ValidationException::withMessages(['tenant' => [$safeMessage]]);
            }

            if ($sqlState === 'P0002') {
                throw new AuthorizationException('An active tenant membership was not found.');
            }

            if ($sqlState === '40001') {
                throw ValidationException::withMessages(['tenant' => ['Tenant membership changed concurrently. Refresh and retry.']]);
            }

            throw $exception;
        }
    }
}
