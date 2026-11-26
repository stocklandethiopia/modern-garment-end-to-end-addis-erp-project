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

    private string $tenantBOwnerId;

    private string $memberId;

    private string $legalEntityA;

    private string $legalEntityB;

    private string $siteA;

    private string $warehouseNodeA;

    private string $warehouseNodeB;

    private string $branchNodeA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = (string) Str::uuid();
        $this->tenantB = (string) Str::uuid();
        $this->userId = (string) Str::uuid();
        $this->tenantBOwnerId = (string) Str::uuid();
        $this->memberId = (string) Str::uuid();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        // Tenant root + initial owner are committed atomically. Both ACTIVE
        // tenants are fully provisioned; the current test user is only a
        // member of tenant A, while tenant B belongs to a different identity.
        $admin->transaction(function () use ($admin, $now): void {
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
                [
                    'id' => $this->userId,
                    'name' => 'Tenant A Test User',
                    'email' => 'tenant-a-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('a-long-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => $this->tenantBOwnerId,
                    'name' => 'Tenant B Owner',
                    'email' => 'tenant-b-owner-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('another-long-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => $this->memberId,
                    'name' => 'Tenant A Ordinary Member',
                    'email' => 'tenant-a-member-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('member-long-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $admin->table('tenant_memberships')->insert([
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $this->tenantA,
                    'user_id' => $this->userId,
                    'membership_role' => 'OWNER',
                    'status' => 'ACTIVE',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $this->tenantB,
                    'user_id' => $this->tenantBOwnerId,
                    'membership_role' => 'OWNER',
                    'status' => 'ACTIVE',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $this->tenantA,
                    'user_id' => $this->memberId,
                    'membership_role' => 'MEMBER',
                    'status' => 'ACTIVE',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        });

        foreach ([$this->tenantA => 'A Entity', $this->tenantB => 'B Entity'] as $tenantId => $name) {
            $entityId = (string) Str::uuid();
            if ($tenantId === $this->tenantA) {
                $this->legalEntityA = $entityId;
            } else {
                $this->legalEntityB = $entityId;
            }

            $admin->table('legal_entities')->insert([
                'id' => $entityId,
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

        $this->siteA = (string) Str::uuid();
        $this->warehouseNodeA = (string) Str::uuid();
        $this->warehouseNodeB = (string) Str::uuid();
        $this->branchNodeA = (string) Str::uuid();

        $admin->table('business_sites')->insert([
            'id' => $this->siteA,
            'tenant_id' => $this->tenantA,
            'legal_entity_id' => $this->legalEntityA,
            'code' => 'TEST-SITE-'.Str::upper(Str::random(6)),
            'site_type' => 'WAREHOUSE_CAMPUS',
            'name' => 'Test Site A',
            'country_code' => 'ET',
            'timezone' => 'Africa/Addis_Ababa',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('operational_nodes')->insert([
            [
                'id' => $this->warehouseNodeA,
                'tenant_id' => $this->tenantA,
                'business_site_id' => $this->siteA,
                'legal_entity_id' => $this->legalEntityA,
                'code' => 'TEST-WH-'.Str::upper(Str::random(6)),
                'node_type' => 'WAREHOUSE',
                'name' => 'Test Warehouse',
                'capabilities' => json_encode(['STOCK_HOLDING'], JSON_THROW_ON_ERROR),
                'stock_holding_enabled' => true,
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->branchNodeA,
                'tenant_id' => $this->tenantA,
                'business_site_id' => $this->siteA,
                'legal_entity_id' => $this->legalEntityA,
                'code' => 'TEST-BR-'.Str::upper(Str::random(6)),
                'node_type' => 'BRANCH',
                'name' => 'Test Branch',
                'capabilities' => json_encode([], JSON_THROW_ON_ERROR),
                'stock_holding_enabled' => false,
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->warehouseNodeB,
                'tenant_id' => $this->tenantA,
                'business_site_id' => $this->siteA,
                'legal_entity_id' => $this->legalEntityA,
                'code' => 'TEST-WH2-'.Str::upper(Str::random(6)),
                'node_type' => 'WAREHOUSE',
                'name' => 'Second Test Warehouse',
                'capabilities' => json_encode(['STOCK_HOLDING'], JSON_THROW_ON_ERROR),
                'stock_holding_enabled' => true,
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

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
        // Use a real ordinary member; never demote the tenant's only OWNER.
        Sanctum::actingAs(User::query()->findOrFail($this->memberId), ['tenant:select', 'erp:access']);

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
            'tenants',
            'tenant_memberships',
            'legal_entities',
            'business_sites',
            'operational_nodes',
            'branch_registrations',
            'inventory_locations',
            'audit_events',
            'entity_licences',
        ];

        $rows = DB::connection('pgsql_test_admin')
            ->table('pg_class as c')
            ->join('pg_namespace as n', 'n.oid', '=', 'c.relnamespace')
            ->where('n.nspname', 'public')
            ->whereIn('c.relname', $tables)
            ->select(['c.relname', 'c.relrowsecurity', 'c.relforcerowsecurity'])
            ->get();

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
        $licencePrivileges = DB::connection('pgsql_test_admin')->selectOne(
            "SELECT
                has_table_privilege('app_runtime', 'entity_licences', 'SELECT') AS can_read_licences,
                has_table_privilege('app_runtime', 'entity_licences', 'INSERT') AS can_submit_licences,
                has_table_privilege('app_runtime', 'entity_licences', 'UPDATE') AS can_activate_licences",
        );
        $this->assertTrue($licencePrivileges->can_read_licences);
        $this->assertTrue($licencePrivileges->can_submit_licences);
        $this->assertFalse($licencePrivileges->can_activate_licences);
    }

    public function test_tenant_admin_can_create_a_draft_business_site_and_audit_event(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites', [
                'legal_entity_id' => $this->legalEntityA,
                'code' => 'FACTORY-ADDIS-01',
                'site_type' => 'FACTORY_CAMPUS',
                'name' => 'Addis Apparel Factory Campus',
                'country_code' => 'ET',
                'region' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'address_line' => 'Test industrial area',
                'timezone' => 'Africa/Addis_Ababa',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.legal_entity_id', $this->legalEntityA);

        $siteId = $response->json('data.id');
        $audit = DB::connection('pgsql_test_admin')->table('audit_events')
            ->where('tenant_id', $this->tenantA)
            ->where('object_type', 'BUSINESS_SITE')
            ->where('object_id', $siteId)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('BUSINESS_SITE_DRAFT_CREATED', $audit->action);
    }

    public function test_business_site_cannot_reference_another_tenants_legal_entity(): void
    {
        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites', [
                'legal_entity_id' => $this->legalEntityB,
                'code' => 'CROSS-TENANT-SITE',
                'site_type' => 'FACTORY_CAMPUS',
                'name' => 'Cross Tenant Site',
                'country_code' => 'ET',
                'timezone' => 'Africa/Addis_Ababa',
            ])
            ->assertUnprocessable();
    }

    public function test_branch_node_does_not_hold_stock_by_default(): void
    {
        $siteId = (string) Str::uuid();
        $now = now();
        DB::connection('pgsql_test_admin')->table('business_sites')->insert([
            'id' => $siteId,
            'tenant_id' => $this->tenantA,
            'legal_entity_id' => $this->legalEntityA,
            'code' => 'BRANCH-SITE-01',
            'site_type' => 'STORE_PREMISES',
            'name' => 'Retail Branch Premises',
            'country_code' => 'ET',
            'timezone' => 'Africa/Addis_Ababa',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites/'.$siteId.'/nodes', [
                'code' => 'BRANCH-01',
                'node_type' => 'BRANCH',
                'name' => 'Branch One',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.stock_holding_enabled', false)
            ->assertJsonPath('data.status', 'DRAFT');
    }

    public function test_branch_node_can_be_explicitly_configured_as_stock_holding(): void
    {
        $siteId = (string) Str::uuid();
        $now = now();
        DB::connection('pgsql_test_admin')->table('business_sites')->insert([
            'id' => $siteId,
            'tenant_id' => $this->tenantA,
            'legal_entity_id' => $this->legalEntityA,
            'code' => 'BRANCH-SITE-02',
            'site_type' => 'STORE_PREMISES',
            'name' => 'Branch Two Premises',
            'country_code' => 'ET',
            'timezone' => 'Africa/Addis_Ababa',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites/'.$siteId.'/nodes', [
                'code' => 'BRANCH-02',
                'node_type' => 'BRANCH',
                'name' => 'Branch Two',
                'stock_holding_enabled' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.stock_holding_enabled', true)
            ->assertJsonPath('data.capabilities.0', 'STOCK_HOLDING');
    }

    public function test_non_admin_member_cannot_create_business_site(): void
    {
        DB::connection('pgsql_test_admin')->table('tenant_memberships')
            ->where('tenant_id', $this->tenantA)
            ->where('user_id', $this->userId)
            ->update(['membership_role' => 'MEMBER']);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites', [
                'legal_entity_id' => $this->legalEntityA,
                'code' => 'MEMBER-SITE-01',
                'site_type' => 'OFFICE',
                'name' => 'Member Site',
                'country_code' => 'ET',
                'timezone' => 'Africa/Addis_Ababa',
            ])
            ->assertForbidden();
    }

    public function test_operating_node_requires_a_legal_entity_linked_site(): void
    {
        $siteId = (string) Str::uuid();
        $now = now();
        DB::connection('pgsql_test_admin')->table('business_sites')->insert([
            'id' => $siteId,
            'tenant_id' => $this->tenantA,
            'legal_entity_id' => null,
            'code' => 'UNASSIGNED-SITE',
            'site_type' => 'FACTORY_CAMPUS',
            'name' => 'Unassigned Site',
            'country_code' => 'ET',
            'timezone' => 'Africa/Addis_Ababa',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites/'.$siteId.'/nodes', [
                'code' => 'NO-ENTITY-NODE',
                'node_type' => 'FACTORY',
                'name' => 'No Entity Factory',
            ])
            ->assertUnprocessable();
    }

    public function test_branch_registration_evidence_is_saved_for_review_not_activated(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/sites/'.$this->siteA.'/branch-registrations', [
                'commercial_register_reference' => 'BR-REG-'.Str::upper(Str::random(8)),
                'activity_codes' => ['MANUFACTURING', 'WHOLESALE'],
                'issuing_authority' => 'Commercial Register Authority',
                'registered_at' => '2026-11-22',
                'valid_from' => '2026-11-22',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'REVIEW_REQUIRED')
            ->assertJsonPath('data.legal_entity_id', $this->legalEntityA)
            ->assertJsonPath('data.activity_codes.0', 'MANUFACTURING');

        $registrationId = $response->json('data.id');
        $audit = DB::connection('pgsql_test_admin')->table('audit_events')
            ->where('tenant_id', $this->tenantA)
            ->where('object_type', 'BRANCH_REGISTRATION')
            ->where('object_id', $registrationId)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('BRANCH_REGISTRATION_EVIDENCE_SUBMITTED', $audit->action);
    }

    public function test_activity_licence_evidence_is_saved_as_review_required(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/legal-entities/'.$this->legalEntityA.'/licences', [
                'site_id' => $this->siteA,
                'activity_code' => 'MANUFACTURING',
                'licence_number' => 'LIC-'.Str::upper(Str::random(8)),
                'issuing_authority' => 'Regional Trade Bureau',
                'issue_date' => '2026-11-01',
                'valid_from' => '2026-11-01',
                'valid_to' => '2027-10-31',
                'evidence_reference' => 'customer-vault://licence/one',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'REVIEW_REQUIRED')
            ->assertJsonPath('data.legal_entity_id', $this->legalEntityA)
            ->assertJsonPath('data.site_id', $this->siteA);

        $licenceId = $response->json('data.id');
        $audit = DB::connection('pgsql_test_admin')->table('audit_events')
            ->where('tenant_id', $this->tenantA)
            ->where('object_type', 'ENTITY_LICENCE')
            ->where('object_id', $licenceId)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('ENTITY_LICENCE_EVIDENCE_SUBMITTED', $audit->action);
    }

    public function test_activity_licence_cannot_link_a_site_owned_by_another_entity(): void
    {
        $otherEntityId = (string) Str::uuid();
        $otherSiteId = (string) Str::uuid();
        $now = now();

        DB::connection('pgsql_test_admin')->table('legal_entities')->insert([
            'id' => $otherEntityId,
            'tenant_id' => $this->tenantA,
            'registration_number' => 'SECOND-REG-'.Str::upper(Str::random(6)),
            'legal_name' => 'Second Legal Entity A',
            'country_code' => 'ET',
            'base_currency' => 'ETB',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::connection('pgsql_test_admin')->table('business_sites')->insert([
            'id' => $otherSiteId,
            'tenant_id' => $this->tenantA,
            'legal_entity_id' => $otherEntityId,
            'code' => 'OTHER-ENTITY-SITE',
            'site_type' => 'FACTORY_CAMPUS',
            'name' => 'Other Entity Site',
            'country_code' => 'ET',
            'timezone' => 'Africa/Addis_Ababa',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/legal-entities/'.$this->legalEntityA.'/licences', [
                'site_id' => $otherSiteId,
                'activity_code' => 'RETAIL',
                'licence_number' => 'LIC-CROSS-ENTITY',
                'issuing_authority' => 'Regional Trade Bureau',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('site_id');
    }

    public function test_user_cannot_submit_licence_evidence_without_tenant_admin_role(): void
    {
        DB::connection('pgsql_test_admin')->table('tenant_memberships')
            ->where('tenant_id', $this->tenantA)
            ->where('user_id', $this->userId)
            ->update(['membership_role' => 'MEMBER']);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/legal-entities/'.$this->legalEntityA.'/licences', [
                'activity_code' => 'RETAIL',
                'licence_number' => 'LIC-NO-ADMIN',
                'issuing_authority' => 'Regional Trade Bureau',
            ])
            ->assertForbidden();
    }

    public function test_stock_capable_node_can_create_an_inventory_location(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/nodes/'.$this->warehouseNodeA.'/inventory-locations', [
                'code' => 'A-01-BIN-01',
                'location_type' => 'BIN',
                'allow_stock' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.allow_stock', true)
            ->assertJsonPath('data.operational_node_id', $this->warehouseNodeA);

        $locationId = $response->json('data.id');
        $audit = DB::connection('pgsql_test_admin')->table('audit_events')
            ->where('tenant_id', $this->tenantA)
            ->where('object_type', 'INVENTORY_LOCATION')
            ->where('object_id', $locationId)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('INVENTORY_LOCATION_DRAFT_CREATED', $audit->action);
    }

    public function test_branch_cannot_create_inventory_locations_without_stock_capability(): void
    {
        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/nodes/'.$this->branchNodeA.'/inventory-locations', [
                'code' => 'BRANCH-BIN-01',
                'location_type' => 'BIN',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('operational_node');
    }

    public function test_inventory_location_parent_must_belong_to_same_node(): void
    {
        $parent = (string) Str::uuid();
        $now = now();

        DB::connection('pgsql_test_admin')->table('inventory_locations')->insert([
            'id' => $parent,
            'tenant_id' => $this->tenantA,
            'operational_node_id' => $this->warehouseNodeA,
            'code' => 'ZONE-01',
            'location_type' => 'ZONE',
            'allow_stock' => false,
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withHeader('X-Tenant-Id', $this->tenantA)
            ->postJson('/api/v1/enterprise/nodes/'.$this->warehouseNodeB.'/inventory-locations', [
                'code' => 'ZONE-CHILD-01',
                'location_type' => 'BIN',
                'parent_id' => $parent,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }
}
