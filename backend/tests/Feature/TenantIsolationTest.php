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
        Sanctum::actingAs($user, ['tenant:select']);
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
        $this->assertSame(
            0,
            DB::connection('pgsql')->table('legal_entities')->count(),
            'RLS must deny reads when transaction-local tenant context is absent.',
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

}
