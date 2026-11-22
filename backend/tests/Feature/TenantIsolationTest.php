<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    private string $tenantA;
    private string $tenantB;
    private string $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = (string) Str::uuid();
        $this->tenantB = (string) Str::uuid();
        $this->userId = (string) Str::uuid();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        foreach ([$this->tenantA, $this->tenantB] as $index => $tenantId) {
            $admin->table('tenants')->insert([
                'id' => $tenantId,
                'code' => 'TEST-'.($index + 1).'-'.Str::upper(Str::random(6)),
                'name' => 'Test tenant '.($index + 1),
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $admin->table('users')->insert([
            'id' => $this->userId,
            'name' => 'Tenant Test User',
            'email' => 'tenant-'.Str::lower(Str::random(8)).'@example.test',
            'password' => Hash::make('a-long-test-password'),
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('tenant_memberships')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA,
            'user_id' => $this->userId,
            'membership_role' => 'OWNER',
            'status' => 'ACTIVE',
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([$this->tenantA => 'A Entity', $this->tenantB => 'B Entity'] as $tenantId => $name) {
            $admin->table('legal_entities')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'registration_number' => 'REG-'.$tenantId,
                'legal_name' => $name,
                'country_code' => 'ET',
                'base_currency' => 'ETB',
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $user = User::query()->findOrFail($this->userId);
        Sanctum::actingAs($user, ['tenant:select', 'erp:access']);
    }

    public function test_membership_scopes_legal_entity_query_to_authorized_tenant(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->getJson('/api/v1/enterprise/legal-entities');

        $response->assertOk()
            ->assertJsonFragment(['legal_name' => 'A Entity'])
            ->assertJsonMissing(['legal_name' => 'B Entity']);
    }

    public function test_user_cannot_select_another_tenant_without_membership(): void
    {
        $this->withHeader('X-Tenant-Id', $this->tenantB)
            ->getJson('/api/v1/enterprise/legal-entities')
            ->assertForbidden();
    }

    public function test_missing_tenant_context_is_rejected(): void
    {
        $this->getJson('/api/v1/enterprise/legal-entities')->assertStatus(400);
    }

    public function test_runtime_database_role_sees_no_tenant_rows_without_context(): void
    {
        $runtime = DB::connection('pgsql');
        $this->assertSame(
            0,
            $runtime->table('legal_entities')->count(),
            'RLS must deny legal-entity reads when tenant context is absent.',
        );
        $this->assertSame(
            0,
            $runtime->table('tenants')->count(),
            'RLS must deny tenant-root reads when no selected tenant or membership context exists.',
        );
    }
    public function test_tenant_admin_can_create_a_draft_legal_entity_with_audit_evidence(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/legal-entities', [
                'registration_number' => 'TEST-REG-'.Str::upper(Str::random(8)),
                'legal_name' => 'Draft Garment Manufacturing PLC',
                'legal_form' => 'PLC',
                'country_code' => 'ET',
                'tax_identifier' => 'TEST-TIN-'.Str::upper(Str::random(6)),
                'base_currency' => 'ETB',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.country_code', 'ET')
            ->assertJsonPath('data.base_currency', 'ETB');

        $entityId = $response->json('data.id');
        $audit = DB::connection('pgsql_test_admin')->table('audit_events')
            ->where('tenant_id', $this->tenantA)
            ->where('object_type', 'LEGAL_ENTITY')
            ->where('object_id', $entityId)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('LEGAL_ENTITY_DRAFT_CREATED', $audit->action);
    }

    public function test_member_without_tenant_admin_role_cannot_create_legal_entity(): void
    {
        DB::connection('pgsql_test_admin')->table('tenant_memberships')
            ->where('tenant_id', $this->tenantA)
            ->where('user_id', $this->userId)
            ->update(['membership_role' => 'MEMBER']);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/legal-entities', [
                'registration_number' => 'TEST-REG-'.Str::upper(Str::random(8)),
                'legal_name' => 'Unauthorized Entity',
                'country_code' => 'ET',
                'base_currency' => 'ETB',
            ])
            ->assertForbidden();
    }

    public function test_tenant_selector_returns_only_active_memberships_for_current_user(): void
    {
        $this->getJson('/api/v1/tenants')
            ->assertOk()
            ->assertJsonFragment(['id' => $this->tenantA])
            ->assertJsonMissing(['id' => $this->tenantB]);
    }

    public function test_tenant_tables_force_row_level_security(): void
    {
        $tables = [
            'tenant_memberships',
            'legal_entities',
            'business_sites',
            'operational_nodes',
            'branch_registrations',
            'inventory_locations',
            'audit_events',
        ];

        $rows = DB::connection('pgsql_test_admin')->select(
            'SELECT c.relname, c.relrowsecurity, c.relforcerowsecurity
             FROM pg_class c
             JOIN pg_namespace n ON n.oid = c.relnamespace
             WHERE n.nspname = ? AND c.relname::text = ANY(string_to_array(?, ','))',
            ['public', implode(',', $tables)],
        );

        $this->assertCount(count($tables), $rows);

        foreach ($rows as $row) {
            $this->assertTrue($row->relrowsecurity, $row->relname.' must enable RLS.');
            $this->assertTrue($row->relforcerowsecurity, $row->relname.' must force RLS.');
        }
    }

    public function test_tenant_roots_have_membership_only_select_policy(): void
    {
        $policies = DB::connection('pgsql_test_admin')->select(
            "SELECT policyname FROM pg_policies
             WHERE schemaname = 'public' AND tablename = 'tenants'",
        );

        $this->assertEqualsCanonicalizing(
            ['tenant_isolation_tenants', 'tenant_self_membership_access'],
            array_map(fn ($row) => $row->policyname, $policies),
        );
    }

    public function test_runtime_role_has_only_explicit_minimum_table_privileges(): void
    {
        $privileges = DB::connection('pgsql_test_admin')->selectOne(
            "SELECT
                has_table_privilege('app_runtime', 'tenant_memberships', 'SELECT') AS can_read_memberships,
                has_table_privilege('app_runtime', 'tenant_memberships', 'INSERT') AS can_insert_memberships,
                has_table_privilege('app_runtime', 'legal_entities', 'INSERT') AS can_insert_entities,
                has_table_privilege('app_runtime', 'legal_entities', 'UPDATE') AS can_update_entities,
                has_table_privilege('app_runtime', 'audit_events', 'INSERT') AS can_append_audit,
                has_table_privilege('app_runtime', 'audit_events', 'UPDATE') AS can_update_audit,
                has_table_privilege('app_runtime', 'personal_access_tokens', 'DELETE') AS can_revoke_token,
                has_column_privilege('app_runtime', 'personal_access_tokens', 'last_used_at', 'UPDATE') AS can_update_token_last_used",
        );

        $this->assertTrue($privileges->can_read_memberships);
        $this->assertFalse($privileges->can_insert_memberships);
        $this->assertTrue($privileges->can_insert_entities);
        $this->assertFalse($privileges->can_update_entities);
        $this->assertTrue($privileges->can_append_audit);
        $this->assertFalse($privileges->can_update_audit);
        $this->assertTrue($privileges->can_revoke_token);
        $this->assertTrue($privileges->can_update_token_last_used);
    }

}
