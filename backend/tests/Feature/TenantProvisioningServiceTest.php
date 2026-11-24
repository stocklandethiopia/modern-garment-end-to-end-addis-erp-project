<?php

declare(strict_types=1);

namespace Tests\\Feature;

use App\\Services\\TenantProvisioningService;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Str;
use InvalidArgumentException;
use RuntimeException;
use Tests\\TestCase;

class TenantProvisioningServiceTest extends TestCase
{
    public function test_one_global_identity_can_own_multiple_customer_tenants(): void
    {
        $admin = DB::connection('pgsql_test_admin');
        $userId = (string) Str::uuid();
        $email = 'multi-tenant-'.Str::lower(Str::random(8)).'@example.test';
        $now = now();

        $admin->table('users')->insert([
            'id' => $userId,
            'name' => 'Shared Global Identity',
            'email' => $email,
            'password' => Hash::make('existing-owner-password'),
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $service = app(TenantProvisioningService::class);
        $first = $service->provision('CUSTOMER-ONE', 'Customer One', $email);
        $second = $service->provision('CUSTOMER-TWO', 'Customer Two', strtoupper($email));

        $this->assertFalse($first['identity_created']);
        $this->assertFalse($second['identity_created']);
        $this->assertSame($userId, $first['owner_user_id']);
        $this->assertSame($userId, $second['owner_user_id']);
        $this->assertSame(2, $admin->table('tenants')->whereIn('id', [
            $first['tenant_id'],
            $second['tenant_id'],
        ])->count());
        $this->assertSame(2, $admin->table('tenant_memberships')
            ->where('user_id', $userId)
            ->whereIn('tenant_id', [$first['tenant_id'], $second['tenant_id']])
            ->where('membership_role', 'OWNER')
            ->where('status', 'ACTIVE')
            ->count());
        $this->assertSame(2, $admin->table('audit_events')
            ->whereIn('object_id', [$first['tenant_id'], $second['tenant_id']])
            ->where('action', 'TENANT_PROVISIONED')
            ->whereNull('actor_user_id')
            ->count());
    }

    public function test_new_tenant_owner_identity_is_created_with_a_one_way_password_hash(): void
    {
        $email = 'new-owner-'.Str::lower(Str::random(8)).'@example.test';
        $password = 'long-test-owner-password-2026';

        $result = app(TenantProvisioningService::class)->provision(
            tenantCode: 'NEW-CUSTOMER',
            tenantName: 'New Customer Garments Ltd',
            ownerEmail: $email,
            ownerName: 'New Customer Owner',
            ownerPassword: $password,
            locale: 'am-ET',
            timezone: 'Africa/Addis_Ababa',
        );

        $user = DB::connection('pgsql_test_admin')->table('users')->where('id', $result['owner_user_id'])->first();

        $this->assertTrue($result['identity_created']);
        $this->assertSame('am-ET', DB::connection('pgsql_test_admin')->table('tenants')->where('id', $result['tenant_id'])->value('locale'));
        $this->assertSame('Africa/Addis_Ababa', DB::connection('pgsql_test_admin')->table('tenants')->where('id', $result['tenant_id'])->value('timezone'));
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotSame($password, $user->password);
        $this->assertSame(1, DB::connection('pgsql_test_admin')->table('tenant_memberships')
            ->where('tenant_id', $result['tenant_id'])
            ->where('user_id', $user->id)
            ->where('membership_role', 'OWNER')
            ->count());
    }

    public function test_duplicate_tenant_code_is_rejected_without_creating_another_identity(): void
    {
        $service = app(TenantProvisioningService::class);
        $email1 = 'owner-one-'.Str::lower(Str::random(8)).'@example.test';
        $email2 = 'owner-two-'.Str::lower(Str::random(8)).'@example.test';

        $service->provision('DUPLICATE-CODE', 'First Customer', $email1, 'Owner One', 'owner-password-long-0001');

        try {
            $service->provision('duplicate-code', 'Second Customer', $email2, 'Owner Two', 'owner-password-long-0002');
            $this->fail('A duplicate tenant code must be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertSame('A tenant with this code already exists.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('users', ['email' => $email2], 'pgsql_test_admin');
        $this->assertDatabaseMissing('tenants', ['name' => 'Second Customer'], 'pgsql_test_admin');
    }

    public function test_inactive_global_identity_cannot_be_reused_as_a_tenant_owner(): void
    {
        $admin = DB::connection('pgsql_test_admin');
        $email = 'inactive-owner-'.Str::lower(Str::random(8)).'@example.test';
        $admin->table('users')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Inactive Owner',
            'email' => $email,
            'password' => Hash::make('owner-password-long-0003'),
            'status' => 'DISABLED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The existing global user is not active; use the controlled identity recovery workflow.');

        app(TenantProvisioningService::class)->provision('INACTIVE-TEST', 'Inactive Tenant', $email);
    }

    public function test_new_identity_requires_a_strong_password_and_valid_locale_timezone(): void
    {
        $service = app(TenantProvisioningService::class);

        try {
            $service->provision('WEAK-PASSWORD', 'Weak Password Tenant', 'weak-owner@example.test', 'Weak Owner', 'short');
            $this->fail('A short owner password must be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('at least 16 characters', $exception->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tenant timezone must be a recognized IANA timezone.');

        $service->provision(
            'BAD-TIMEZONE',
            'Bad Timezone Tenant',
            'bad-timezone-owner@example.test',
            'Timezone Owner',
            'owner-password-long-0004',
            'en',
            'Mars/Olympus_Mons',
        );
    }
}
