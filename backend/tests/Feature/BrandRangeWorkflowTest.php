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
    }

}