<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantMembershipLifecycleTest extends TestCase
{
    private string $tenantId;

    private string $ownerId;

    private string $adminId;

    private string $memberId;

    private string $adminMemberId;

    private string $ownerTokenUserEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = DB::connection('pgsql_test_admin');
        $this->tenantId = (string) Str::uuid();
        $this->ownerId = (string) Str::uuid();
        $this->adminId = (string) Str::uuid();
        $this->memberId = (string) Str::uuid();
        $this->adminMemberId = (string) Str::uuid();
        $this->ownerTokenUserEmail = 'membership-owner-'.Str::lower(Str::random(8)).'@example.test';
        $now = now();

        $admin->table('tenants')->insert([
            'id' => $this->tenantId,
            'code' => 'MEMBERS-'.Str::upper(Str::random(8)),
            'name' => 'Membership lifecycle tenant',
            'status' => 'ACTIVE',
            'locale' => 'en',
            'timezone' => 'Africa/Addis_Ababa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            [$this->ownerId, $this->ownerTokenUserEmail, 'Membership Owner'],
            [$this->adminId, 'membership-admin-'.Str::lower(Str::random(8)).'@example.test', 'Membership Admin'],
            [$this->memberId, 'membership-member-'.Str::lower(Str::random(8)).'@example.test', 'Membership Member'],
            [$this->adminMemberId, 'membership-admin-target-'.Str::lower(Str::random(8)).'@example.test', 'Target Admin'],
        ] as [$id, $email, $name]) {
            $admin->table('users')->insert([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('long-test-account-password'),
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            [$this->ownerId, 'OWNER'],
            [$this->adminId, 'ADMIN'],
            [$this->adminMemberId, 'ADMIN'],
        ] as [$userId, $role]) {
            $admin->table('tenant_memberships')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenantId,
                'user_id' => $userId,
                'membership_role' => $role,
                'status' => 'ACTIVE',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function actAs(string $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Sanctum::actingAs($user, ['tenant:select', 'erp:access']);
        $this->withHeader('X-Tenant-Id', $this->tenantId);
    }

    public function test_owner_can_add_an_existing_global_identity_as_a_tenant_member(): void
    {
        $this->actAs($this->ownerId);

        $this->postJson('/api/v1/enterprise/members', [
            'email' => 'membership-member@example.test',
            'membership_role' => 'MEMBER',
            'reason' => 'Add an existing user to the garment workspace.',
        ])->assertUnprocessable();

        $email = DB::connection('pgsql_test_admin')->table('users')->where('id', $this->memberId)->value('email');

        $response = $this->postJson('/api/v1/enterprise/members', [
            'email' => $email,
            'membership_role' => 'MEMBER',
            'reason' => 'Add an existing user to the garment workspace.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonPath('data.user_id', $this->memberId);

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->memberId,
            'membership_role' => 'MEMBER',
            'status' => 'ACTIVE',
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'action' => 'TENANT_MEMBER_ADDED',
            'object_id' => $response->json('data.membership_id'),
        ], 'pgsql_test_admin');
    }

    public function test_administrator_can_add_member_but_cannot_grant_administrator_role(): void
    {
        $this->actAs($this->adminId);
        $email = DB::connection('pgsql_test_admin')->table('users')->where('id', $this->memberId)->value('email');

        $this->postJson('/api/v1/enterprise/members', [
            'email' => $email,
            'membership_role' => 'ADMIN',
            'reason' => 'Attempt to grant administrator access.',
        ])->assertForbidden();

        $this->postJson('/api/v1/enterprise/members', [
            'email' => $email,
            'membership_role' => 'MEMBER',
            'reason' => 'Add a standard member to the tenant.',
        ])->assertCreated();
    }

    public function test_only_owner_can_promote_member_to_administrator_and_role_change_is_audited(): void
    {
        $this->actAs($this->ownerId);
        $email = DB::connection('pgsql_test_admin')->table('users')->where('id', $this->memberId)->value('email');

        $this->postJson('/api/v1/enterprise/members', [
            'email' => $email,
            'membership_role' => 'MEMBER',
            'reason' => 'Add a standard member before promotion.',
        ])->assertCreated();

        $this->patchJson('/api/v1/enterprise/members/'.$this->memberId.'/role', [
            'membership_role' => 'ADMIN',
            'reason' => 'Promote the member to tenant administrator.',
        ])->assertOk();

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->memberId,
            'membership_role' => 'ADMIN',
            'status' => 'ACTIVE',
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'action' => 'TENANT_MEMBER_ROLE_CHANGED',
        ], 'pgsql_test_admin');
    }

    public function test_administrator_cannot_modify_another_administrator_role(): void
    {
        $this->actAs($this->adminId);

        $this->patchJson('/api/v1/enterprise/members/'.$this->adminMemberId.'/role', [
            'membership_role' => 'MEMBER',
            'reason' => 'Attempt to demote another administrator.',
        ])->assertForbidden();
    }

    public function test_owner_can_revoke_member_and_membership_audit_is_retained(): void
    {
        $this->actAs($this->ownerId);
        $email = DB::connection('pgsql_test_admin')->table('users')->where('id', $this->memberId)->value('email');

        $this->postJson('/api/v1/enterprise/members', [
            'email' => $email,
            'membership_role' => 'MEMBER',
            'reason' => 'Add a member before revocation.',
        ])->assertCreated();

        $this->postJson('/api/v1/enterprise/members/'.$this->memberId.'/revoke', [
            'reason' => 'Member access is no longer required.',
        ])->assertOk()->assertJsonPath('data.status', 'REVOKED');

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->memberId,
            'status' => 'REVOKED',
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'action' => 'TENANT_MEMBER_REVOKED',
        ], 'pgsql_test_admin');
    }

    public function test_administrator_cannot_revoke_another_administrator_or_owner(): void
    {
        $this->actAs($this->adminId);

        $this->postJson('/api/v1/enterprise/members/'.$this->adminMemberId.'/revoke', [
            'reason' => 'Attempt to revoke an administrator.',
        ])->assertForbidden();

        $this->postJson('/api/v1/enterprise/members/'.$this->ownerId.'/revoke', [
            'reason' => 'Attempt to revoke the tenant owner.',
        ])->assertForbidden();
    }

    public function test_tenant_member_list_is_available_only_to_tenant_administrators(): void
    {
        $this->actAs($this->ownerId);
        $this->getJson('/api/v1/enterprise/members')
            ->assertOk()
            ->assertJsonFragment(['email' => $this->ownerTokenUserEmail]);

        DB::connection('pgsql_test_admin')->table('tenant_memberships')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'user_id' => $this->memberId,
            'membership_role' => 'MEMBER',
            'status' => 'ACTIVE',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actAs($this->memberId);
        $this->getJson('/api/v1/enterprise/members')->assertForbidden();
    }

    public function test_app_runtime_has_no_direct_membership_write_but_can_execute_controlled_functions(): void
    {
        $runtime = DB::connection('pgsql');
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'tenant_memberships', 'INSERT')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'tenant_memberships', 'UPDATE')"));
        $this->assertTrue((bool) $runtime->scalar("SELECT has_function_privilege('app_runtime', 'public.add_tenant_member(uuid,text,text)', 'EXECUTE')"));
        $this->assertTrue((bool) $runtime->scalar("SELECT has_function_privilege('app_runtime', 'public.revoke_tenant_member(uuid,text)', 'EXECUTE')"));
    }
}
