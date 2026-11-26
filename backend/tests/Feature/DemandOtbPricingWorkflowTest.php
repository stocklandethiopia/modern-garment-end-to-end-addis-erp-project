<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DemandOtbPricingWorkflowTest extends TestCase
{
    private string $tenantId;

    private string $ownerId;

    private string $approverId;

    private string $legalEntityId;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        $this->tenantId = (string) Str::uuid();
        $this->ownerId = (string) Str::uuid();
        $this->approverId = (string) Str::uuid();
        $this->legalEntityId = (string) Str::uuid();

        $admin->transaction(function () use ($admin, $now): void {
            $admin->table('tenants')->insert([
                'id' => $this->tenantId,
                'code' => 'PLAN-'.Str::upper(Str::random(8)),
                'name' => 'Planning workflow tenant',
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $admin->table('users')->insert([
                [
                    'id' => $this->ownerId,
                    'name' => 'Merchandising Planner',
                    'email' => 'planner-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('a-long-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => $this->approverId,
                    'name' => 'Commercial Finance Approver',
                    'email' => 'commercial-approver-'.Str::lower(Str::random(8)).'@example.test',
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

            $admin->table('legal_entities')->insert([
                'id' => $this->legalEntityId,
                'tenant_id' => $this->tenantId,
                'registration_number' => 'REG-'.$this->tenantId,
                'legal_name' => 'Planning Test Apparel PLC',
                'country_code' => 'ET',
                'base_currency' => 'ETB',
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
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

    private function createForecast(): string
    {
        $response = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/forecast-runs', [
            'grain' => 'STYLE_WEEK',
            'method' => 'MANUAL_CONSENSUS',
            'horizon' => '13_WEEKS',
            'scenario_code' => 'BASELINE',
            'forecast_start' => '2027-01-04',
            'forecast_end' => '2027-01-10',
            'assumptions' => ['basis' => 'store research and prior season sell-through'],
            'forecasts' => [
                [
                    'dimension_refs' => ['style' => 'STYLE-001', 'channel' => 'OWN_RETAIL'],
                    'time_bucket' => '2027-01-04',
                    'forecast_quantity' => '100.0000',
                    'forecast_value' => '150000.0000',
                    'currency' => 'ETB',
                    'confidence_low' => '85.0000',
                    'confidence_high' => '120.0000',
                ],
            ],
        ])->assertCreated()->assertJsonPath('row_count', 1);

        return $response->json('data.id');
    }

    private function createActiveOtbPolicy(string $code = 'OTB-RETAIL'): string
    {
        $response = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/otb-policies', [
            'legal_entity_id' => $this->legalEntityId,
            'code' => $code,
            'budget_basis' => 'RETAIL',
            'valuation_basis' => 'RETAIL',
            'effective_from' => '2027-01-01',
            'effective_to' => '2027-12-31',
            'inclusion_rules' => [
                'commitments_mode' => 'ADDITIONAL',
                'actual_receipts_reduce_otb' => true,
            ],
            'approval_thresholds' => ['manager_review_at' => '100000.0000'],
        ])->assertCreated();

        $policyId = $response->json('data.id');

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/otb-policies/'.$policyId.'/activate', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'ACTIVE');

        $this->actAsOwner();

        return $policyId;
    }

    private function createPublishedPriceList(string $code = 'OWN-RETAIL-2027'): string
    {
        $list = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/price-lists', [
            'legal_entity_id' => $this->legalEntityId,
            'code' => $code,
            'channel_code' => 'OWN_RETAIL',
            'market_scope' => 'ADDIS_ABABA',
            'currency' => 'ETB',
            'effective_from' => '2027-01-01T00:00:00Z',
            'effective_to' => '2027-12-31T23:59:59Z',
        ])->assertCreated();

        $priceListId = $list->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/price-lists/'.$priceListId.'/lines', [
                'product_reference' => 'STYLE-001',
                'list_price' => '1500.0000',
                'wholesale_price' => '1000.0000',
                'price_floor' => '900.0000',
                'cost_basis' => '700.0000',
            ])
            ->assertCreated();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/price-lists/'.$priceListId.'/publish', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'PUBLISHED');

        $this->actAsOwner();

        return $priceListId;
    }

    public function test_forecast_runs_and_adjustments_are_append_only_and_independently_approved(): void
    {
        $runId = $this->createForecast();

        $run = DB::connection('pgsql_test_admin')->table('demand_forecast_runs')->where('id', $runId)->first();
        $forecast = DB::connection('pgsql_test_admin')->table('demand_forecasts')
            ->where('tenant_id', $this->tenantId)
            ->where('forecast_run_id', $runId)
            ->first();

        $this->assertSame('PUBLISHED', $run->status);
        $this->assertSame('100.0000', $forecast->forecast_quantity);

        $adjustment = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/forecasts/'.$forecast->id.'/adjustments', [
            'baseline_value' => '100.0000',
            'override_value' => '125.0000',
            'reason' => 'Store observation supports a higher expected weekly sell-through.',
            'evidence_reference' => 'INSIGHT-STORE-001',
        ])->assertCreated()->assertJsonPath('approval_status', 'PENDING');

        $adjustmentId = $adjustment->json('data.id');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/forecast-adjustments/'.$adjustmentId.'/approve', [])
            ->assertForbidden();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/forecast-adjustments/'.$adjustmentId.'/approve', [])
            ->assertOk()
            ->assertJsonPath('approval_status', 'APPROVED');

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/forecast-adjustments/'.$adjustmentId.'/approve', [])
            ->assertUnprocessable();

        $this->actAsOwner();

        $unchanged = DB::connection('pgsql_test_admin')->table('demand_forecasts')->where('id', $forecast->id)->first();
        $this->assertSame('100.0000', $unchanged->forecast_quantity);

        $detail = $this->withHeaders($this->tenantHeader())
            ->getJson('/api/v1/product/forecast-runs/'.$runId)
            ->assertOk()
            ->assertJsonPath('adjustments.0.is_approved', true);

        $this->assertSame('PUBLISHED', $detail->json('data.status'));

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/forecast-runs', [
            'grain' => 'STYLE_WEEK',
            'method' => 'MANUAL_CONSENSUS',
            'horizon' => '1_WEEK',
            'scenario_code' => 'INVALID',
            'forecast_start' => '2027-01-04',
            'forecast_end' => '2027-01-10',
            'assumptions' => [],
            'forecasts' => [
                [
                    'dimension_refs' => ['style' => 'STYLE-002'],
                    'time_bucket' => '2027-01-04',
                    'forecast_quantity' => 10,
                    'confidence_low' => 20,
                    'confidence_high' => 15,
                ],
            ],
        ])->assertUnprocessable();
    }

    public function test_otb_policy_period_and_scenario_use_exact_versioned_policy_formula(): void
    {
        $policyId = $this->createActiveOtbPolicy();

        $period = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/otb-periods', [
            'otb_policy_id' => $policyId,
            'period_start' => '2027-01-01',
            'period_end' => '2027-01-31',
            'currency' => 'ETB',
            'beginning_inventory' => '500.0000',
            'planned_sales' => '800.0000',
            'planned_markdowns' => '50.0000',
            'planned_ending_inventory' => '600.0000',
            'on_order_purchases' => '200.0000',
            'commitments' => '75.0000',
            'actual_receipts' => '25.0000',
        ])->assertCreated();

        $periodId = $period->json('data.id');
        $this->assertSame('650.0000', $period->json('data.available_budget'));
        $this->assertSame('BALANCED_OTB_V1', $period->json('formula_version'));

        $scenario = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/otb-periods/'.$periodId.'/scenarios', [
            'assumptions' => ['planned_sales' => '900.0000'],
        ])->assertCreated();

        $scenarioId = $scenario->json('data.id');
        $result = json_decode($scenario->json('data.result'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('750.0000', $result['calculation']['available_budget']);
        $this->assertStringContainsString('not a purchase authorization', $result['warning']);

        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/otb-scenarios/'.$scenarioId.'/approve', [])
            ->assertForbidden();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/otb-scenarios/'.$scenarioId.'/approve', [])
            ->assertOk()
            ->assertJsonPath('status', 'APPROVED_FOR_PLANNING_ONLY');

        $this->assertDatabaseHas('merchandising_decisions', [
            'tenant_id' => $this->tenantId,
            'object_type' => 'OTB_SCENARIO',
            'object_id' => $scenarioId,
            'decision' => 'APPROVE_SCENARIO',
            'decided_by' => $this->approverId,
        ], 'pgsql_test_admin');

        $this->assertSame(0, DB::connection('pgsql_test_admin')->table('otb_scenarios')->where('id', '00000000-0000-0000-0000-000000000000')->count());
    }

    public function test_otb_policy_rejects_valuation_basis_mismatch_and_overlapping_effective_policy(): void
    {
        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/otb-policies', [
            'legal_entity_id' => $this->legalEntityId,
            'code' => 'OTB-MISMATCH',
            'budget_basis' => 'RETAIL',
            'valuation_basis' => 'COST',
            'effective_from' => '2027-01-01',
            'inclusion_rules' => ['commitments_mode' => 'ADDITIONAL', 'actual_receipts_reduce_otb' => true],
            'approval_thresholds' => ['manager_review_at' => '100000.0000'],
        ])->assertUnprocessable();

        $this->createActiveOtbPolicy('OTB-ONE');

        $second = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/otb-policies', [
            'legal_entity_id' => $this->legalEntityId,
            'code' => 'OTB-TWO',
            'budget_basis' => 'RETAIL',
            'valuation_basis' => 'RETAIL',
            'effective_from' => '2027-06-01',
            'effective_to' => '2028-06-30',
            'inclusion_rules' => ['commitments_mode' => 'ADDITIONAL', 'actual_receipts_reduce_otb' => true],
            'approval_thresholds' => ['manager_review_at' => '100000.0000'],
        ])->assertCreated();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())
            ->postJson('/api/v1/product/otb-policies/'.$second->json('data.id').'/activate', [])
            ->assertUnprocessable();
    }

    public function test_price_list_and_promotion_require_independent_approval_and_explicit_eligibility(): void
    {
        $priceListId = $this->createPublishedPriceList();

        $promotion = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/promotions', [
            'price_list_id' => $priceListId,
            'code' => 'SUMMER-10',
            'promotion_type' => 'PERCENTAGE',
            'discount_value' => '10.0000',
            'stack_policy' => 'NO_STACK',
            'starts_at' => '2027-06-01T00:00:00Z',
            'ends_at' => '2027-06-30T23:59:59Z',
            'margin_floor' => '15.0000',
            'budget_amount' => '50000.0000',
            'funded_by' => 'BRAND',
        ])->assertCreated();

        $promotionId = $promotion->json('data.id');

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/promotions/'.$promotionId.'/approve', [])
            ->assertForbidden();

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/promotions/'.$promotionId.'/eligibility', [
            'scope_type' => 'CHANNEL',
            'scope_reference' => 'OWN_RETAIL',
            'is_exclusion' => false,
        ])->assertCreated();

        $this->actAsApprover();
        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/promotions/'.$promotionId.'/approve', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'APPROVED');

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/promotions/'.$promotionId.'/approve', [])
            ->assertUnprocessable();

        $this->assertDatabaseHas('merchandising_decisions', [
            'tenant_id' => $this->tenantId,
            'object_type' => 'PROMOTION',
            'object_id' => $promotionId,
            'decision' => 'APPROVE_PROMOTION',
            'decided_by' => $this->approverId,
        ], 'pgsql_test_admin');
    }

    public function test_price_list_and_promotion_validation_prevents_unsafe_or_ambiguous_values(): void
    {
        $list = $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/price-lists', [
            'legal_entity_id' => $this->legalEntityId,
            'code' => 'BAD-PRICE',
            'channel_code' => 'OWN_RETAIL',
            'market_scope' => 'ADDIS_ABABA',
            'currency' => 'ETB',
            'effective_from' => '2027-01-01T00:00:00Z',
            'effective_to' => '2027-12-31T23:59:59Z',
        ])->assertCreated();

        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/price-lists/'.$list->json('data.id').'/lines', [
            'product_reference' => 'STYLE-BAD',
            'list_price' => '100.0000',
            'price_floor' => '120.0000',
        ])->assertUnprocessable();

        $priceListId = $this->createPublishedPriceList('PROMO-CHECK');
        $this->withHeaders($this->tenantHeader())->postJson('/api/v1/product/promotions', [
            'price_list_id' => $priceListId,
            'code' => 'OVER-100',
            'promotion_type' => 'PERCENTAGE',
            'discount_value' => '100.0001',
            'stack_policy' => 'NO_STACK',
            'starts_at' => '2027-06-01T00:00:00Z',
            'ends_at' => '2027-06-30T23:59:59Z',
        ])->assertUnprocessable();
    }
}
