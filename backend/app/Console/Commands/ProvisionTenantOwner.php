<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $email = mb_strtolower(trim((string) $this->ask('Owner email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');
            return self::FAILURE;
        }

        if (DB::connection('pgsql_admin')->table('users')->where('email', $email)->exists()) {
            $this->error('This email is already registered.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('Owner password (minimum 16 characters)');
        $confirmation = (string) $this->secret('Confirm owner password');

        if (strlen($password) < 16 || ! hash_equals($password, $confirmation)) {
            $this->error('Password confirmation failed or the password is too short.');
            return self::FAILURE;
        }

        $tenantId = (string) Str::uuid();
        $userId = (string) Str::uuid();
        $now = now();

        $connection->transaction(function () use ($connection, $tenantId, $userId, $email, $password, $now): void {
            $connection->table('tenants')->insert([
                'id' => $tenantId,
                'code' => $this->argument('tenant_code'),
                'name' => $this->argument('tenant_name'),
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $connection->select("select set_config('app.current_tenant_id', ?, true)", [$tenantId]);

            $connection->table('users')->insert([
                'id' => $userId,
                'name' => $this->ask('Owner full name'),
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

        $this->info('Initial tenant owner provisioned. The password was not displayed or stored in plaintext.');
        $this->line('Tenant ID: '.$tenantId);
        $this->line('Owner email: '.$email);

        return self::SUCCESS;
    }
}
