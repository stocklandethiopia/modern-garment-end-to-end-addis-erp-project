<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScopedAccessMutationSecurityTest extends TestCase
{
    private string $tenantId;

    private string $ownerId;

    private string $adminId;

    private string $memberId;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = DB::connection('pgsql_test_admin');
        $this->tenantId = (string) Str::uuid();
        $this->ownerId = (string) Str::uuid();
        $this->adminId = (string) Str::uuid();
        $this->memberId = (string) Str::uuid();
        $now = now();

        $admin->transaction(function () use ($admin, $now): void {
            $admin->table('tenants')->insert([
                'id' => $this->tenantId,
                'code' => 'SCOPE-'.Str::upper(Str::random(8)),
                'name' => 'Scope mutation security tenant',
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $users = [];
            foreach ([
                [$this->ownerId, 'Scope Owner'],
                [$this->adminId, 'Scope Administrator'],
                [$this->memberId, 'Scope Member'],
            ] as [$id, $name]) {
                $users[] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => Str::lower(Str::slug($name)).'-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('a-long-scope-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $admin->table('users')->insert($users);

            $memberships = [];
            foreach ([
                [$this->ownerId, 'OWNER'],
                [$this->adminId, 'ADMIN'],
                [$this->memberId, 'MEMBER'],
            ] as [$userId, $role]) {
                $memberships[] = [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $this->tenantId,
                    'user_id' => $userId,
                    'membership_role' => $role,
                    'status' => 'ACTIVE',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $admin->table('tenant_memberships')->insert($memberships);
        });
    }

    private function actAs(string $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Sanctum::actingAs($user, ['tenant:select', 'erp:access']);
        $this->withHeader('X-Tenant-Id', $this->tenantId);
    }

    public function test_runtime_has_read_only_table_access_and_narrow_scope_mutation_functions(): void
    {
        $runtime = DB::connection('pgsql');

        foreach (['INSERT', 'UPDATE', 'DELETE'] as $privilege) {
            $this->assertFalse(
                (bool) $runtime->scalar(
                    "SELECT has_table_privilege('app_runtime', 'public.tenant_access_scope_modes', ?)",
                    [$privilege],
                ),
                "app_runtime must not have {$privilege} privilege on tenant_access_scope_modes.",
            );
            $this->assertFalse(
                (bool) $runtime->scalar(
                    "SELECT has_table_privilege('app_runtime', 'public.tenant_access_scopes', ?)",
                    [$privilege],
                ),
                "app_runtime must not have {$privilege} privilege on tenant_access_scopes.",
            );
        }

        $this->assertTrue((bool) $runtime->scalar(
            "SELECT has_table_privilege('app_runtime', 'public.tenant_access_scope_modes', 'SELECT')",
        ));
        $this->assertTrue((bool) $runtime->scalar(
            "SELECT has_table_privilege('app_runtime', 'public.tenant_access_scopes', 'SELECT')",
        ));

        $control = DB::connection('pgsql_control');
        foreach ([
            'public.set_tenant_member_scope_mode(uuid,text,text)',
            'public.grant_tenant_access_scope(uuid,text,uuid,text,text,date,date,text)',
            'public.revoke_tenant_access_scope(uuid,text)',
        ] as $function) {
            $this->assertFalse(
                (bool) $runtime->scalar(
                    "SELECT has_function_privilege('app_runtime', ?, 'EXECUTE')",
                    [$function],
                ),
                "app_runtime must not execute privileged scope API {$function}.",
            );
            $this->assertTrue(
                (bool) $control->scalar(
                    "SELECT has_function_privilege('app_control', ?, 'EXECUTE')",
                    [$function],
                ),
                "app_control must execute the intended scope API {$function}.",
            );
        }

        foreach (['SELECT', 'INSERT', 'UPDATE', 'DELETE'] as $privilege) {
            $this->assertFalse((bool) $control->scalar(
                "SELECT has_table_privilege('app_control', 'public.tenant_access_scope_modes', ?)",
                [$privilege],
            ));
            $this->assertFalse((bool) $control->scalar(
                "SELECT has_table_privilege('app_control', 'public.tenant_access_scopes', ?)",
                [$privilege],
            ));
        }
    }

    public function test_tenant_owner_can_change_member_scope_mode_and_the_database_writes_audit_atomically(): void
    {
        $this->actAs($this->ownerId);

        $this->putJson('/api/v1/enterprise/members/'.$this->memberId.'/access-scope-mode', [
            'scope_mode' => 'SCOPED',
            'reason' => 'Restrict this member to approved operating scopes.',
        ])->assertOk()
            ->assertJsonPath('data.user_id', $this->memberId)
            ->assertJsonPath('data.scope_mode', 'SCOPED');

        $this->assertDatabaseHas('tenant_access_scope_modes', [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->memberId,
            'scope_mode' => 'SCOPED',
            'changed_by' => $this->ownerId,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'actor_user_id' => $this->ownerId,
            'action' => 'TENANT_MEMBER_SCOPE_MODE_CHANGED',
            'object_id' => $this->memberId,
            'reason' => 'Restrict this member to approved operating scopes.',
        ], 'pgsql_test_admin');
    }

    public function test_tenant_administrator_can_grant_and_revoke_a_market_scope_with_audit_events(): void
    {
        $this->actAs($this->adminId);

        $grant = $this->postJson('/api/v1/enterprise/members/'.$this->memberId.'/access-scopes', [
            'scope_type' => 'MARKET',
            'scope_code' => 'addis-ababa',
            'permission_code' => 'READ',
            'valid_from' => now()->toDateString(),
            'grant_reason' => 'Limit this member to Addis market reporting.',
        ])->assertCreated()
            ->assertJsonPath('data.user_id', $this->memberId)
            ->assertJsonPath('data.scope_type', 'MARKET')
            ->assertJsonPath('data.scope_code', 'ADDIS-ABABA')
            ->assertJsonPath('data.status', 'ACTIVE');

        $grantId = $grant->json('data.id');
        $this->assertIsString($grantId);

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'actor_user_id' => $this->adminId,
            'action' => 'TENANT_ACCESS_SCOPE_GRANTED',
            'object_id' => $grantId,
            'reason' => 'Limit this member to Addis market reporting.',
        ], 'pgsql_test_admin');

        $this->postJson('/api/v1/enterprise/members/'.$this->memberId.'/access-scopes', [
            'scope_type' => 'MARKET',
            'scope_code' => 'ADDIS-ABABA',
            'permission_code' => 'READ',
            'valid_from' => now()->toDateString(),
            'grant_reason' => 'Attempt to duplicate the active Addis market scope.',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/enterprise/members/'.$this->memberId.'/access-scopes/'.$grantId.'/revoke', [
            'reason' => 'Remove the member market access after reassignment.',
        ])->assertOk()
            ->assertJsonPath('data.id', $grantId)
            ->assertJsonPath('data.status', 'REVOKED')
            ->assertJsonPath('data.revoked_by', $this->adminId);

        $this->assertDatabaseHas('tenant_access_scopes', [
            'tenant_id' => $this->tenantId,
            'id' => $grantId,
            'status' => 'REVOKED',
            'revoked_by' => $this->adminId,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'actor_user_id' => $this->adminId,
            'action' => 'TENANT_ACCESS_SCOPE_REVOKED',
            'object_id' => $grantId,
            'reason' => 'Remove the member market access after reassignment.',
        ], 'pgsql_test_admin');
    }

    public function test_scope_security_migration_cannot_be_rolled_back_to_restore_direct_runtime_writes(): void
    {
        $migration = require database_path('migrations/2026_11_27_000034_secure_tenant_access_scope_mutations.php');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Security migration 000034 is forward-only');

        $migration->down();
    }

    public function test_runtime_cannot_spoof_an_admin_guc_and_execute_the_scope_mutation_function(): void
    {
        $runtime = DB::connection('pgsql');
        $runtime->beginTransaction();

        try {
            $runtime->select(
                "SELECT set_config('app.current_tenant_id', ?, true)",
                [$this->tenantId],
            );
            $runtime->select(
                "SELECT set_config('app.current_user_id', ?, true)",
                [$this->ownerId],
            );

            try {
                $runtime->select(
                    'SELECT * FROM public.set_tenant_member_scope_mode(?::uuid, ?, ?)',
                    [
                        $this->adminId,
                        'TENANT_WIDE',
                        'Attempt to bypass the tenant administrator scope control.',
                    ],
                );
                $this->fail('The ordinary app_runtime role must not invoke the privileged mutation API, even with a forged administrator GUC.');
            } catch (QueryException $exception) {
                $this->assertSame('42501', $exception->errorInfo[0] ?? null);
            }
        } finally {
            if ($runtime->transactionLevel() > 0) {
                $runtime->rollBack();
            }
        }

        $this->assertDatabaseMissing('tenant_access_scope_modes', [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->adminId,
        ], 'pgsql_test_admin');
    }
}
