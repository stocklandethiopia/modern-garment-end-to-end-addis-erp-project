<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ProvisionTenantOwner extends Command
{
    protected $signature = 'platform:provision-tenant-owner {tenant_code} {tenant_name}';

    protected $description = 'Provision the first tenant and owner using the migration connection in an interactive terminal';

    public function handle(): int
    {
        $connection = DB::connection('pgsql_migrator');

        if ($connection->table('tenants')->exists()) {
            $this->error('A tenant already exists. Use the controlled tenant-administration workflow.');

            return self::FAILURE;
        }

        $tenantCode = trim((string) $this->argument('tenant_code'));
        $tenantName = trim((string) $this->argument('tenant_name'));

        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/D', $tenantCode)) {
            $this->error('Tenant code must be 2–80 characters using letters, numbers, dot, underscore or hyphen.');

            return self::FAILURE;
        }

        if ($tenantName === '' || mb_strlen($tenantName) > 200) {
            $this->error('Tenant name is required and must not exceed 200 characters.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->ask('Owner email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::FAILURE;
        }

        if ($connection->table('users')->where('email', $email)->exists()) {
            $this->error('This email is already registered.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Owner password (minimum 16 characters)');
        $confirmation = (string) $this->secret('Confirm owner password');

        if (strlen($password) < 16 || ! hash_equals($password, $confirmation)) {
            $this->error('Password confirmation failed or the password is too short.');

            return self::FAILURE;
        }

        $ownerName = trim((string) $this->ask('Owner full name'));

        if ($ownerName === '' || mb_strlen($ownerName) > 160) {
            $this->error('Owner full name is required and must not exceed 160 characters.');

            return self::FAILURE;
        }

        $tenantId = (string) Str::uuid();
        $userId = (string) Str::uuid();
        $now = now();

        try {
            $connection->transaction(function () use (
                $connection,
                $tenantId,
                $tenantCode,
                $tenantName,
                $userId,
                $email,
                $password,
                $ownerName,
                $now,
            ): void {
                // Serialize first-owner creation and re-check under the same transaction.
                $connection->select("select pg_advisory_xact_lock(hashtext('modern-garment-erp-initial-owner'))");

                if ($connection->table('tenants')->exists()) {
                    throw new RuntimeException('A tenant was provisioned by another process.');
                }

                if ($connection->table('users')->where('email', $email)->exists()) {
                    throw new RuntimeException('This owner email is already registered.');
                }

                $connection->table('tenants')->insert([
                    'id' => $tenantId,
                    'code' => $tenantCode,
                    'name' => $tenantName,
                    'status' => 'ACTIVE',
                    'locale' => 'en',
                    'timezone' => 'Africa/Addis_Ababa',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // tenant_memberships and audit_events use FORCE RLS.
                $connection->select("select set_config('app.current_tenant_id', ?, true)", [$tenantId]);

                $connection->table('users')->insert([
                    'id' => $userId,
                    'name' => $ownerName,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $connection->table('tenant_memberships')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'membership_role' => 'OWNER',
                    'status' => 'ACTIVE',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $connection->table('audit_events')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'actor_user_id' => $userId,
                    'action' => 'TENANT_OWNER_PROVISIONED',
                    'object_type' => 'TENANT',
                    'object_id' => $tenantId,
                    'scope' => json_encode(['membership_role' => 'OWNER'], JSON_THROW_ON_ERROR),
                    'reason' => 'Initial interactive platform provisioning',
                    'occurred_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Initial tenant owner provisioned. The password was not displayed or stored in plaintext.');
        $this->line('Tenant ID: '.$tenantId);
        $this->line('Owner email: '.$email);

        return self::SUCCESS;
    }
}
