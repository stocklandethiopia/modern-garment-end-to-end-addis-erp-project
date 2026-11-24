<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TenantProvisioningService;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ProvisionTenantOwner extends Command
{
    protected $signature = 'platform:provision-tenant-owner
                            {tenant_code : Unique tenant code}
                            {tenant_name : Customer tenant display name}
                            {--locale=en : Tenant locale tag}
                            {--timezone=Africa/Addis_Ababa : Tenant IANA timezone}';

    protected $description = 'Provision a tenant and its first owner using the controlled migration connection';

    public function handle(TenantProvisioningService $provisioner): int
    {
        $tenantCode = trim((string) $this->argument('tenant_code'));
        $tenantName = trim((string) $this->argument('tenant_name'));
        $email = mb_strtolower(trim((string) $this->ask('Owner email')));

        try {
            $existingOwner = $provisioner->findOwner($email);
            $ownerName = null;
            $password = null;

            if ($existingOwner === null) {
                $ownerName = trim((string) $this->ask('Owner full name'));
                $password = (string) $this->secret('Owner password (minimum 16 characters)');
                $confirmation = (string) $this->secret('Confirm owner password');

                if (strlen($password) < 16 || ! hash_equals($password, $confirmation)) {
                    throw new InvalidArgumentException('Password confirmation failed or the password is too short.');
                }
            } elseif ($existingOwner->status !== 'ACTIVE') {
                throw new RuntimeException('The existing global user is not active; use the controlled identity recovery workflow.');
            }

            $result = $provisioner->provision(
                tenantCode: $tenantCode,
                tenantName: $tenantName,
                ownerEmail: $email,
                ownerName: $ownerName,
                ownerPassword: $password,
                locale: (string) $this->option('locale'),
                timezone: (string) $this->option('timezone'),
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Tenant provisioning failed unexpectedly. Review the protected application log.');

            return self::FAILURE;
        }

        $this->info('Tenant provisioning completed.');
        $this->line('Tenant ID: '.$result['tenant_id']);
        $this->line('Tenant code: '.$result['tenant_code']);
        $this->line('Owner email: '.$result['owner_email']);
        $this->line('Owner identity: '.($result['identity_created'] ? 'created' : 'existing global identity linked'));

        return self::SUCCESS;
    }
}
