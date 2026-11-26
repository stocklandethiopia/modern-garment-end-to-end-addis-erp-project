<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlatformAdministrationTest extends TestCase
{
    private string $tenantId;
    private string $tenantOwnerId;
    private string $platformAdminId;
    private string $platformSupportId;
    private string $grantOfficerId;
    private string $securityApproverId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = (string) Str::uuid();
        $this->tenantOwnerId = (string) Str::uuid();
        $this->platformAdminId = (string) Str::uuid();
        $this->platformSupportId = (string) Str::uuid();
        $this->grantOfficerId = (string) Str::uuid();
        $this->securityApproverId = (string) Str::uuid();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        // Provision the tenant root and initial OWNER in one transaction.
        $admin->transaction(function () use ($admin, $now): void {
            $admin->table('tenants')->insert([
                'id' => $this->tenantId,
                'code' => 'PLATFORM-'.Str::upper(Str::random(8)),
                'name' => 'Platform Test Tenant',
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $users = [];
            foreach ([
                [$this->tenantOwnerId, 'Tenant Owner'],
                [$this->platformAdminId, 'Platform Administrator'],
                [$this->platformSupportId, 'Platform Support'],
                [$this->grantOfficerId, 'Platform Grant Officer'],
                [$this->securityApproverId, 'Platform Security Approver'],
            ] as [$id, $name]) {
                $users[] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => Str::lower(Str::slug($name)).'-'.Str::lower(Str::random(7)).'@example.test',
                    'password' => Hash::make('a-long-platform-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $admin->table('users')->insert($users);

            $admin->table('tenant_memberships')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenantId,
                'user_id' => $this->tenantOwnerId,
                'membership_role' => 'OWNER',
                'status' => 'ACTIVE',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        // Platform grants are deliberately provisioned through the privileged
        // test-fixture connection: app_runtime has no INSERT/UPDATE/DELETE path.
        $admin->table('platform_operator_grants')->insert([
            [
                'id' => (string) Str::uuid(),
                'user_id' => $this->platformAdminId,
                'role_code' => 'PLATFORM_ADMIN',
                'status' => 'ACTIVE',
                'valid_from' => now()->toDateString(),
                'grant_reason' => 'Test fixture platform administrator approval.',
                'granted_by' => $this->grantOfficerId,
                'approved_by' => $this->securityApproverId,
                'approved_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'user_id' => $this->platformSupportId,
                'role_code' => 'PLATFORM_SUPPORT',
                'status' => 'ACTIVE',
                'valid_from' => now()->toDateString(),
                'grant_reason' => 'Test fixture read-only support approval.',
                'granted_by' => $this->grantOfficerId,
                'approved_by' => $this->securityApproverId,
                'approved_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function actAs(string $userId): void
    {
        Sanctum::actingAs(User::query()->findOrFail($userId), ['tenant:select', 'erp:access']);
    }

    public function test_new_tenant_receives_unassigned_review_required_entitlement_by_default(): void
    {
        $this->actAs($this->tenantOwnerId);

        $response = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/tenant/entitlements')
            ->assertOk()
            ->assertJsonPath('data.plan_code', 'UNASSIGNED')
            ->assertJsonPath('data.entitlement_status', 'REVIEW_REQUIRED')
            ->assertJsonPath('data.feature_flags', [])
            ->assertJsonPath('data.limits', []);

        $this->assertSame($this->tenantId, $response->json('data.tenant_id'));
    }

    public function test_platform_admin_can_list_tenants_but_tenant_owner_cannot(): void
    {
        $this->actAs($this->platformAdminId);
        $this->getJson('/api/v1/platform/tenants')
            ->assertOk()
            ->assertJsonFragment(['tenant_id' => $this->tenantId])
            ->assertJsonFragment(['plan_code' => 'UNASSIGNED']);

        $this->actAs($this->tenantOwnerId);
        $this->getJson('/api/v1/platform/tenants')->assertForbidden();
    }

    public function test_platform_support_is_read_only_for_tenant_lifecycle_and_entitlements(): void
    {
        $this->actAs($this->platformSupportId);
        $this->getJson('/api/v1/platform/tenants')->assertOk();

        $this->patchJson('/api/v1/platform/tenants/'.$this->tenantId.'/lifecycle', [
            'status' => 'SUSPENDED',
            'reason' => 'Test support role must remain read only.',
        ])->assertForbidden();

        $this->putJson('/api/v1/platform/tenants/'.$this->tenantId.'/entitlements', [
            'plan_code' => 'BUSINESS',
            'status' => 'ACTIVE',
            'feature_flags' => ['manufacturing' => true],
            'limits' => ['sites' => 3],
            'valid_from' => now()->toDateString(),
            'reason' => 'Test support role cannot change entitlements.',
        ])->assertForbidden();
    }

    public function test_platform_admin_can_change_entitlements_and_tenant_owner_can_only_read_them(): void
    {
        $this->actAs($this->platformAdminId);
        $this->putJson('/api/v1/platform/tenants/'.$this->tenantId.'/entitlements', [
            'plan_code' => 'BUSINESS',
            'status' => 'ACTIVE',
            'feature_flags' => [
                'manufacturing' => true,
                'plm' => true,
                'advanced_analytics' => false,
            ],
            'limits' => [
                'sites' => 5,
                'users' => 100,
                'storage_gb' => 50,
            ],
            'valid_from' => now()->toDateString(),
            'reason' => 'Approved business plan provisioning for the tenant.',
        ])->assertOk()
            ->assertJsonPath('data.plan_code', 'BUSINESS')
            ->assertJsonPath('data.entitlement_status', 'ACTIVE');

        $this->actAs($this->tenantOwnerId);
        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/tenant/entitlements')
            ->assertOk()
            ->assertJsonPath('data.feature_flags.manufacturing', true)
            ->assertJsonPath('data.limits.sites', 5);

        $this->putJson('/api/v1/platform/tenants/'.$this->tenantId.'/entitlements', [
            'plan_code' => 'ENTERPRISE',
            'status' => 'ACTIVE',
            'feature_flags' => ['all' => true],
            'limits' => ['sites' => 999],
            'valid_from' => now()->toDateString(),
            'reason' => 'Tenant owner cannot self-assign platform plan.',
        ])->assertForbidden();
    }

    public function test_platform_lifecycle_suspend_reactivate_and_close_are_audited_and_terminal(): void
    {
        $this->actAs($this->platformAdminId);
        $this->patchJson('/api/v1/platform/tenants/'.$this->tenantId.'/lifecycle', [
            'status' => 'SUSPENDED',
            'reason' => 'Security review requires a temporary tenant suspension.',
        ])->assertOk()
            ->assertJsonPath('data.current_status', 'SUSPENDED');

        $this->assertDatabaseHas('platform_tenant_entitlements', [
            'tenant_id' => $this->tenantId,
            'entitlement_status' => 'SUSPENDED',
            'status_before_tenant_suspend' => 'REVIEW_REQUIRED',
        ], 'pgsql_test_admin');

        // A suspended tenant cannot establish a normal tenant-context request.
        $this->actAs($this->tenantOwnerId);
        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/tenant/entitlements')
            ->assertForbidden();

        $this->actAs($this->platformAdminId);
        $this->patchJson('/api/v1/platform/tenants/'.$this->tenantId.'/lifecycle', [
            'status' => 'ACTIVE',
            'reason' => 'Security review has cleared tenant reactivation.',
        ])->assertOk()
            ->assertJsonPath('data.current_status', 'ACTIVE');

        $this->assertDatabaseHas('platform_tenant_entitlements', [
            'tenant_id' => $this->tenantId,
            'entitlement_status' => 'REVIEW_REQUIRED',
            'status_before_tenant_suspend' => null,
        ], 'pgsql_test_admin');

        $this->patchJson('/api/v1/platform/tenants/'.$this->tenantId.'/lifecycle', [
            'status' => 'CLOSED',
            'reason' => 'Tenant closure was approved and records are retained.',
        ])->assertOk()
            ->assertJsonPath('data.current_status', 'CLOSED');

        $this->patchJson('/api/v1/platform/tenants/'.$this->tenantId.'/lifecycle', [
            'status' => 'ACTIVE',
            'reason' => 'Attempt to reopen a terminally closed tenant.',
        ])->assertForbidden();

        $this->assertDatabaseHas('platform_audit_events', [
            'tenant_id' => $this->tenantId,
            'action' => 'TENANT_LIFECYCLE_CHANGED',
        ], 'pgsql_test_admin');
        $this->assertSame(
            3,
            DB::connection('pgsql_test_admin')->table('platform_audit_events')
                ->where('tenant_id', $this->tenantId)
                ->where('action', 'TENANT_LIFECYCLE_CHANGED')
                ->count(),
        );
    }

    public function test_runtime_role_has_no_platform_write_or_audit_table_privileges(): void
    {
        $runtime = DB::connection('pgsql');

        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_operator_grants', 'INSERT')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_operator_grants', 'UPDATE')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_operator_grants', 'DELETE')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_tenant_entitlements', 'INSERT')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_tenant_entitlements', 'UPDATE')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_tenant_entitlements', 'DELETE')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'platform_audit_events', 'SELECT')"));
        $this->assertTrue((bool) $runtime->scalar("SELECT has_function_privilege('app_runtime', 'public.platform_change_tenant_status(uuid,text,text)', 'EXECUTE')"));
        $this->assertTrue((bool) $runtime->scalar("SELECT has_function_privilege('app_runtime', 'public.platform_set_tenant_entitlements(uuid,text,text,jsonb,jsonb,date,date,text)', 'EXECUTE')"));
    }
}
