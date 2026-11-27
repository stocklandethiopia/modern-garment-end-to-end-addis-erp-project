<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlatformAdministrationService
{
    public function tenants(string $actorId): array
    {
        try {
            return $this->runControlSelect($actorId, null, 'SELECT * FROM public.platform_list_tenants() ORDER BY tenant_code');
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '42501') {
                throw new AuthorizationException('The authenticated identity does not have the required platform authority.');
            }

            throw $exception;
        }
    }

    public function currentOperatorRoles(string $actorId): array
    {
        try {
            return array_values(array_map(
                static fn (object $row): string => (string) $row->role_code,
                $this->runControlSelect($actorId, null, 'SELECT role_code FROM public.platform_current_operator_roles()'),
            ));
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '42501') {
                throw new AuthorizationException('The authenticated platform identity could not be resolved.');
            }

            throw $exception;
        }
    }

    public function operatorGrants(string $actorId): array
    {
        try {
            return $this->runControlSelect($actorId, null, 'SELECT * FROM public.platform_list_operator_grants()');
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '42501') {
                throw new AuthorizationException('An active platform administrator grant is required.');
            }

            throw $exception;
        }
    }

    public function requestOperatorGrant(
        string $actorId,
        string $userEmail,
        string $roleCode,
        ?string $validFrom,
        ?string $validTo,
        string $reason,
    ): object {
        return $this->call($actorId, null,
            'SELECT * FROM public.platform_request_operator_grant(?, ?, ?::date, ?::date, ?)',
            [$userEmail, $roleCode, $validFrom, $validTo, $reason],
        ), 3);
    }

    public function approveOperatorGrant(string $actorId, string $grantId, string $reason): object
    {
        return $this->call($actorId, null,
            'SELECT * FROM public.platform_approve_operator_grant(?::uuid, ?)',
            [$grantId, $reason],
        ), 3);
    }

    public function revokeOperatorGrant(string $actorId, string $grantId, string $reason): object
    {
        return $this->call($actorId, null,
            'SELECT * FROM public.platform_revoke_operator_grant(?::uuid, ?)',
            [$grantId, $reason],
        ), 3);
    }

    public function changeTenantStatus(string $tenantId, string $status, string $reason): object
    {
        return $this->call(
            $actorId,
            null,
            'SELECT * FROM public.platform_change_tenant_status(?::uuid, ?, ?)',
            [$tenantId, $status, $reason],
        );
    }

    public function setTenantEntitlements(
        string $actorId,
        string $tenantId,
        string $planCode,
        string $status,
        array $featureFlags,
        array $limits,
        ?string $validFrom,
        ?string $validTo,
        string $reason,
    ): object {
        return $this->call(
            $actorId,
            null,
            'SELECT * FROM public.platform_set_tenant_entitlements(
                ?::uuid, ?, ?, ?::jsonb, ?::jsonb, ?::date, ?::date, ?
            )',
            [
                $tenantId,
                $planCode,
                $status,
                json_encode($featureFlags, JSON_THROW_ON_ERROR),
                json_encode($limits, JSON_THROW_ON_ERROR),
                $validFrom,
                $validTo,
                $reason,
            ],
        );
    }

    public function currentEntitlement(string $tenantId): ?object
    {
        $entitlement = DB::selectOne(
            'SELECT * FROM public.platform_get_current_entitlement(?::uuid)',
            [$tenantId],
        );

        if ($entitlement === null) {
            return null;
        }

        foreach (['feature_flags', 'limits'] as $jsonField) {
            if (is_string($entitlement->{$jsonField} ?? null)) {
                $entitlement->{$jsonField} = json_decode(
                    $entitlement->{$jsonField},
                    false,
                    512,
                    JSON_THROW_ON_ERROR,
                );
            }
        }

        return $entitlement;
    }

    /**
     * Execute a SECURITY DEFINER control API through the isolated app_control
     * database identity. The ordinary app_runtime role cannot EXECUTE these
     * functions, even when an injected query sets arbitrary custom GUC values.
     *
     * Actor and tenant values come from the authenticated request context.
     */
    private function runControlSelect(
        string $actorId,
        ?string $tenantId,
        string $sql,
        array $bindings = [],
        bool $single = false,
    ): array|object|null {
        $connection = DB::connection('pgsql_control');

        return $connection->transaction(function () use ($connection, $actorId, $tenantId, $sql, $bindings, $single): array|object|null {
            $connection->select("SELECT set_config('app.current_user_id', ?, true)", [$actorId]);
            $connection->select("SELECT set_config('app.current_tenant_id', ?, true)", [$tenantId ?? '']);

            return $single
                ? $connection->selectOne($sql, $bindings)
                : $connection->select($sql, $bindings);
        }, 3);
    }

    private function call(string $actorId, ?string $tenantId, string $sql, array $bindings): object
    {
        try {
            $result = $this->runControlSelect($actorId, $tenantId, $sql, $bindings, true);
            if ($result === null) {
                throw ValidationException::withMessages(['platform' => 'The platform operation returned no result.']);
            }

            return $result;
        } catch (QueryException $exception) {
            $sqlState = $exception->errorInfo[0] ?? null;
            $message = $exception->getMessage();

            if ($sqlState === '42501') {
                throw new AuthorizationException('The authenticated identity does not have the required platform authority.');
            }

            if ($sqlState === 'P0002') {
                $safe = str_contains($message, 'target identity')
                    ? 'An active account with that email address was not found.'
                    : (str_contains($message, 'platform operator grant')
                        ? 'The requested platform operator grant is no longer pending or active.'
                        : 'The requested tenant does not exist.');
                throw ValidationException::withMessages(['platform' => $safe]);
            }

            if (in_array($sqlState, ['22023', '23514'], true)) {
                $safe = match (true) {
                    str_contains($message, 'CLOSED tenant') => 'A closed tenant cannot be reactivated.',
                    str_contains($message, 'already has the requested lifecycle') => 'The tenant already has that lifecycle status.',
                    str_contains($message, 'transition is not allowed') => 'The requested tenant lifecycle transition is not allowed.',
                    str_contains($message, 'inactive tenant') => 'Entitlements cannot be activated for a suspended or closed tenant.',
                    str_contains($message, 'reason') => 'A valid reason of 10 to 500 characters is required.',
                    str_contains($message, 'Plan code') => 'The supplied plan code is not supported.',
                    str_contains($message, 'Entitlement status') => 'The supplied entitlement status is not supported.',
                    str_contains($message, 'JSON objects') => 'Feature flags and limits must be JSON objects.',
                    str_contains($message, 'effective dates') || str_contains($message, 'validity dates') => 'The supplied effective dates are invalid.',
                    str_contains($message, 'last active platform administrator') => 'The last active platform administrator cannot be removed.',
                    str_contains($message, 'requester and the grant recipient') => 'The grant requester and recipient must be different from the approving administrator.',
                    str_contains($message, 'cannot request platform access for their own identity') => 'An administrator cannot request platform access for their own account.',
                    str_contains($message, 'already exists for this account and role') => 'A pending or active grant already exists for this account and role.',
                    str_contains($message, 'grant reason') => 'A reason of 10 to 500 characters is required.',
                    str_contains($message, 'approval reason') => 'An approval reason of 10 to 500 characters is required.',
                    str_contains($message, 'revocation reason') => 'A revocation reason of 10 to 500 characters is required.',
                    str_contains($message, 'no longer active') => 'The target account is no longer active.',
                    str_contains($message, 'cannot revoke their own platform grant') => 'An administrator cannot revoke their own account.',
                    default => 'The requested platform operation violates a lifecycle, entitlement or operator-governance rule.',
                };

                throw ValidationException::withMessages(['platform' => $safe]);
            }

            throw $exception;
        }
    }
}
