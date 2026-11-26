<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlatformAdministrationService
{
    public function tenants(): array
    {
        try {
            return DB::select('SELECT * FROM public.platform_list_tenants() ORDER BY tenant_code');
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '42501') {
                throw new AuthorizationException('The authenticated identity does not have the required platform authority.');
            }

            throw $exception;
        }
    }

    public function changeTenantStatus(string $tenantId, string $status, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $status, $reason): object {
            return $this->call(
                'SELECT * FROM public.platform_change_tenant_status(?::uuid, ?, ?)',
                [$tenantId, $status, $reason],
            );
        }, 3);
    }

    public function setTenantEntitlements(
        string $tenantId,
        string $planCode,
        string $status,
        array $featureFlags,
        array $limits,
        ?string $validFrom,
        ?string $validTo,
        string $reason,
    ): object {
        return DB::transaction(function () use (
            $tenantId,
            $planCode,
            $status,
            $featureFlags,
            $limits,
            $validFrom,
            $validTo,
            $reason,
        ): object {
            return $this->call(
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
        }, 3);
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
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );
            }
        }

        return $entitlement;
    }

    private function call(string $sql, array $bindings): object
    {
        try {
            $result = DB::selectOne($sql, $bindings);
            if ($result === null) {
                throw new ValidationException(['platform' => 'The platform operation returned no result.']);
            }

            return $result;
        } catch (QueryException $exception) {
            $sqlState = $exception->errorInfo[0] ?? null;
            $message = $exception->getMessage();

            if ($sqlState === '42501') {
                throw new AuthorizationException('The authenticated identity does not have the required platform authority.');
            }

            if ($sqlState === 'P0002') {
                throw new ValidationException(['tenant_id' => 'The requested tenant does not exist.']);
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
                    str_contains($message, 'effective dates') => 'The supplied entitlement effective dates are invalid.',
                    default => 'The requested platform operation violates a lifecycle or entitlement rule.',
                };

                throw ValidationException::withMessages(['platform' => $safe]);
            }

            throw $exception;
        }
    }
}
