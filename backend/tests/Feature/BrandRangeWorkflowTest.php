<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BrandRangeWorkflowTest extends TestCase
{
    private string $tenantId;

    private string $ownerId;

    private string $approverId;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        $this->tenantId = (string) Str::uuid();
        $this->ownerId = (string) Str::uuid();
        $this->approverId = (string) Str::uuid();

        $admin->transaction(function () use ($admin, $now): void {
            $admin->table('tenants')->insert([
                'id' => $this->tenantId,
                'code' => 'RANGE-'.Str::upper(Str::random(8)),
                'name' => 'Brand and range workflow tenant',
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $admin->table('users')->insert([
                [
                    'id' => $this->ownerId,
                    'name' => 'Range Planner',
                    'email' => 'range-owner-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('a-long-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => $this->approverId,
                    'name' => 'Commercial Approver',
                    'email' => 'range-approver-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('a-long-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $admin->table('tenant_memberships')->insert([
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $this->tenantId,
                    'user_id' => $this->ownerId,
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

        });
        $this->actAsOwner();
    }

    private function actAsOwner(): void
    {
        Sanctum::actingAs(User::query()->findOrFail($this->ownerId), ['tenant:select', 'erp:access']);
    }

    private function actAsApprover(): void
    {
        Sanctum::actingAs(User::query()->findOrFail($this->approverId), ['tenant:select', 'erp:access']);
    }

    private function tenantHeader(): array
    {
        return ['X-Tenant-Id' => $this->tenantId];
    }

    private function createApprovedBrand(): string
    {
        $brand = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/brands', [
            'code' => 'URBAN-'.Str::upper(Str::random(5)),
            'name' => 'Urban Essentials',
        ])->assertCreated();

        $brandId = $brand->json('data.id');

        $strategy = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions', [
            'positioning' => 'Durable everyday garments for urban consumers.',
            'target_consumer' => 'Urban adults seeking reliable everyday apparel.',
            'value_proposition' => 'Consistent fit, durable construction and accessible price architecture.',
            'quality_tier' => 'GOOD',
            'approved_categories' => ['CASUALWEAR', 'KNITWEAR'],
            'price_architecture' => ['GOOD' => ['currency' => 'ETB', 'target_margin' => 35]],
            'approved_channels' => ['OWN_RETAIL', 'WHOLESALE', 'D2C'],
            'effective_from' => '2027-01-01',
            'change_reason' => 'Initial strategy for the approved collection concept.',
        ])->assertCreated()->assertJsonPath('data.status', 'PENDING_APPROVAL');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions/'.$strategy->json('data.id').'/approve', [])
            ->assertOk();

        $this->actAsOwner();

        return $brandId;
    }

    private function createDraftRangePlanForAcceptance(): array
    {
        $brandId = $this->createApprovedBrand();

        $season = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/seasons', [
            'code' => 'ATMER-SS27-'.Str::upper(Str::random(5)),
            'name' => 'Acceptance Spring Summer 2027',
            'calendar_type' => 'FASHION',
            'timezone' => 'Africa/Addis_Ababa',
            'design_start' => '2026-09-01',
            'buy_start' => '2026-11-01',
            'production_start' => '2027-01-01',
            'production_end' => '2027-03-31',
            'delivery_start' => '2027-04-01',
            'delivery_end' => '2027-05-31',
            'selling_start' => '2027-06-01',
            'selling_end' => '2027-09-30',
        ])->assertCreated();

        $collection = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections', [
            'brand_id' => $brandId,
            'season_id' => $season->json('data.id'),
            'code' => 'ATMER-COL-'.Str::upper(Str::random(5)),
            'name' => 'Acceptance collection',
            'concept' => 'A controlled collection used for scoped authorization and legal eligibility tests.',
            'target_market' => 'Ethiopia',
        ])->assertCreated();

        $collectionId = $collection->json('data.id');
        $version = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections/'.$collectionId.'/versions', [
            'story' => 'A practical apparel collection for an explicit target customer and channel strategy.',
            'target_consumer' => 'Urban adults',
            'category_architecture' => ['CASUALWEAR'],
            'price_margin_envelope' => ['currency' => 'ETB', 'margin_floor' => 25],
            'budget_amount' => 150000,
            'budget_currency' => 'ETB',
            'delivery_start' => '2027-04-01',
            'delivery_end' => '2027-05-31',
            'change_reason' => 'Acceptance-test collection baseline.',
        ])->assertCreated();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/collections/'.$collectionId.'/versions/'.$version->json('data.id').'/approve', [])
            ->assertOk();
        $this->actAsOwner();

        $range = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans', [
            'collection_id' => $collectionId,
            'collection_version' => 1,
            'code' => 'ATMER-RANGE-'.Str::upper(Str::random(5)),
            'plan_start' => '2027-01-01',
            'plan_end' => '2027-03-31',
        ])->assertCreated();

        $line = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans/'.$range->json('data.id').'/lines', [
            'line_code' => 'ATMER-STYLE-01',
            'category_code' => 'CASUALWEAR',
            'target_tier' => 'GOOD',
            'product_hypothesis' => 'A durable everyday garment for an identifiable target consumer.',
            'planned_units' => 240,
            'target_retail_price' => 1800,
            'target_wholesale_price' => 1200,
            'price_currency' => 'ETB',
            'target_margin_percent' => 32,
            'channel_scope' => ['OWN_RETAIL', 'WHOLESALE'],
            'size_colour_intent' => ['sizes' => ['S', 'M', 'L'], 'colours' => ['NAVY']],
            'lifecycle_type' => 'NEW',
            'forecast_basis' => 'Initial research-led demand hypothesis; not a firm order.',
            'launch_date' => '2027-06-01',
        ])->assertCreated();

        return [
            'brand_id' => $brandId,
            'collection_id' => $collectionId,
            'range_plan_id' => $range->json('data.id'),
            'range_line_id' => $line->json('data.id'),
        ];
    }

    public function test_brand_strategy_is_approved_independently_and_version_content_remains_immutable(): void
    {
        $brand = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/brands', [
            'code' => 'BASIC-'.Str::upper(Str::random(5)),
            'name' => 'Core Basics',
        ])->assertCreated();

        $brandId = $brand->json('data.id');

        $strategy = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions', [
            'positioning' => 'Functional basics with consistent construction.',
            'target_consumer' => 'Consumers seeking everyday wardrobe essentials.',
            'value_proposition' => 'Reliable quality and fit with accessible prices.',
            'approved_categories' => ['BASICS'],
            'price_architecture' => ['currency' => 'ETB', 'target_margin' => 30],
            'approved_channels' => ['OWN_RETAIL'],
            'change_reason' => 'Create the first brand strategy revision.',
        ])->assertCreated();

        $versionId = $strategy->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions/'.$versionId.'/approve', [])
            ->assertForbidden();

        $this->actAsApprover();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions/'.$versionId.'/approve', [])
            ->assertOk();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions/'.$versionId.'/approve', [])
            ->assertUnprocessable();

        $this->actAsOwner();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/brands')
            ->assertOk()
            ->assertJsonPath('data.data.0.current_strategy_status', 'APPROVED')
            ->assertJsonPath('data.data.0.current_strategy_version', 1);

        $decision = DB::connection('pgsql_test_admin')->table('merchandising_decisions')
            ->where('tenant_id', $this->tenantId)
            ->where('object_type', 'BRAND_STRATEGY')
            ->where('object_id', $versionId)
            ->where('decision', 'APPROVE')
            ->first();

        $this->assertNotNull($decision);
        $this->assertSame($this->approverId, $decision->decided_by);

        // A new pending revision must not invalidate the previously approved strategy.
        $nextStrategy = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/brands/'.$brandId.'/strategy-versions', [
            'positioning' => 'A refined everyday essentials proposition.',
            'target_consumer' => 'Consumers seeking durable versatile wardrobe staples.',
            'value_proposition' => 'Improved fit consistency, durable quality and clear value.',
            'approved_categories' => ['BASICS', 'CASUALWEAR'],
            'price_architecture' => ['currency' => 'ETB', 'target_margin' => 32],
            'approved_channels' => ['OWN_RETAIL', 'WHOLESALE'],
            'change_reason' => 'Prepare next brand strategy revision for lifecycle regression test.',
        ])->assertCreated()->assertJsonPath('data.version_no', 2);

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/brands')
            ->assertOk()
            ->assertJsonPath('data.data.0.current_strategy_status', 'APPROVED')
            ->assertJsonPath('data.data.0.current_strategy_version', 1)
            ->assertJsonPath('data.data.0.latest_strategy_version_no', 2)
            ->assertJsonPath('data.data.0.latest_strategy_version_id', $nextStrategy->json('data.id'))
            ->assertJsonPath('data.data.0.latest_strategy_status', 'PENDING_APPROVAL');

        $privileges = DB::connection('pgsql_test_admin')->selectOne(
            "SELECT
                has_table_privilege('app_runtime', 'brand_strategy_versions', 'INSERT') AS can_insert,
                has_table_privilege('app_runtime', 'brand_strategy_versions', 'UPDATE') AS can_update,
                has_table_privilege('app_runtime', 'merchandising_decisions', 'UPDATE') AS can_update_decision",
        );

        $this->assertTrue($privileges->can_insert);
        $this->assertFalse($privileges->can_update);
        $this->assertFalse($privileges->can_update_decision);

        $this->expectException(QueryException::class);
        DB::connection('pgsql_test_admin')->table('brand_strategy_versions')
            ->where('tenant_id', $this->tenantId)
            ->where('id', $versionId)
            ->update(['positioning' => 'This overwrite must be rejected.']);
    }

    public function test_collection_approval_unlocks_range_plan_freeze_and_channel_assortment(): void
    {
        $brandId = $this->createApprovedBrand();

        $season = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/seasons', [
            'code' => 'SS27-'.Str::upper(Str::random(5)),
            'name' => 'Spring Summer 2027',
            'calendar_type' => 'FASHION',
            'timezone' => 'Africa/Addis_Ababa',
            'design_start' => '2026-09-01',
            'buy_start' => '2026-11-01',
            'production_start' => '2027-01-01',
            'production_end' => '2027-03-31',
            'delivery_start' => '2027-04-01',
            'delivery_end' => '2027-05-31',
            'selling_start' => '2027-06-01',
            'selling_end' => '2027-09-30',
        ])->assertCreated();

        $seasonId = $season->json('data.id');

        $collection = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections', [
            'brand_id' => $brandId,
            'season_id' => $seasonId,
            'code' => 'URBAN-SS27-'.Str::upper(Str::random(4)),
            'name' => 'Urban Spring Summer',
            'concept' => 'A focused collection of everyday apparel.',
            'target_market' => 'Ethiopia',
        ])->assertCreated()->assertJsonPath('data.current_version', 0);

        $collectionId = $collection->json('data.id');

        $version = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections/'.$collectionId.'/versions', [
            'story' => 'A versatile collection built around durable everyday apparel.',
            'target_consumer' => 'Urban adults',
            'category_architecture' => ['CASUALWEAR', 'KNITWEAR'],
            'price_margin_envelope' => ['currency' => 'ETB', 'margin_floor' => 25],
            'budget_amount' => 250000,
            'budget_currency' => 'ETB',
            'delivery_start' => '2027-04-01',
            'delivery_end' => '2027-05-31',
            'change_reason' => 'Initial collection architecture and commercial envelope.',
        ])->assertCreated();

        $versionId = $version->json('data.id');

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans', [
            'collection_id' => $collectionId,
            'collection_version' => 1,
            'code' => 'RANGE-BEFORE-APPROVAL',
            'plan_start' => '2027-01-01',
            'plan_end' => '2027-03-31',
        ])->assertUnprocessable();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/collections/'.$collectionId.'/versions/'.$versionId.'/approve', [])
            ->assertForbidden();

        $this->actAsApprover();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/collections/'.$collectionId.'/versions/'.$versionId.'/approve', [])
            ->assertOk()
            ->assertJsonPath('data.current_version', 1)
            ->assertJsonPath('data.status', 'APPROVED');

        $this->actAsOwner();

        $collectionVersion = DB::connection('pgsql_test_admin')->table('collection_versions')
            ->where('tenant_id', $this->tenantId)
            ->where('id', $versionId)
            ->first();

        $this->assertNotNull($collectionVersion);
        $this->assertSame('PENDING_APPROVAL', $collectionVersion->status);

        $range = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans', [
            'collection_id' => $collectionId,
            'collection_version' => 1,
            'code' => 'RANGE-SS27-'.Str::upper(Str::random(4)),
            'plan_start' => '2027-01-01',
            'plan_end' => '2027-03-31',
        ])->assertCreated();

        $rangePlanId = $range->json('data.id');

        $rangeLine = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/lines', [
            'line_code' => 'STYLE-001',
            'category_code' => 'CASUALWEAR',
            'target_tier' => 'GOOD',
            'product_hypothesis' => 'A durable everyday overshirt for urban customers.',
            'planned_units' => 600,
            'target_retail_price' => 1500,
            'target_wholesale_price' => 1000,
            'price_currency' => 'ETB',
            'target_margin_percent' => 35,
            'channel_scope' => ['OWN_RETAIL', 'WHOLESALE'],
            'size_colour_intent' => ['sizes' => ['S', 'M', 'L'], 'colours' => ['NAVY', 'STONE']],
            'lifecycle_type' => 'NEW',
            'forecast_basis' => 'Initial buyer/store research hypothesis; not a firm order.',
            'launch_date' => '2027-06-01',
        ])->assertCreated();

        $rangeLineId = $rangeLine->json('data.id');

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/freeze', [])
            ->assertForbidden();

        $this->actAsApprover();

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/freeze', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'FROZEN');

        $this->actAsOwner();

        $assortment = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/assortment-plans', [
            'range_plan_id' => $rangePlanId,
            'code' => 'OWN-RETAIL-SS27-'.Str::upper(Str::random(4)),
            'channel_code' => 'OWN_RETAIL',
            'market_scope' => 'Addis Ababa',
        ])->assertCreated();

        $assortmentId = $assortment->json('data.id');

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/assortment-plans/'.$assortmentId.'/lines', [
            'range_line_id' => $rangeLineId,
            'variant_size_curve' => ['S' => 120, 'M' => 220, 'L' => 180],
            'planned_units' => 520,
            'launch_date' => '2027-06-01',
            'minimum_depth' => 100,
            'maximum_depth' => 600,
        ])->assertCreated();

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/assortment-plans/'.$assortmentId.'/lines', [
            'range_line_id' => $rangeLineId,
            'variant_size_curve' => ['S' => 100],
            'planned_units' => 100,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('merchandising_decisions', [
            'tenant_id' => $this->tenantId,
            'object_type' => 'COLLECTION_VERSION',
            'object_id' => $versionId,
            'decision' => 'APPROVE',
            'decided_by' => $this->approverId,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('merchandising_decisions', [
            'tenant_id' => $this->tenantId,
            'object_type' => 'RANGE_PLAN',
            'object_id' => $rangePlanId,
            'decision' => 'FREEZE',
            'decided_by' => $this->approverId,
        ], 'pgsql_test_admin');
    }

    public function test_merchandising_master_records_and_history_are_tenant_scoped_and_protected(): void
    {
        $brandId = $this->createApprovedBrand();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/brands')
            ->assertOk()
            ->assertJsonFragment(['id' => $brandId]);

        $privileges = DB::connection('pgsql_test_admin')->selectOne(
            "SELECT
                has_table_privilege('app_runtime', 'brand_strategy_versions', 'UPDATE') AS can_update_brand_version,
                has_table_privilege('app_runtime', 'collection_versions', 'UPDATE') AS can_update_collection_version,
                has_table_privilege('app_runtime', 'merchandising_decisions', 'DELETE') AS can_delete_decision",
        );

        $this->assertFalse($privileges->can_update_brand_version);
        $this->assertFalse($privileges->can_update_collection_version);
        $this->assertFalse($privileges->can_delete_decision);
    }

    public function test_range_and_assortment_read_workflows_are_tenant_scoped_and_return_saved_rows(): void
    {
        $brandId = $this->createApprovedBrand();

        $season = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/seasons', [
            'code' => 'READ-SEASON-'.Str::upper(Str::random(5)),
            'name' => 'Read workflow season',
            'calendar_type' => 'FASHION',
            'timezone' => 'Africa/Addis_Ababa',
        ])->assertCreated();

        $collection = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections', [
            'brand_id' => $brandId,
            'season_id' => $season->json('data.id'),
            'code' => 'READ-COLLECTION-'.Str::upper(Str::random(4)),
            'name' => 'Read workflow collection',
            'target_market' => 'Ethiopia',
        ])->assertCreated();

        $collectionId = $collection->json('data.id');
        $version = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections/'.$collectionId.'/versions', [
            'story' => 'A collection used to verify persisted planning records.',
            'target_consumer' => 'Adults',
            'category_architecture' => ['CASUALWEAR'],
            'price_margin_envelope' => ['currency' => 'ETB'],
            'change_reason' => 'Create collection version for read workflow coverage.',
        ])->assertCreated();

        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/product/collections')
            ->assertOk()
            ->assertJsonPath('data.data.0.latest_version_status', 'PENDING_APPROVAL')
            ->assertJsonPath('data.data.0.latest_version_id', $version->json('data.id'));

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/collections/'.$collectionId.'/versions/'.$version->json('data.id').'/approve', [])
            ->assertOk();

        $this->actAsOwner();
        $range = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans', [
            'collection_id' => $collectionId,
            'collection_version' => 1,
            'code' => 'READ-RANGE-'.Str::upper(Str::random(4)),
            'plan_start' => '2027-01-01',
            'plan_end' => '2027-02-28',
        ])->assertCreated();

        $rangePlanId = $range->json('data.id');
        $line = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/lines', [
            'line_code' => 'READ-STYLE-001',
            'category_code' => 'CASUALWEAR',
            'product_hypothesis' => 'A durable everyday product for the selected consumer.',
            'planned_units' => 100,
            'channel_scope' => ['OWN_RETAIL'],
            'size_colour_intent' => ['sizes' => ['S', 'M']],
            'lifecycle_type' => 'NEW',
            'forecast_basis' => 'Research-led range hypothesis.',
        ])->assertCreated();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/freeze', [])
            ->assertOk();

        $this->actAsOwner();
        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/range-plans/'.$rangePlanId.'/lines')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $line->json('data.id'));

        $assortment = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/assortment-plans', [
            'range_plan_id' => $rangePlanId,
            'code' => 'READ-ASSORTMENT-'.Str::upper(Str::random(4)),
            'channel_code' => 'OWN_RETAIL',
            'market_scope' => 'Addis Ababa',
        ])->assertCreated();

        $assortmentId = $assortment->json('data.id');
        $assortmentLine = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/assortment-plans/'.$assortmentId.'/lines', [
            'range_line_id' => $line->json('data.id'),
            'variant_size_curve' => ['S' => 40, 'M' => 60],
            'planned_units' => 100,
        ])->assertCreated();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/assortment-plans')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $assortmentId)
            ->assertJsonPath('data.data.0.range_plan_code', $range->json('data.code'));

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/assortment-plans/'.$assortmentId.'/lines')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $assortmentLine->json('data.id'));

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/range-plans/'.Str::uuid().'/lines')
            ->assertNotFound();
        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/assortment-plans/'.Str::uuid().'/lines')
            ->assertNotFound();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/merchandising-dashboard')
            ->assertOk()
            ->assertJsonPath('data.tenant_role', 'OWNER')
            ->assertJsonPath('data.work_queue.approval_scope', 'TENANT_APPROVALS')
            ->assertJsonPath('data.kpis.1.key', 'range_brief_readiness')
            ->assertJsonPath('data.kpis.1.status', 'AVAILABLE')
            ->assertJsonPath('data.kpis.1.value', 0)
            ->assertJsonPath('data.kpis.1.numerator', 0)
            ->assertJsonPath('data.kpis.1.denominator', 1)
            ->assertJsonPath('data.range_lifecycle_mix.0.lifecycle_type', 'NEW')
            ->assertJsonPath('data.range_lifecycle_mix.0.planned_units', 100)
            ->assertJsonPath('data.range_category_depth.0.category_code', 'CASUALWEAR')
            ->assertJsonPath('data.range_category_depth.0.planned_units', 100)
            ->assertJsonPath('data.evidence_register.sufficiency_status', 'NOT_CONFIGURED')
            ->assertJsonPath('data.evidence_register.sufficiency_reason', 'No approved, effective tenant evidence policy is currently active. The legacy sourced-insight minimum remains in force and is not a full sufficiency assessment.');
    }

    public function test_scoped_administrator_cannot_freeze_a_range_without_brand_and_market_grants(): void
    {
        $range = $this->createDraftRangePlanForAcceptance();

        $unknownUserId = (string) Str::uuid();

        $this->withHeaders($this->tenantHeader())
            ->putJson('/api/v1/enterprise/members/'.$unknownUserId.'/access-scope-mode', [
                'scope_mode' => 'SCOPED',
                'reason' => 'An unrelated non-member must not receive a tenant access profile.',
            ])
            ->assertNotFound();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/members/'.$unknownUserId.'/access-scopes', [
                'scope_type' => 'MARKET',
                'scope_code' => 'ETHIOPIA',
                'permission_code' => 'READ',
                'grant_reason' => 'An unrelated non-member must not receive a tenant access grant.',
            ])
            ->assertNotFound();

        $this->withHeaders($this->tenantHeader())
            ->putJson('/api/v1/enterprise/members/'.$this->approverId.'/access-scope-mode', [
                'scope_mode' => 'SCOPED',
                'reason' => 'Restrict this approver to explicitly assigned brands and markets.',
            ])
            ->assertOk()
            ->assertJsonPath('data.scope_mode', 'SCOPED');

        $this->actAsApprover();
        $freezeUrl = '/api/v1/product/range-plans/'.$range['range_plan_id'].'/freeze';
        $this->withHeaders($this->tenantHeader())
            ->postJson($freezeUrl, [])
            ->assertForbidden()
            ->assertJsonPath('message', 'The authenticated tenant member does not have the required scope for this brand, site or market action.');

        $this->actAsOwner();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/members/'.$this->approverId.'/access-scopes', [
                'scope_type' => 'BRAND',
                'scope_id' => $range['brand_id'],
                'permission_code' => 'APPROVE',
                'grant_reason' => 'The approver is responsible for decisions on this brand.',
            ])
            ->assertCreated();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/members/'.$this->approverId.'/access-scopes', [
                'scope_type' => 'MARKET',
                'scope_code' => 'Ethiopia',
                'permission_code' => 'APPROVE',
                'grant_reason' => 'The approver is assigned to this target market.',
            ])
            ->assertCreated();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/enterprise/members/'.$this->approverId.'/access-scopes')
            ->assertOk()
            ->assertJsonPath('data.scope_mode', 'SCOPED')
            ->assertJsonCount(2, 'data.grants');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson($freezeUrl, [])
            ->assertOk()
            ->assertJsonPath('data.status', 'FROZEN');
    }

    public function test_own_retail_assortment_is_simulation_only_until_current_legal_evidence_is_verified(): void
    {
        $range = $this->createDraftRangePlanForAcceptance();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/range-plans/'.$range['range_plan_id'].'/freeze', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'FROZEN');

        $this->actAsOwner();
        $entity = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/legal-entities', [
                'registration_number' => 'ATMER-REG-'.Str::upper(Str::random(6)),
                'legal_name' => 'Acceptance Apparel Trading Entity',
                'legal_form' => 'PRIVATE_LIMITED_COMPANY',
                'country_code' => 'ET',
                'tax_identifier' => 'ATMER-TIN-'.Str::upper(Str::random(6)),
                'base_currency' => 'ETB',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT');

        $site = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/sites', [
                'legal_entity_id' => $entity->json('data.id'),
                'code' => 'ATMER-STORE-'.Str::upper(Str::random(4)),
                'site_type' => 'STORE_PREMISES',
                'name' => 'Acceptance Retail Store',
                'country_code' => 'ET',
                'region' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'timezone' => 'Africa/Addis_Ababa',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT');

        $siteId = $site->json('data.id');
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/sites/'.$siteId.'/nodes', [
                'code' => 'ATMER-RETAIL-NODE-'.Str::upper(Str::random(4)),
                'node_type' => 'RETAIL_STORE',
                'name' => 'Acceptance store node',
                'capabilities' => ['RETAIL', 'STOCK_HOLDING'],
                'stock_holding_enabled' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/sites/'.$siteId.'/branch-registrations', [
                'commercial_register_reference' => 'ATMER-BRANCH-'.Str::upper(Str::random(6)),
                'activity_codes' => ['RETAIL'],
                'issuing_authority' => 'Acceptance-test authority placeholder',
                'valid_from' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'REVIEW_REQUIRED');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/enterprise/legal-entities/'.$entity->json('data.id').'/licences', [
                'site_id' => $siteId,
                'activity_code' => 'RETAIL',
                'licence_number' => 'ATMER-LIC-'.Str::upper(Str::random(6)),
                'issuing_authority' => 'Acceptance-test authority placeholder',
                'valid_from' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'REVIEW_REQUIRED');

        $assortment = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans', [
                'range_plan_id' => $range['range_plan_id'],
                'code' => 'ATMER-OWN-RETAIL-'.Str::upper(Str::random(5)),
                'channel_code' => 'OWN_RETAIL',
                'market_scope' => 'Ethiopia',
                'business_site_id' => $siteId,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.commercial_eligibility_status', 'SIMULATION_ONLY')
            ->assertJsonPath('data.commercial_activation_allowed', false);

        $this->assertStringContainsString(
            'Current verified branch registration evidence is required.',
            $assortment->json('data.commercial_block_reason'),
        );
        $this->assertStringContainsString(
            'Current verified RETAIL activity licence evidence is required.',
            $assortment->json('data.commercial_block_reason'),
        );
        $this->assertDatabaseHas('assortment_plans', [
            'tenant_id' => $this->tenantId,
            'id' => $assortment->json('data.id'),
            'commercial_eligibility_status' => 'SIMULATION_ONLY',
        ], 'pgsql_test_admin');
    }

    public function test_wholesale_and_reseller_assortments_keep_independent_approved_account_terms_versions(): void
    {
        $range = $this->createDraftRangePlanForAcceptance();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/range-plans/'.$range['range_plan_id'].'/freeze', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'FROZEN');
        $this->actAsOwner();

        $entityId = (string) Str::uuid();
        DB::connection('pgsql_test_admin')->table('legal_entities')->insert([
            'id' => $entityId,
            'tenant_id' => $this->tenantId,
            'registration_number' => 'ATMER-TERMS-'.Str::upper(Str::random(6)),
            'legal_name' => 'Acceptance Wholesale Trading Entity',
            'country_code' => 'ET',
            'base_currency' => 'ETB',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $createPublishedPriceList = function (string $channel, string $account, string $code) use ($entityId, $range): string {
            $list = $this->withHeaders($this->tenantHeader())
                ->postJson('/api/v1/product/price-lists', [
                    'legal_entity_id' => $entityId,
                    'brand_id' => $range['brand_id'],
                    'code' => $code,
                    'channel_code' => $channel,
                    'market_scope' => 'ETHIOPIA',
                    'account_reference' => $account,
                    'currency' => 'ETB',
                    'effective_from' => '2026-11-22T00:00:00Z',
                    'effective_to' => '2028-12-31T23:59:59Z',
                ])
                ->assertCreated();

            $listId = $list->json('data.id');
            $this->withHeaders($this->tenantHeader())
                ->postJson('/api/v1/product/price-lists/'.$listId.'/lines', [
                    'product_reference' => 'ATMER-STYLE-01',
                    'list_price' => '1800.0000',
                    'wholesale_price' => '1200.0000',
                    'price_floor' => '1100.0000',
                    'cost_basis' => '800.0000',
                ])
                ->assertCreated();

            $this->actAsApprover();
            $this->withHeaders($this->tenantHeader())
                ->postJson('/api/v1/product/price-lists/'.$listId.'/publish', [])
                ->assertOk()
                ->assertJsonPath('data.status', 'PUBLISHED');
            $this->actAsOwner();

            return $listId;
        };

        $wholesalePriceListId = $createPublishedPriceList(
            'WHOLESALE',
            'DISTRIBUTOR-001',
            'ATMER-WHOLESALE-'.Str::upper(Str::random(5)),
        );
        $resellerPriceListId = $createPublishedPriceList(
            'RESELLER',
            'RESELLER-002',
            'ATMER-RESELLER-'.Str::upper(Str::random(5)),
        );

        $wholesalePlan = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans', [
                'range_plan_id' => $range['range_plan_id'],
                'code' => 'ATMER-WHOLESALE-ASSORTMENT-'.Str::upper(Str::random(4)),
                'channel_code' => 'WHOLESALE',
                'market_scope' => 'ETHIOPIA',
                'account_reference' => 'DISTRIBUTOR-001',
                'location_cluster_reference' => 'NORTH',
            ])
            ->assertCreated();
        $wholesalePlanId = $wholesalePlan->json('data.id');

        $resellerPlan = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans', [
                'range_plan_id' => $range['range_plan_id'],
                'code' => 'ATMER-RESELLER-ASSORTMENT-'.Str::upper(Str::random(4)),
                'channel_code' => 'RESELLER',
                'market_scope' => 'ETHIOPIA',
                'account_reference' => 'RESELLER-002',
                'location_cluster_reference' => 'ADDIS',
            ])
            ->assertCreated();
        $resellerPlanId = $resellerPlan->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms', [
                'price_list_id' => $resellerPriceListId,
                'commercial_terms_reference' => 'CONTRACT-RESELLER-002',
                'payment_terms_reference' => 'PAYMENT-POLICY-NET30',
                'delivery_terms_reference' => 'DELIVERY-POLICY-DC-ADDIS',
                'effective_from' => '2027-01-01',
                'effective_to' => '2027-06-30',
                'change_reason' => 'Verify that a reseller price book cannot be assigned to a wholesale account.',
            ])
            ->assertUnprocessable();

        $terms = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms', [
                'price_list_id' => $wholesalePriceListId,
                'account_tier' => 'GOLD',
                'commercial_terms_reference' => 'CONTRACT-DISTRIBUTOR-001',
                'payment_terms_reference' => 'PAYMENT-POLICY-NET30',
                'delivery_terms_reference' => 'DELIVERY-POLICY-DC-ADDIS',
                'returns_policy_reference' => 'RETURNS-POLICY-WHOLESALE',
                'minimum_order_units' => 240,
                'order_multiple_units' => 24,
                'effective_from' => '2027-01-01',
                'effective_to' => '2027-06-30',
                'change_reason' => 'Initial approved wholesale account terms for the planned assortment.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.version_no', 1)
            ->assertJsonPath('data.currency', 'ETB')
            ->assertJsonPath('data.account_reference', 'DISTRIBUTOR-001');
        $termsId = $terms->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$termsId.'/submit', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'PENDING_APPROVAL');

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/merchandising-dashboard')
            ->assertOk()
            ->assertJsonMissing([
                'type' => 'ASSORTMENT_ACCOUNT_TERMS_APPROVAL',
                'id' => $termsId,
            ]);

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$termsId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The terms author cannot approve their own wholesale commercial terms.',
            ])
            ->assertForbidden();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/merchandising-dashboard')
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'ASSORTMENT_ACCOUNT_TERMS_APPROVAL',
                'id' => $termsId,
            ]);

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$termsId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The wholesale account, price list and effective terms have been independently reviewed.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED');

        $this->actAsOwner();
        $overlappingTerms = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms', [
                'price_list_id' => $wholesalePriceListId,
                'account_tier' => 'GOLD',
                'commercial_terms_reference' => 'CONTRACT-DISTRIBUTOR-001-OVERLAP',
                'payment_terms_reference' => 'PAYMENT-POLICY-NET30',
                'delivery_terms_reference' => 'DELIVERY-POLICY-DC-ADDIS',
                'effective_from' => '2027-06-01',
                'effective_to' => '2027-12-31',
                'change_reason' => 'Test an overlapping account-terms renewal against the approved version.',
            ])
            ->assertCreated();

        $overlappingId = $overlappingTerms->json('data.id');
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$overlappingId.'/submit', [])
            ->assertOk();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$overlappingId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'This overlapping version must not replace the current effective account terms.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Approved account terms already overlap this channel, market, account and effective period. Create a new non-overlapping version.');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$overlappingId.'/decisions', [
                'decision' => 'REJECT',
                'reason' => 'The renewal period overlaps the approved version and must not become effective.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'REJECTED');

        $this->actAsOwner();
        $nextTerms = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms', [
                'price_list_id' => $wholesalePriceListId,
                'account_tier' => 'GOLD',
                'commercial_terms_reference' => 'CONTRACT-DISTRIBUTOR-001-RENEWAL',
                'payment_terms_reference' => 'PAYMENT-POLICY-NET30',
                'delivery_terms_reference' => 'DELIVERY-POLICY-DC-ADDIS',
                'effective_from' => '2027-07-01',
                'effective_to' => '2027-12-31',
                'change_reason' => 'Non-overlapping successor account terms for the second half of the trading year.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.version_no', 3);

        $nextTermsId = $nextTerms->json('data.id');
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$nextTermsId.'/submit', [])
            ->assertOk();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms/'.$nextTermsId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The non-overlapping successor period is independently approved.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED');

        $this->actAsOwner();
        $resellerTerms = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$resellerPlanId.'/account-terms', [
                'price_list_id' => $resellerPriceListId,
                'account_tier' => 'SILVER',
                'commercial_terms_reference' => 'CONTRACT-RESELLER-002',
                'payment_terms_reference' => 'PAYMENT-POLICY-NET15',
                'delivery_terms_reference' => 'DELIVERY-POLICY-RESELLER-ADDIS',
                'returns_policy_reference' => 'RETURNS-POLICY-RESELLER',
                'minimum_order_units' => 60,
                'order_multiple_units' => 12,
                'effective_from' => '2027-01-01',
                'effective_to' => '2027-12-31',
                'change_reason' => 'Independent reseller account terms and price-book linkage.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.account_reference', 'RESELLER-002')
            ->assertJsonPath('data.price_list_id', $resellerPriceListId);

        $resellerTermsId = $resellerTerms->json('data.id');
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$resellerPlanId.'/account-terms/'.$resellerTermsId.'/submit', [])
            ->assertOk();
        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/assortment-plans/'.$resellerPlanId.'/account-terms/'.$resellerTermsId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The distinct reseller account terms are independently approved.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED');

        $this->actAsOwner();
        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/assortment-plans/'.$wholesalePlanId.'/account-terms')
            ->assertOk()
            ->assertJsonCount(3, 'data.data')
            ->assertJsonFragment(['account_reference' => 'DISTRIBUTOR-001', 'price_list_id' => $wholesalePriceListId]);
        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/assortment-plans/'.$resellerPlanId.'/account-terms')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['account_reference' => 'RESELLER-002', 'price_list_id' => $resellerPriceListId]);

        $this->expectException(QueryException::class);
        DB::connection('pgsql_test_admin')->table('assortment_account_terms')
            ->where('tenant_id', $this->tenantId)
            ->where('id', $termsId)
            ->update(['payment_terms_reference' => 'UNCONTROLLED-CHANGE']);
    }

    public function test_approved_range_line_requires_an_accepted_brief_before_version_pinned_style_development(): void
    {
        $brandId = $this->createApprovedBrand();

        $season = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/seasons', [
            'code' => 'PLM-SEASON-'.Str::upper(Str::random(4)),
            'name' => 'Product development season',
            'calendar_type' => 'FASHION',
            'timezone' => 'Africa/Addis_Ababa',
        ])->assertCreated();

        $collection = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections', [
            'brand_id' => $brandId,
            'season_id' => $season->json('data.id'),
            'code' => 'PLM-COLLECTION-'.Str::upper(Str::random(4)),
            'name' => 'Product development collection',
            'target_market' => 'Ethiopia',
        ])->assertCreated();
        $collectionId = $collection->json('data.id');

        $collectionVersion = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/collections/'.$collectionId.'/versions', [
            'story' => 'Functional everyday garments designed for local climate and use.',
            'target_consumer' => 'Urban adults seeking durable clothing.',
            'category_architecture' => ['CASUALWEAR'],
            'price_margin_envelope' => ['currency' => 'ETB', 'margin_floor' => 25],
            'change_reason' => 'Approve the seasonal product architecture for development.',
        ])->assertCreated();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/collections/'.$collectionId.'/versions/'.$collectionVersion->json('data.id').'/approve', [])
            ->assertOk();
        $this->actAsOwner();

        $range = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans', [
            'collection_id' => $collectionId,
            'collection_version' => 1,
            'code' => 'PLM-RANGE-'.Str::upper(Str::random(4)),
            'plan_start' => '2027-01-01',
            'plan_end' => '2027-03-31',
        ])->assertCreated();

        $rangePlanId = $range->json('data.id');
        $line = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/lines', [
            'line_code' => 'PLM-STYLE-FAMILY-01',
            'category_code' => 'CASUALWEAR',
            'product_hypothesis' => 'An everyday overshirt with durable construction and versatile use.',
            'planned_units' => 500,
            'channel_scope' => ['OWN_RETAIL', 'WHOLESALE'],
            'size_colour_intent' => ['sizes' => ['S', 'M', 'L'], 'colours' => ['NAVY', 'OLIVE']],
            'lifecycle_type' => 'NEW',
            'forecast_basis' => 'Initial range hypothesis pending product development validation.',
        ])->assertCreated();

        $briefPayload = [
            'range_line_id' => $line->json('data.id'),
            'target_consumer' => 'Urban adults needing durable everyday garments.',
            'product_need' => 'A versatile overshirt that handles daily wear and repeated laundering.',
            'intended_use' => 'Daily commuting, casual work and weekend wear.',
            'target_price' => 1800,
            'target_cost' => 720,
            'target_currency' => 'ETB',
            'quality_tier' => 'GOOD',
            'delivery_need_by' => '2027-03-15',
            'sustainability_intent' => ['Prefer traceable fibre sources', 'Minimize avoidable cutting waste'],
            'change_reason' => 'Initial development brief linked to an approved frozen range line.',
        ];

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs', $briefPayload)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A product brief requires a frozen range line from the current approved collection and an active brand strategy.');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/range-plans/'.$rangePlanId.'/freeze', [])
            ->assertOk();
        $this->actAsOwner();

        $brief = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs', $briefPayload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.current_version', 1)
            ->assertJsonPath('data.accepted_version', null);
        $briefId = $brief->json('data.id');

        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/product-briefs')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $briefId)
            ->assertJsonPath('data.data.0.line_code', 'PLM-STYLE-FAMILY-01');

        $stylePayload = [
            'product_brief_id' => $briefId,
            'style_code' => 'PLM-STYLE-001',
            'name' => 'Everyday overshirt',
            'category_code' => 'CASUALWEAR',
            'design_direction' => 'A clean, versatile overshirt with a practical everyday silhouette.',
            'silhouette' => 'Relaxed straight fit',
            'fit_intent' => 'Allow comfortable movement over a light base layer.',
            'construction_intent' => 'Use durable seams and reinforced stress points suitable for repeated wear.',
            'material_direction' => ['preferred_fibre' => 'Cotton blend', 'handle' => 'Midweight, breathable'],
            'colour_direction' => ['core' => ['NAVY', 'OLIVE']],
            'target_size_range' => ['system' => 'TENANT_DEFINED', 'sizes' => ['S', 'M', 'L']],
            'change_summary' => 'Initial creative and fit direction for the accepted product brief.',
        ];

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles', $stylePayload)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A style can only be created from the currently accepted product brief version.');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs/'.$briefId.'/submit', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'SUBMITTED_FOR_REVIEW');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs/'.$briefId.'/decisions', [
                'decision' => 'ACCEPT',
                'reason' => 'The brief creator cannot accept their own development request.',
            ])
            ->assertForbidden();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs/'.$briefId.'/decisions', [
                'decision' => 'ACCEPT',
                'reason' => 'The commercial and consumer need is clear enough to authorize product development.',
                'actions' => ['Validate fit and construction during sample review'],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ACCEPTED')
            ->assertJsonPath('data.accepted_version', 1);
        $this->actAsOwner();

        $style = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles', $stylePayload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'DESIGN_IN_PROGRESS')
            ->assertJsonPath('data.current_revision', 1)
            ->assertJsonPath('data.product_brief_version', 1);
        $styleId = $style->json('data.id');

        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/styles/'.$styleId)
            ->assertOk()
            ->assertJsonPath('data.product_brief_version', 1)
            ->assertJsonCount(1, 'revisions')
            ->assertJsonPath('revisions.0.revision_no', 1);

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles/'.$styleId.'/revisions', [
                'design_direction' => 'Refined everyday overshirt with reduced bulk at high-movement points.',
                'silhouette' => 'Relaxed straight fit, refined shoulder balance',
                'fit_intent' => 'Maintain ease through shoulder and chest while improving sleeve movement.',
                'construction_intent' => 'Reinforce pocket corners and stress points while controlling seam bulk.',
                'material_direction' => ['preferred_fibre' => 'Cotton blend', 'weight_target' => 'Midweight'],
                'colour_direction' => ['core' => ['NAVY'], 'seasonal' => ['OLIVE']],
                'target_size_range' => ['system' => 'TENANT_DEFINED', 'sizes' => ['S', 'M', 'L', 'XL']],
                'change_summary' => 'Refine shoulder balance and reinforce high-stress construction points.',
            ])
            ->assertOk()
            ->assertJsonPath('data.current_revision', 2);

        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/styles')
            ->assertOk()
            ->assertJsonPath('data.data.0.latest_revision_no', 2);
        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/styles/'.$styleId)
            ->assertOk()
            ->assertJsonCount(2, 'revisions')
            ->assertJsonPath('revisions.0.revision_no', 2)
            ->assertJsonPath('revisions.1.revision_no', 1);

        $measurementSpec = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles/'.$styleId.'/measurement-specs', [
                'size_system_code' => 'BRAND-ADULT-CM',
                'size_sequence' => ['XS', 'S', 'M', 'L'],
                'base_size_code' => 'M',
                'measurement_condition' => 'Garment measured flat and circumference doubled; relaxed condition.',
                'change_reason' => 'Initial approved size specification for the development pattern.',
                'points' => [
                    [
                        'point_code' => 'CHEST_CIRC',
                        'name' => 'Chest circumference',
                        'measurement_method' => 'Measure horizontally around the garment at the underarm level.',
                        'unit_code' => 'CM',
                        'is_required' => true,
                        'values' => [
                            ['size_code' => 'XS', 'nominal_value' => 92, 'tolerance_minus' => 1, 'tolerance_plus' => 1],
                            ['size_code' => 'S', 'nominal_value' => 96, 'tolerance_minus' => 1, 'tolerance_plus' => 1],
                            ['size_code' => 'M', 'nominal_value' => 100, 'tolerance_minus' => 1, 'tolerance_plus' => 1],
                            ['size_code' => 'L', 'nominal_value' => 104, 'tolerance_minus' => 1, 'tolerance_plus' => 1],
                        ],
                    ],
                    [
                        'point_code' => 'BODY_LENGTH',
                        'name' => 'Body length',
                        'measurement_method' => 'Measure from high point shoulder to finished hem.',
                        'unit_code' => 'CM',
                        'is_required' => true,
                        'values' => [
                            ['size_code' => 'XS', 'nominal_value' => 66, 'tolerance_minus' => 0.5, 'tolerance_plus' => 0.5],
                            ['size_code' => 'S', 'nominal_value' => 68, 'tolerance_minus' => 0.5, 'tolerance_plus' => 0.5],
                            ['size_code' => 'M', 'nominal_value' => 70, 'tolerance_minus' => 0.5, 'tolerance_plus' => 0.5],
                            ['size_code' => 'L', 'nominal_value' => 72, 'tolerance_minus' => 0.5, 'tolerance_plus' => 0.5],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT');
        $specId = $measurementSpec->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/styles/'.$styleId.'/measurement-specs')
            ->assertOk()
            ->assertJsonPath('data.0.id', $specId)
            ->assertJsonCount(2, 'data.0.points')
            ->assertJsonCount(4, 'data.0.points.0.values');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles/'.$styleId.'/measurement-specs/'.$specId.'/approve', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED');
        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/styles/'.$styleId)
            ->assertOk()
            ->assertJsonPath('data.status', 'TECHNICAL_DESIGN');
        $this->actAsOwner();

        $pieces = [
            ['piece_code' => 'FRONT', 'description' => 'Left and right front body panels.', 'material_reference' => 'MAIN_FABRIC', 'grain_direction' => 'LENGTHWISE', 'seam_allowance' => ['side' => 1, 'shoulder' => 1, 'hem' => 3], 'piece_count' => 2],
            ['piece_code' => 'BACK', 'description' => 'Back body panel.', 'material_reference' => 'MAIN_FABRIC', 'grain_direction' => 'LENGTHWISE', 'seam_allowance' => ['side' => 1, 'shoulder' => 1, 'hem' => 3], 'piece_count' => 1],
            ['piece_code' => 'SLEEVE', 'description' => 'Long sleeve panels.', 'material_reference' => 'MAIN_FABRIC', 'grain_direction' => 'LENGTHWISE', 'seam_allowance' => ['underarm' => 1, 'cuff' => 3], 'piece_count' => 2],
        ];
        $completeGradeRules = [
            ['point_code' => 'CHEST_CIRC', 'from_size_code' => 'XS', 'to_size_code' => 'S', 'increment' => 4, 'unit_code' => 'CM'],
            ['point_code' => 'CHEST_CIRC', 'from_size_code' => 'S', 'to_size_code' => 'M', 'increment' => 4, 'unit_code' => 'CM'],
            ['point_code' => 'CHEST_CIRC', 'from_size_code' => 'M', 'to_size_code' => 'L', 'increment' => 4, 'unit_code' => 'CM'],
            ['point_code' => 'BODY_LENGTH', 'from_size_code' => 'XS', 'to_size_code' => 'S', 'increment' => 2, 'unit_code' => 'CM'],
            ['point_code' => 'BODY_LENGTH', 'from_size_code' => 'S', 'to_size_code' => 'M', 'increment' => 2, 'unit_code' => 'CM'],
            ['point_code' => 'BODY_LENGTH', 'from_size_code' => 'M', 'to_size_code' => 'L', 'increment' => 2, 'unit_code' => 'CM'],
        ];
        $patternSet = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles/'.$styleId.'/patterns', [
                'pattern_code' => 'OVERSHIRT-MAIN',
                'name' => 'Main overshirt pattern',
                'measurement_spec_version_id' => $specId,
                'pieces' => $pieces,
                'grade_rules' => array_slice($completeGradeRules, 0, 5),
                'change_summary' => 'Initial pattern architecture for the approved measurement revision.',
            ])
            ->assertCreated()
            ->assertJsonPath('revision.status', 'DRAFT');
        $patternSetId = $patternSet->json('data.id');
        $patternRevisionId = $patternSet->json('revision.id');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/patterns/'.$patternSetId.'/revisions/'.$patternRevisionId.'/approve', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.grade_rules.0', 'Every required measurement point needs an approved grade increment for adjacent sizes; missing BODY_LENGTH: M to L.');
        $this->actAsOwner();

        $correctedPattern = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/patterns/'.$patternSetId.'/revisions', [
                'measurement_spec_version_id' => $specId,
                'pieces' => $pieces,
                'grade_rules' => $completeGradeRules,
                'change_summary' => 'Complete all mandatory grade points and correct the size progression.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.revision_no', 2);
        $correctedPatternRevisionId = $correctedPattern->json('data.id');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/patterns/'.$patternSetId.'/revisions/'.$correctedPatternRevisionId.'/approve', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED')
            ->assertJsonPath('pattern_set.current_revision', 2);
        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/styles/'.$styleId)
            ->assertOk()
            ->assertJsonPath('data.status', 'PATTERN_DEVELOPMENT');
        $this->actAsOwner();

        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/styles/'.$styleId.'/patterns')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'APPROVED')
            ->assertJsonPath('data.0.current_revision', 2)
            ->assertJsonPath('data.0.latest_revision_status', 'APPROVED');

        $sample = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles/'.$styleId.'/sample-requests', [
                'pattern_set_id' => $patternSetId,
                'pattern_revision_id' => $correctedPatternRevisionId,
                'sample_type' => 'FIT',
                'purpose' => 'Validate the approved overshirt fit before technical release.',
                'requested_quantity' => 1,
                'requested_size_codes' => ['M'],
                'requested_colour_codes' => ['NAVY'],
                'required_by' => '2027-03-25',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'REQUESTED')
            ->assertJsonPath('data.pattern_revision_id', $correctedPatternRevisionId)
            ->assertJsonPath('data.measurement_spec_version_id', $specId)
            ->assertJsonPath('round.round_no', 1);
        $sampleRequestId = $sample->json('data.id');
        $sampleRoundId = $sample->json('round.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-requests/'.$sampleRequestId.'/rounds/'.$sampleRoundId.'/start', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'IN_PROGRESS');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-requests/'.$sampleRequestId.'/rounds/'.$sampleRoundId.'/complete', [
                'actual_quantity' => 1,
                'completion_summary' => 'First fit sample built with the approved pattern revision; ready for physical fit review.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'COMPLETED');

        $fitSession = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-rounds/'.$sampleRoundId.'/fit-sessions', [
                'fit_size_code' => 'M',
                'fit_model_code' => 'FIT-M-01',
                'posture_code' => 'STANDING',
                'condition_notes' => 'Garment worn over a light base layer after pressing.',
                'session_date' => '2026-11-22',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT');
        $fitSessionId = $fitSession->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$fitSessionId.'/observations', [
                'observations' => [
                    [
                        'point_code' => 'CHEST_CIRC',
                        'observed_value' => 108,
                        'issue_code' => 'CHEST_EASE',
                        'severity' => 'MAJOR',
                        'finding' => 'Chest circumference is above the approved upper tolerance.',
                        'corrective_action' => 'Reduce chest ease and recheck body balance.',
                    ],
                    [
                        'point_code' => 'BODY_LENGTH',
                        'observed_value' => 70,
                        'issue_code' => 'NONE',
                        'severity' => 'NONE',
                        'finding' => null,
                        'corrective_action' => null,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.0.within_tolerance', false)
            ->assertJsonPath('data.1.within_tolerance', true);

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$fitSessionId.'/submit', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'SUBMITTED_FOR_REVIEW');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$fitSessionId.'/decisions', [
                'decision' => 'CORRECTION_REQUIRED',
                'reason' => 'The chest circumference exceeds its approved size tolerance and requires pattern correction.',
                'actions' => ['Reduce chest ease and submit a corrected fit sample.'],
            ])
            ->assertForbidden();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$fitSessionId.'/decisions', [
                'decision' => 'CORRECTION_REQUIRED',
                'reason' => 'The chest circumference exceeds its approved size tolerance and requires pattern correction.',
                'actions' => ['Reduce chest ease and submit a corrected fit sample.'],
            ])
            ->assertOk()
            ->assertJsonPath('sample_request.status', 'CORRECTION_REQUIRED');
        $this->actAsOwner();

        $correctionRound = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-requests/'.$sampleRequestId.'/rounds', [
                'round_reason' => 'Build a corrected fit sample after the chest-ease correction.',
                'planned_quantity' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('round.round_no', 2)
            ->assertJsonPath('round.status', 'REQUESTED');
        $correctionRoundId = $correctionRound->json('round.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-requests/'.$sampleRequestId.'/rounds/'.$correctionRoundId.'/start', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'IN_PROGRESS');
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-requests/'.$sampleRequestId.'/rounds/'.$correctionRoundId.'/complete', [
                'actual_quantity' => 1,
                'completion_summary' => 'Corrected fit sample built and ready for a second fit review.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'COMPLETED');

        $correctedFit = $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/sample-rounds/'.$correctionRoundId.'/fit-sessions', [
                'fit_size_code' => 'M',
                'fit_model_code' => 'FIT-M-01',
                'posture_code' => 'STANDING',
                'condition_notes' => 'Same fit conditions used for the corrected comparison.',
                'session_date' => '2026-11-22',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'DRAFT');
        $correctedFitId = $correctedFit->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$correctedFitId.'/observations', [
                'observations' => [
                    [
                        'point_code' => 'CHEST_CIRC',
                        'observed_value' => 100,
                        'issue_code' => 'NONE',
                        'severity' => 'NONE',
                        'finding' => null,
                        'corrective_action' => null,
                    ],
                    [
                        'point_code' => 'BODY_LENGTH',
                        'observed_value' => 70,
                        'issue_code' => 'NONE',
                        'severity' => 'NONE',
                        'finding' => null,
                        'corrective_action' => null,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.0.within_tolerance', true)
            ->assertJsonPath('data.1.within_tolerance', true);

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$correctedFitId.'/submit', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'SUBMITTED_FOR_REVIEW');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/fit-sessions/'.$correctedFitId.'/decisions', [
                'decision' => 'APPROVE',
                'reason' => 'The corrected sample is within the approved chest and body-length measurement tolerances.',
            ])
            ->assertOk()
            ->assertJsonPath('sample_request.status', 'FIT_APPROVED');
        $this->actAsOwner();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/sample-requests/'.$sampleRequestId)
            ->assertOk()
            ->assertJsonPath('data.status', 'FIT_APPROVED')
            ->assertJsonCount(2, 'rounds')
            ->assertJsonCount(2, 'fit_sessions')
            ->assertJsonPath('fit_sessions.0.status', 'DECIDED')
            ->assertJsonPath('fit_sessions.1.status', 'DECIDED')
            ->assertJsonPath('fit_sessions.0.decisions.0.decision', 'CORRECTION_REQUIRED')
            ->assertJsonPath('fit_sessions.1.decisions.0.decision', 'APPROVE');

        $versionPayload = $briefPayload;
        unset($versionPayload['range_line_id']);
        $versionPayload['target_consumer'] = 'Urban adults seeking longer-wear garments.';
        $versionPayload['product_need'] = 'A versatile overshirt with improved movement and lower lifecycle impact.';
        $versionPayload['change_reason'] = 'Revise the development brief after initial design review feedback.';

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs/'.$briefId.'/versions', $versionPayload)
            ->assertOk()
            ->assertJsonPath('data.current_version', 2)
            ->assertJsonPath('data.accepted_version', 1)
            ->assertJsonPath('data.status', 'DRAFT');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles', array_merge($stylePayload, ['style_code' => 'PLM-STYLE-002']))
            ->assertUnprocessable();

        $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/styles/'.$styleId)
            ->assertOk()
            ->assertJsonPath('data.product_brief_version', 1)
            ->assertJsonCount(2, 'revisions');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs/'.$briefId.'/submit', [])
            ->assertOk();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product-briefs/'.$briefId.'/decisions', [
                'decision' => 'ACCEPT',
                'reason' => 'The revised product need and use case are acceptable for a new style.',
            ])
            ->assertOk()
            ->assertJsonPath('data.accepted_version', 2);
        $this->actAsOwner();

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/styles', array_merge($stylePayload, ['style_code' => 'PLM-STYLE-002']))
            ->assertCreated()
            ->assertJsonPath('data.product_brief_version', 2);

        $this->withHeaders($this->tenantHeader())->getJson('/api/v1/product-briefs/'.$briefId)
            ->assertOk()
            ->assertJsonPath('data.line_code', 'PLM-STYLE-FAMILY-01')
            ->assertJsonPath('data.range_plan_code', $range->json('data.code'))
            ->assertJsonPath('data.collection_name', 'Product development collection')
            ->assertJsonCount(2, 'versions')
            ->assertJsonCount(2, 'decisions')
            ->assertJsonCount(2, 'styles');
    }
}
