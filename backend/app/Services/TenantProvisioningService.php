<?php

declare(strict_types=1);

namespace App\\Services;

use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Str;
use InvalidArgumentException;
use RuntimeException;

class TenantProvisioningService
{
    /**
     * Find a global identity without exposing user records through the tenant API.
     * This method is used only by the controlled platform provisioning command.
     */
    public function findOwner(string $email): ?object
    {
        $normalizedEmail = $this->normalizeEmail($email);

        return DB::connection('pgsql_migrator')
            ->table('users')
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();
    }

    /**
     * Provision a new tenant root and its first OWNER membership.
     *
     * Existing active global identities may own multiple tenants. Existing
     * identities are never silently renamed or have their password reset here.
     * All writes run through the migration/provisioning connection, never the
     * HTTP app_runtime connection.
     *
     * @return array{tenant_id:string,tenant_code:string,owner_user_id:string,owner_email:string,identity_created:bool}
     */
    public function provision(
        string $tenantCode,
        string $tenantName,
        string $ownerEmail,
        ?string $ownerName = null,
        ?string $ownerPassword = null,
        string $locale = 'en',
        string $timezone = 'Africa/Addis_Ababa',
    ): array {
        $tenantCode = strtoupper(trim($tenantCode));
        $tenantName = trim($tenantName);
        $normalizedEmail = $this->normalizeEmail($ownerEmail);
        $locale = trim($locale);
        $timezone = trim($timezone);

        if (! preg_match('/^[A-Z0-9][A-Z0-9._-]{1,79}$/D', $tenantCode)) {
            throw new InvalidArgumentException('Tenant code must be 2–80 letters, numbers, dots, underscores or hyphens.');
        }

        if ($tenantName === '' || mb_strlen($tenantName) > 200) {
            throw new InvalidArgumentException('Tenant name is required and must not exceed 200 characters.');
        }

        if (! preg_match('/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8}){0,2}$/D', $locale) || mb_strlen($locale) > 12) {
            throw new InvalidArgumentException('Tenant locale must be a valid short language tag (for example en or am-ET).');
        }

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException('Tenant timezone must be a recognized IANA timezone.');
        }

        $connection = DB::connection('pgsql_migrator');
        $tenantId = (string) Str::uuid();

        return $connection->transaction(function () use (
            $connection,
            $tenantCode,
            $tenantName,
            $normalizedEmail,
            $ownerName,
            $ownerPassword,
            $locale,
            $timezone,
            $tenantId,
        ): array {
            // Acquire deterministic transaction locks for both unique resources.
            // This avoids races when two platform operators provision the same
            // tenant code or the same global identity concurrently.
            $lockKeys = [
                'modern-garment-erp:tenant-code:'.$tenantCode,
                'modern-garment-erp:owner-email:'.$normalizedEmail,
            ];
            sort($lockKeys, SORT_STRING);

            foreach ($lockKeys as $lockKey) {
                $connection->selectOne(
                    'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                    [$lockKey],
                );
            }

            if ($connection->table('tenants')->where('code', $tenantCode)->exists()) {
                throw new RuntimeException('A tenant with this code already exists.');
            }

            $owner = $connection->table('users')
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->lockForUpdate()
                ->first();

            $identityCreated = false;
            if ($owner !== null) {
                if ($owner->status !== 'ACTIVE') {
                    throw new RuntimeException('The existing global user is not active; use the controlled identity recovery workflow.');
                }

                $ownerUserId = (string) $owner->id;
            } else {
                $ownerName = trim((string) $ownerName);

                if ($ownerName === '' || mb_strlen($ownerName) > 160) {
                    throw new InvalidArgumentException('Owner full name is required for a new global user and must not exceed 160 characters.');
                }

                if (! is_string($ownerPassword) || strlen($ownerPassword) < 16) {
                    throw new InvalidArgumentException('A new owner password must contain at least 16 characters.');
                }

                $ownerUserId = (string) Str::uuid();
                $connection->table('users')->insert([
                    'id' => $ownerUserId,
                    'name' => $ownerName,
                    'email' => $normalizedEmail,
                    'password' => Hash::make($ownerPassword),
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $identityCreated = true;
            }

            if ($connection->table('tenant_memberships')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $ownerUserId)
                ->exists()) {
                throw new RuntimeException('The owner is already a member of this tenant.');
            }

            $now = now();
            $connection->table('tenants')->insert([
                'id' => $tenantId,
                'code' => $tenantCode,
                'name' => $tenantName,
                'status' => 'ACTIVE',
                'locale' => $locale,
                'timezone' => $timezone,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Set transaction-local context even on the privileged provisioning
            // connection so audit rows carry the same tenant context as runtime
            // operations. The role is BYPASSRLS only in controlled provisioning.
            $connection->select(
                "select set_config('app.current_tenant_id', ?, true)",
                [$tenantId],
            );
            $connection->select(
                "select set_config('app.current_user_id', ?, true)",
                [$ownerUserId],
            );

            $connection->table('tenant_memberships')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'user_id' => $ownerUserId,
                'membership_role' => 'OWNER',
                'status' => 'ACTIVE',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $connection->table('audit_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'actor_user_id' => null,
                'action' => 'TENANT_PROVISIONED',
                'object_type' => 'TENANT',
                'object_id' => $tenantId,
                'scope' => json_encode([
                    'tenant_code' => $tenantCode,
                    'owner_user_id' => $ownerUserId,
                    'identity_created' => $identityCreated,
                    'provisioning_channel' => 'CONTROLLED_PLATFORM_CLI',
                ], JSON_THROW_ON_ERROR),
                'reason' => 'Controlled platform provisioning created a tenant root and first owner membership.',
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return [
                'tenant_id' => $tenantId,
                'tenant_code' => $tenantCode,
                'owner_user_id' => $ownerUserId,
                'owner_email' => $normalizedEmail,
                'identity_created' => $identityCreated,
            ];
        });
    }

    private function normalizeEmail(string $email): string
    {
        $normalized = mb_strtolower(trim($email));

        if (mb_strlen($normalized) > 254 || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('A valid owner email address is required.');
        }

        return $normalized;
    }
}
