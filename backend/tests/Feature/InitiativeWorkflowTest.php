<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
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

    public function test_approved_evidence_policy_requires_distinct_current_sources_before_assessment(): void
    {
        $initiativeResponse = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives', [
                'code' => 'EVIDENCE-POLICY-01',
                'initiative_type' => 'NEW_COLLECTION',
                'title' => 'Evidence-gated collection proposal',
                'problem_statement' => 'A new collection requires current and independently sourced market evidence.',
                'objective' => 'Verify tenant-approved evidence requirements before assessment.',
                'target_market' => 'Ethiopia',
            ])
            ->assertCreated();

        $initiativeId = $initiativeResponse->json('data.id');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/submit', [])
            ->assertOk();

        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/decisions', [
                'decision' => 'APPROVE_RESEARCH',
                'reason' => 'The proposal may proceed to structured research.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'RESEARCH');

        Sanctum::actingAs(User::query()->findOrFail($this->userId), ['tenant:select', 'erp:access']);

        $policyResponse = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies', [
                'code' => 'NEW_COLLECTION_ASSESSMENT',
                'name' => 'New collection assessment evidence',
                'initiative_type' => 'NEW_COLLECTION',
                'stage' => 'ASSESSMENT',
                'target_market' => 'Ethiopia',
                'effective_from' => now()->toDateString(),
                'change_reason' => 'Initial tenant evidence threshold for a new collection assessment.',
                'rules' => [[
                    'requirement_code' => 'CURRENT_STORE_OBSERVATIONS',
                    'insight_type' => 'OBSERVED_FACT',
                    'minimum_records' => 2,
                    'minimum_distinct_sources' => 2,
                    'minimum_confidence' => 0.70,
                    'minimum_source_reliability' => 0.80,
                    'max_age_days' => 30,
                    'require_geography' => true,
                    'require_target_segment' => true,
                    'require_sample_denominator' => true,
                    'is_mandatory' => true,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.policy.status', 'DRAFT')
            ->assertJsonPath('data.policy.version_no', 1)
            ->assertJsonPath('data.rules.0.minimum_records', 2);

        $policyId = $policyResponse->json('data.policy.id');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies/'.$policyId.'/submit', [])
            ->assertOk()
            ->assertJsonPath('data.policy.status', 'PENDING_APPROVAL');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/product/merchandising-dashboard')
            ->assertOk()
            ->assertJsonMissing([
                'type' => 'EVIDENCE_POLICY_APPROVAL',
                'id' => $policyId,
            ]);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies/'.$policyId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The policy author cannot approve their own evidence thresholds.',
            ])
            ->assertForbidden();

        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/product/merchandising-dashboard')
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'EVIDENCE_POLICY_APPROVAL',
                'id' => $policyId,
            ]);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies/'.$policyId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The evidence thresholds are approved for the stated initiative type, stage and market.',
            ])
            ->assertOk()
            ->assertJsonPath('data.policy.status', 'APPROVED')
            ->assertJsonPath('data.policy.approved_by', $this->approverId);

        $coverage = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->getJson('/api/v1/product/initiatives/'.$initiativeId.'/evidence-coverage?stage=ASSESSMENT')
            ->assertOk()
            ->assertJsonPath('data.evidence_coverage.status', 'GAP')
            ->assertJsonPath('data.evidence_coverage.coverage_percent', 0)
            ->assertJsonPath('data.evidence_coverage.rules.0.gaps.0', 'NO_MATCHING_INSIGHTS');

        $this->assertSame($initiativeId, $coverage->json('data.initiative_id'));

        try {
            DB::connection('pgsql_test_admin')
                ->table('initiative_evidence_policy_rules')
                ->where('tenant_id', $this->tenantId)
                ->where('policy_id', $policyId)
                ->update(['minimum_records' => 1]);
            $this->fail('Approved evidence rules must be immutable.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        Sanctum::actingAs(User::query()->findOrFail($this->userId), ['tenant:select', 'erp:access']);

        foreach ([1, 2] as $index) {
            $source = $this->withHeader('X-Tenant-Id', $this->tenantId)
                ->postJson('/api/v1/product/insight-sources', [
                    'source_type' => 'STORE_OBSERVATION',
                    'source_name' => 'Approved evidence source '.$index,
                    'source_reference' => 'internal-research://evidence-policy/'.$index,
                    'capture_method' => 'STRUCTURED_STORE_OBSERVATION',
                    'geography' => 'Addis Ababa',
                    'reliability_score' => 0.90,
                    'permitted_use_basis' => 'Aggregated internal market research',
                ])
                ->assertCreated();

            $insight = $this->withHeader('X-Tenant-Id', $this->tenantId)
                ->postJson('/api/v1/product/market-insights', [
                    'source_id' => $source->json('data.id'),
                    'initiative_id' => $initiativeId,
                    'insight_type' => 'OBSERVED_FACT',
                    'title' => 'Current store observation '.$index,
                    'summary' => 'A structured, independently sourced observation for the policy gate.',
                    'observed_at' => now()->subDays(1)->toIso8601String(),
                    'geography' => 'Addis Ababa',
                    'target_segment' => 'Urban adults',
                    'channel_code' => 'OWN_RETAIL',
                    'category_code' => 'CASUALWEAR',
                    'sample_size' => 12,
                    'denominator' => 30,
                    'confidence' => 0.85,
                    'privacy_basis' => 'Aggregated research; no personal consumer identifiers are collected.',
                ]);

            $insight->assertCreated();

            if ($index === 1) {
                $this->withHeader('X-Tenant-Id', $this->tenantId)
                    ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/advance-to-assessment', [])
                    ->assertUnprocessable()
                    ->assertJsonPath('message', 'Configured assessment evidence requirements are not satisfied.');
            }
        }

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/advance-to-assessment', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'ASSESSMENT')
            ->assertJsonPath('evidence_coverage.status', 'MET')
            ->assertJsonPath('evidence_coverage.coverage_percent', 100)
            ->assertJsonPath('evidence_coverage.rules.0.qualifying_records', 2)
            ->assertJsonPath('evidence_coverage.rules.0.distinct_sources', 2);
    }

    public function test_approved_research_evidence_policy_gates_screening_approval(): void
    {
        $initiative = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives', [
                'code' => 'RESEARCH-GATE-01',
                'initiative_type' => 'NEW_COLLECTION',
                'title' => 'Research-gated collection proposal',
                'problem_statement' => 'The proposal requires qualifying research before resources are authorized.',
                'objective' => 'Verify the configured research evidence policy at screening approval.',
                'target_market' => ' Ethiopia ',
            ])
            ->assertCreated();

        $initiativeId = $initiative->json('data.id');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/initiatives/'.$initiativeId.'/submit', [])
            ->assertOk();

        $policy = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies', [
                'code' => ' new_collection_research ',
                'name' => 'New collection research evidence',
                'initiative_type' => 'NEW_COLLECTION',
                'stage' => 'RESEARCH',
                'target_market' => 'Ethiopia',
                'effective_from' => now()->toDateString(),
                'change_reason' => 'Initial research gate for proposed new collections.',
                'rules' => [[
                    'requirement_code' => ' current_survey_evidence ',
                    'insight_type' => 'SURVEY_RESULT',
                    'minimum_records' => 1,
                    'minimum_distinct_sources' => 1,
                    'minimum_confidence' => 0.70,
                    'minimum_source_reliability' => 0.80,
                    'max_age_days' => 30,
                    'is_mandatory' => true,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.policy.code', 'NEW_COLLECTION_RESEARCH')
            ->assertJsonPath('data.policy.target_market', 'Ethiopia')
            ->assertJsonPath('data.rules.0.requirement_code', 'CURRENT_SURVEY_EVIDENCE');

        $policyId = $policy->json('data.policy.id');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies/'.$policyId.'/submit', [])
            ->assertOk();

        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/evidence-policies/'.$policyId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The research evidence policy is approved for this market and initiative type.',
            ])
            ->assertOk()
            ->assertJsonPath('data.policy.status', 'APPROVED');

        $decisionUrl = '/api/v1/product/initiatives/'.$initiativeId.'/decisions';

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson($decisionUrl, [
                'decision' => 'APPROVE_RESEARCH',
                'reason' => 'Attempt research approval before its configured evidence is present.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Configured research evidence requirements are not satisfied.');

        Sanctum::actingAs(User::query()->findOrFail($this->userId), ['tenant:select', 'erp:access']);

        $source = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/insight-sources', [
                'source_type' => 'STORE_OBSERVATION',
                'source_name' => 'Independent survey source',
                'source_reference' => 'internal-research://survey/research-gate-01',
                'capture_method' => 'STRUCTURED_STORE_OBSERVATION',
                'geography' => 'Addis Ababa',
                'reliability_score' => 0.90,
                'permitted_use_basis' => 'Aggregated market research; no personal consumer identifiers are collected.',
            ])
            ->assertCreated();

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/market-insights', [
                'source_id' => $source->json('data.id'),
                'initiative_id' => $initiativeId,
                'insight_type' => 'SURVEY_RESULT',
                'title' => 'Qualifying research survey observation',
                'summary' => 'An independently sourced result collected for the research-stage evidence requirement.',
                'observed_at' => now()->subDays(2)->toIso8601String(),
                'geography' => 'Addis Ababa',
                'target_segment' => 'Urban adults',
                'sample_size' => 25,
                'denominator' => 40,
                'confidence' => 0.85,
                'privacy_basis' => 'Aggregated research; no personal consumer identifiers are collected.',
            ])
            ->assertCreated();

        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson($decisionUrl, [
                'decision' => 'APPROVE_RESEARCH',
                'reason' => 'The approved research evidence requirements are now satisfied.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'RESEARCH')
            ->assertJsonPath('evidence_coverage.status', 'MET')
            ->assertJsonPath('evidence_coverage.coverage_percent', 100);
    }

    public function test_market_insight_source_event_replay_is_deduplicated_and_conflicting_replay_is_rejected(): void
    {
        $source = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/insight-sources', [
                'source_type' => 'EXTERNAL_BENCHMARK',
                'source_name' => 'Idempotent source event benchmark',
                'source_reference' => 'internal-research://event-replay/benchmark-01',
                'capture_method' => 'CONTROLLED_IMPORT',
                'reliability_score' => 0.92,
                'permitted_use_basis' => 'Aggregated test research with no personal data.',
            ])
            ->assertCreated();

        $payload = [
            'source_id' => $source->json('data.id'),
            'insight_type' => 'EXTERNAL_BENCHMARK',
            'title' => 'Imported market observation',
            'summary' => 'One source event must produce one immutable market insight.',
            'observed_at' => now()->subDays(2)->toIso8601String(),
            'geography' => 'Addis Ababa',
            'target_segment' => 'Urban adults',
            'confidence' => 0.88,
            'privacy_basis' => 'Aggregated test evidence; no personal identifiers.',
            'source_system' => 'MARKET_RESEARCH_FEED',
            'source_event_id' => 'event-mer-014-0001',
        ];

        $first = $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/market-insights', $payload)
            ->assertCreated()
            ->assertJsonPath('deduplicated', false)
            ->assertJsonPath('duplicate_count', 0);

        $insightId = $first->json('data.id');

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/market-insights', $payload)
            ->assertOk()
            ->assertJsonPath('data.id', $insightId)
            ->assertJsonPath('deduplicated', true)
            ->assertJsonPath('duplicate_count', 1);

        $admin = DB::connection('pgsql_test_admin');
        $this->assertSame(
            1,
            $admin->table('market_insights')
                ->where('tenant_id', $this->tenantId)
                ->where('source_system', 'MARKET_RESEARCH_FEED')
                ->where('source_event_id', 'event-mer-014-0001')
                ->count(),
        );
        $this->assertSame(
            1,
            (int) $admin->table('market_insight_event_receipts')
                ->where('tenant_id', $this->tenantId)
                ->where('source_system', 'MARKET_RESEARCH_FEED')
                ->where('source_event_id', 'event-mer-014-0001')
                ->value('duplicate_count'),
        );

        $conflictingPayload = $payload;
        $conflictingPayload['summary'] = 'A changed payload cannot reuse an existing source event ID.';

        $this->withHeader('X-Tenant-Id', $this->tenantId)
            ->postJson('/api/v1/product/market-insights', $conflictingPayload)
            ->assertStatus(409);
    }

    public function test_initiative_tables_force_rls_and_history_is_immutable(): void
    {
        $tables = [
            'initiatives',
            'initiative_versions',
            'initiative_decisions',
            'insight_sources',
            'market_insights',
            'initiative_evidence_policies',
            'initiative_evidence_policy_rules',
            'tenant_access_scope_modes',
            'tenant_access_scopes',
            'market_insight_event_receipts',
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
                has_table_privilege('app_runtime', 'market_insights', 'UPDATE') AS can_update_insight,
                has_table_privilege('app_runtime', 'initiative_evidence_policies', 'INSERT') AS can_insert_evidence_policy,
                has_table_privilege('app_runtime', 'initiative_evidence_policies', 'DELETE') AS can_delete_evidence_policy,
                has_table_privilege('app_runtime', 'initiative_evidence_policy_rules', 'INSERT') AS can_insert_evidence_rule,
                has_table_privilege('app_runtime', 'initiative_evidence_policy_rules', 'DELETE') AS can_delete_evidence_rule,
                has_table_privilege('app_runtime', 'tenant_access_scope_modes', 'UPDATE') AS can_update_scope_mode,
                has_table_privilege('app_runtime', 'tenant_access_scopes', 'INSERT') AS can_insert_scope,
                has_column_privilege('app_runtime', 'tenant_access_scopes', 'status', 'UPDATE') AS can_revoke_scope,
                has_column_privilege('app_runtime', 'tenant_access_scopes', 'scope_id', 'UPDATE') AS can_change_scope_target,
                has_table_privilege('app_runtime', 'tenant_access_scopes', 'DELETE') AS can_delete_scope,
                has_table_privilege('app_runtime', 'market_insight_event_receipts', 'INSERT') AS can_insert_event_receipt,
                has_column_privilege('app_runtime', 'market_insight_event_receipts', 'duplicate_count', 'UPDATE') AS can_update_event_receipt,
                has_column_privilege('app_runtime', 'market_insight_event_receipts', 'payload_sha256', 'UPDATE') AS can_change_event_payload_hash,
                has_table_privilege('app_runtime', 'market_insight_event_receipts', 'DELETE') AS can_delete_event_receipt",
        );

        $this->assertTrue($versionPrivileges->can_insert_version);
        $this->assertFalse($versionPrivileges->can_update_version);
        $this->assertTrue($versionPrivileges->can_insert_insight);
        $this->assertFalse($versionPrivileges->can_update_insight);
        $this->assertTrue($versionPrivileges->can_insert_evidence_policy);
        $this->assertFalse($versionPrivileges->can_delete_evidence_policy);
        $this->assertTrue($versionPrivileges->can_insert_evidence_rule);
        $this->assertTrue($versionPrivileges->can_delete_evidence_rule);
        $this->assertTrue($versionPrivileges->can_update_scope_mode);
        $this->assertTrue($versionPrivileges->can_insert_scope);
        $this->assertTrue($versionPrivileges->can_revoke_scope);
        $this->assertFalse($versionPrivileges->can_change_scope_target);
        $this->assertFalse($versionPrivileges->can_delete_scope);
        $this->assertTrue($versionPrivileges->can_insert_event_receipt);
        $this->assertTrue($versionPrivileges->can_update_event_receipt);
        $this->assertFalse($versionPrivileges->can_change_event_payload_hash);
        $this->assertFalse($versionPrivileges->can_delete_event_receipt);
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

        $this->expectException(QueryException::class);
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

        $this->expectException(QueryException::class);
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
