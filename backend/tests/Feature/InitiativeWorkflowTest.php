<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InitiativeWorkflowTest extends TestCase
{
    private string $tenantId;

    private string $otherTenantId;

    private string $userId;

    private string $approverId;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();
        $this->tenantId = (string) Str::uuid();
        $this->otherTenantId = (string) Str::uuid();
        $this->userId = (string) Str::uuid();
        $this->approverId = (string) Str::uuid();

        foreach ([$this->tenantId => 'MERCH-TEST-1', $this->otherTenantId => 'MERCH-TEST-2'] as $tenantId => $code) {
            $admin->table('tenants')->insert([
                'id' => $tenantId,
                'code' => $code.'-'.Str::upper(Str::random(6)),
                'name' => $code,
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $admin->table('users')->insert([
            'id' => $this->userId,
            'name' => 'Merchandising Test Owner',
            'email' => 'merch-'.Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('a-long-test-password'),
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('users')->insert([
            'id' => $this->approverId,
            'name' => 'Independent Merchandising Approver',
            'email' => 'approver-'.Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('a-long-test-password'),
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('tenant_memberships')->insert([
            [
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenantId,
                'user_id' => $this->userId,
                'membership_role' => 'OWNER',
                'status' => 'ACTIVE',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenantId,
                'user_id' => $this->approverId,
                'membership_role' => 'ADMIN',
                'status' => 'ACTIVE',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Sanctum::actingAs(User::query()->findOrFail($this->userId), ['tenant:select', 'erp:access']);
    }

    public function test_initiative_moves_through_screening_and_requires_sourced_insight_before_assessment(): void
    {
        $create = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives', [
                'code' => 'SS27-URBAN-01',
                'initiative_type' => 'NEW_COLLECTION',
                'title' => 'Urban everyday collection',
                'problem_statement' => 'Customers report a gap in durable everyday apparel at the intended price.',
                'objective' => 'Test a new seasonal range for the target consumer and channel.',
                'target_market' => 'Ethiopia / Addis Ababa',
                'target_consumer' => 'Urban adults',
                'expected_benefit' => 'Validated commercial proposition and repeatable range.',
            ]);

        $create->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.current_version', 1)
            ->assertJsonPath('version', 1);

        $initiativeId = $create->json('data.id');

        $version = DB::connection('pgsql_test_admin')->table('initiative_versions')
            ->where('tenant_id', $this->tenantId)
            ->where('initiative_id', $initiativeId)
            ->first();

        $this->assertNotNull($version);
        $this->assertSame(1, (int) $version->version_no);

        $audit = DB::connection('pgsql_test_admin')->table('audit_events')
            ->where('tenant_id', $this->tenantId)
            ->where('object_type', 'INITIATIVE')
            ->where('object_id', $initiativeId)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('INITIATIVE_DRAFT_CREATED', $audit->action);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/submit', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'SCREENING');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/decisions', [
                'decision' => 'APPROVE_RESEARCH',
                'reason' => 'The creator cannot approve their own screening decision.',
            ])
            ->assertForbidden();

        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/decisions', [
                'decision' => 'APPROVE_RESEARCH',
                'reason' => 'The opportunity is sufficiently clear to authorize focused research.',
                'actions' => ['Collect store observations', 'Review wholesale buyer requests'],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'RESEARCH');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/advance-to-assessment', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'At least one sourced market insight is required before assessment.');

        $sourceResponse = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/insight-sources', [
                'source_type' => 'STORE_OBSERVATION',
                'source_name' => 'Addis store visit notes',
                'source_reference' => 'internal-research://store-visits/2026-11-22',
                'capture_method' => 'STRUCTURED_STORE_OBSERVATION',
                'geography' => 'Addis Ababa',
                'reliability_score' => 0.85,
                'permitted_use_basis' => 'Internal product and merchandising research',
                'limitations' => 'Small sample of locations; not representative of all regions.',
            ]);

        $sourceResponse->assertCreated();
        $sourceId = $sourceResponse->json('data.id');

        $insightResponse = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/market-insights', [
                'source_id' => $sourceId,
                'initiative_id' => $initiativeId,
                'insight_type' => 'OBSERVED_FACT',
                'title' => 'Observed gap in durable everyday garments',
                'summary' => 'Store staff and customer feedback indicate repeated requests for durable everyday garments at the target price.',
                'observed_at' => '2026-11-22T08:00:00+03:00',
                'geography' => 'Addis Ababa',
                'target_segment' => 'Urban adults',
                'channel_code' => 'OWN_RETAIL',
                'category_code' => 'CASUALWEAR',
                'metric_name' => 'Recorded requests',
                'metric_value_numeric' => 18,
                'metric_unit' => 'requests',
                'sample_size' => 18,
                'denominator' => 40,
                'confidence' => 0.72,
                'limitations' => 'Qualitative observation; the sample is not a market-size estimate.',
                'privacy_basis' => 'Aggregated internal research; no personal consumer identifiers collected.',
            ]);

        $insightResponse->assertCreated()
            ->assertJsonPath('data.initiative_id', $initiativeId)
            ->assertJsonPath('data.source_id', $sourceId);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/advance-to-assessment', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'ASSESSMENT');

        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'Evidence supports advancing the opportunity to collection and range planning.',
                'actions' => ['Prepare the commercial brief'],
                'review_date' => '2026-12-15',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED');

        $detail = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/product/initiatives/'.$initiativeId);

        $detail->assertOk()
            ->assertJsonCount(1, 'versions')
            ->assertJsonCount(2, 'decisions')
            ->assertJsonCount(1, 'insights')
            ->assertJsonFragment(['decision' => 'APPROVE_RESEARCH'])
            ->assertJsonFragment(['decision' => 'APPROVE'])
            ->assertJsonPath('insights.0.source_name', 'Addis store visit notes');

        $decisionCount = DB::connection('pgsql_test_admin')->table('initiative_decisions')
            ->where('tenant_id', $this->tenantId)
            ->where('initiative_id', $initiativeId)
            ->count();

        $this->assertSame(2, $decisionCount);
    }

    public function test_initiative_and_market_evidence_are_isolated_by_tenant(): void
    {
        $sourceId = (string) Str::uuid();
        $otherInitiativeId = (string) Str::uuid();
        $now = now();

        DB::connection('pgsql_test_admin')->table('insight_sources')->insert([
            'id' => $sourceId,
            'tenant_id' => $this->otherTenantId,
            'source_type' => 'EXTERNAL_BENCHMARK',
            'source_name' => 'Other tenant benchmark',
            'capture_method' => 'RESEARCH_REVIEW',
            'permitted_use_basis' => 'Other tenant internal research',
            'created_by' => $this->userId,
            'created_at' => $now,
        ]);

        DB::connection('pgsql_test_admin')->table('initiatives')->insert([
            'id' => $otherInitiativeId,
            'tenant_id' => $this->otherTenantId,
            'owner_user_id' => $this->userId,
            'code' => 'OTHER-TENANT-01',
            'initiative_type' => 'NEW_COLLECTION',
            'title' => 'Other tenant initiative',
            'problem_statement' => 'An independent tenant opportunity.',
            'objective' => 'Validate tenant-scoped data boundaries.',
            'target_market' => 'Other market',
            'status' => 'DRAFT',
            'current_version' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::connection('pgsql_test_admin')->table('initiative_versions')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenantId,
            'initiative_id' => $otherInitiativeId,
            'version_no' => 1,
            'snapshot' => json_encode(['title' => 'Other tenant initiative'], JSON_THROW_ON_ERROR),
            'change_reason' => 'Initial proposal',
            'created_by' => $this->userId,
            'created_at' => $now,
        ]);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/product/initiatives/'.$otherInitiativeId)
            ->assertNotFound();

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/market-insights', [
                'source_id' => $sourceId,
                'initiative_id' => null,
                'insight_type' => 'EXTERNAL_BENCHMARK',
                'title' => 'Cross-tenant source attempt',
                'summary' => 'The source reference belongs to a different tenant and must be rejected.',
                'observed_at' => '2026-11-22T08:00:00+03:00',
                'privacy_basis' => 'Test-only validation',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source_id');
    }

    public function test_non_admin_member_cannot_record_initiative_decision(): void
    {
        $create = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives', [
                'code' => 'MEMBER-OP-01',
                'initiative_type' => 'CATEGORY_EXPANSION',
                'title' => 'Member proposal',
                'problem_statement' => 'A member may propose an opportunity but cannot approve it.',
                'objective' => 'Demonstrate decision segregation.',
                'target_market' => 'Ethiopia',
            ])
            ->assertCreated();

        $initiativeId = $create->json('data.id');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/submit', [])
            ->assertOk();

        DB::connection('pgsql_test_admin')->table('tenant_memberships')
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->update(['membership_role' => 'MEMBER']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/decisions', [
                'decision' => 'APPROVE_RESEARCH',
                'reason' => 'This user is not authorized to approve the screening gate.',
            ])
            ->assertForbidden();
    }

    public function test_initiative_tables_force_rls_and_history_is_immutable(): void
    {
        $tables = [
            'initiatives',
            'initiative_versions',
            'initiative_decisions',
            'insight_sources',
            'market_insights',
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
            $this->assertTrue($row->relrowsecurity);
            $this->assertTrue($row->relforcerowsecurity);
        }

        $versionPrivileges = DB::connection('pgsql_test_admin')->selectOne(
            "SELECT
                has_table_privilege('app_runtime', 'initiative_versions', 'INSERT') AS can_insert_version,
                has_table_privilege('app_runtime', 'initiative_versions', 'UPDATE') AS can_update_version,
                has_table_privilege('app_runtime', 'market_insights', 'INSERT') AS can_insert_insight,
                has_table_privilege('app_runtime', 'market_insights', 'UPDATE') AS can_update_insight",
        );

        $this->assertTrue($versionPrivileges->can_insert_version);
        $this->assertFalse($versionPrivileges->can_update_version);
        $this->assertTrue($versionPrivileges->can_insert_insight);
        $this->assertFalse($versionPrivileges->can_update_insight);
    }

    public function test_insight_source_provenance_is_append_only(): void
    {
        $response = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/insight-sources', [
                'source_type' => 'SURVEY',
                'source_name' => 'Customer survey wave one',
                'source_reference' => 'internal-research://survey/wave-one',
                'capture_method' => 'CONTROLLED_SURVEY',
                'permitted_use_basis' => 'Internal aggregate merchandising research',
            ])
            ->assertCreated();

        $sourceId = $response->json('data.id');

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::connection('pgsql_test_admin')->table('insight_sources')
            ->where('tenant_id', $this->tenantId)
            ->where('id', $sourceId)
            ->update(['source_name' => 'Silently overwritten source']);
    }

    public function test_database_rejects_market_insight_confidence_outside_zero_to_one(): void
    {
        $admin = DB::connection('pgsql_test_admin');
        $now = now();
        $sourceId = (string) Str::uuid();

        $admin->table('insight_sources')->insert([
            'id' => $sourceId,
            'tenant_id' => $this->tenantId,
            'source_type' => 'EXTERNAL_BENCHMARK',
            'source_name' => 'Test benchmark source',
            'capture_method' => 'SOURCE_REVIEW',
            'permitted_use_basis' => 'Test-only market research',
            'created_by' => $this->userId,
            'created_at' => $now,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $admin->table('market_insights')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'source_id' => $sourceId,
            'insight_type' => 'EXTERNAL_BENCHMARK',
            'title' => 'Invalid confidence boundary',
            'summary' => 'This row must be rejected by the database constraint.',
            'observed_at' => $now,
            'captured_at' => $now,
            'confidence' => 1.2,
            'privacy_basis' => 'Test-only validation',
            'analyst_user_id' => $this->userId,
            'created_at' => $now,
        ]);
    }

}
