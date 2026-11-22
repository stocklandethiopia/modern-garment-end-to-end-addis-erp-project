<?php

namespace App\Services\Merchandising;

use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MerchandisingDashboardService
{
    public function dashboard(TenantContext $tenantContext): array
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) request()->user()->getAuthIdentifier();
        $isApprover = $tenantContext->canAdministerTenant();

        $initiativeStatuses = DB::table('initiatives')
            ->where('tenant_id', $tenantId)
            ->select('status', DB::raw('COUNT(*) AS record_count'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn (object $row): array => [
                'status' => $row->status,
                'count' => (int) $row->record_count,
            ])
            ->values();

        $latestAssessment = DB::table('initiative_decisions')
            ->where('tenant_id', $tenantId)
            ->where('stage', 'ASSESSMENT')
            ->select('initiative_id', DB::raw('MAX(decided_at) AS latest_decided_at'))
            ->groupBy('initiative_id');

        $assessmentOutcomes = DB::table('initiative_decisions AS d')
            ->joinSub($latestAssessment, 'latest', function ($join): void {
                $join->on('latest.initiative_id', '=', 'd.initiative_id')
                    ->on('latest.latest_decided_at', '=', 'd.decided_at');
            })
            ->where('d.tenant_id', $tenantId)
            ->where('d.stage', 'ASSESSMENT')
            ->whereIn('d.decision', ['APPROVE', 'APPROVE_CONDITIONAL', 'PARK', 'REJECT'])
            ->get(['d.initiative_id', 'd.decision'])
            ->unique('initiative_id')
            ->values();

        $assessedCount = $assessmentOutcomes->count();
        $approvedCount = $assessmentOutcomes
            ->whereIn('decision', ['APPROVE', 'APPROVE_CONDITIONAL'])
            ->count();

        $frozenLineQuery = DB::table('range_lines AS rl')
            ->join('range_plans AS rp', function ($join): void {
                $join->on('rp.tenant_id', '=', 'rl.tenant_id')
                    ->on('rp.id', '=', 'rl.range_plan_id');
            })
            ->where('rl.tenant_id', $tenantId)
            ->where('rp.status', 'FROZEN');

        $frozenLineCount = (clone $frozenLineQuery)->count();
        $linesWithCurrentAcceptedBrief = (clone $frozenLineQuery)
            ->join('product_briefs AS pb', function ($join): void {
                $join->on('pb.tenant_id', '=', 'rl.tenant_id')
                    ->on('pb.range_line_id', '=', 'rl.id');
            })
            ->where('pb.status', 'ACCEPTED')
            ->whereColumn('pb.accepted_version', 'pb.current_version')
            ->count();

        $rangeReadiness = $frozenLineCount > 0
            ? round(($linesWithCurrentAcceptedBrief / $frozenLineCount) * 100, 2)
            : null;

        $lifecycleMix = (clone $frozenLineQuery)
            ->select(
                'rl.lifecycle_type',
                DB::raw('COUNT(*) AS line_count'),
                DB::raw('SUM(rl.planned_units) AS planned_units'),
            )
            ->groupBy('rl.lifecycle_type')
            ->orderBy('rl.lifecycle_type')
            ->get()
            ->map(fn (object $row): array => [
                'lifecycle_type' => $row->lifecycle_type,
                'range_line_count' => (int) $row->line_count,
                'planned_units' => (int) $row->planned_units,
            ])
            ->values();

        $categoryDepth = (clone $frozenLineQuery)
            ->select(
                'rl.category_code',
                'rl.target_tier',
                DB::raw('COUNT(*) AS range_line_count'),
                DB::raw('SUM(rl.planned_units) AS planned_units'),
            )
            ->groupBy('rl.category_code', 'rl.target_tier')
            ->orderBy('rl.category_code')
            ->orderBy('rl.target_tier')
            ->get()
            ->map(fn (object $row): array => [
                'category_code' => $row->category_code,
                'target_tier' => $row->target_tier,
                'range_line_count' => (int) $row->range_line_count,
                'planned_units' => (int) $row->planned_units,
            ])
            ->values();

        $sourceCount = DB::table('insight_sources')
            ->where('tenant_id', $tenantId)
            ->count();
        $insightCount = DB::table('market_insights')
            ->where('tenant_id', $tenantId)
            ->count();

        $kpis = [
            [
                'key' => 'initiative_assessment_conversion',
                'label' => 'Initiative assessment conversion',
                'status' => $assessedCount > 0 ? 'AVAILABLE' : 'NO_DATA',
                'value' => $assessedCount > 0 ? round(($approvedCount / $assessedCount) * 100, 2) : null,
                'unit' => 'percent',
                'numerator' => $approvedCount,
                'denominator' => $assessedCount,
                'definition' => 'Distinct initiatives whose latest assessment-stage decision is APPROVE or APPROVE_CONDITIONAL divided by distinct initiatives whose latest assessment-stage decision is APPROVE, APPROVE_CONDITIONAL, PARK or REJECT.',
                'sources' => ['initiative_decisions'],
            ],
            [
                'key' => 'range_brief_readiness',
                'label' => 'Frozen range lines with current accepted briefs',
                'status' => $frozenLineCount > 0 ? 'AVAILABLE' : 'NO_DATA',
                'value' => $rangeReadiness,
                'unit' => 'percent',
                'numerator' => $linesWithCurrentAcceptedBrief,
                'denominator' => $frozenLineCount,
                'definition' => 'Frozen range lines whose product brief is ACCEPTED and whose accepted version equals its current version, divided by all lines on frozen range plans.',
                'sources' => ['range_plans', 'range_lines', 'product_briefs'],
            ],
            [
                'key' => 'frozen_range_line_depth',
                'label' => 'Planned units on frozen range lines',
                'status' => $frozenLineCount > 0 ? 'AVAILABLE' : 'NO_DATA',
                'value' => (int) $lifecycleMix->sum('planned_units'),
                'unit' => 'planned_units',
                'numerator' => null,
                'denominator' => $frozenLineCount,
                'definition' => 'Sum of the planned unit quantities recorded on frozen range lines; this is planned depth, not demand or sales actuals.',
                'sources' => ['range_plans', 'range_lines'],
            ],
            [
                'key' => 'market_evidence_volume',
                'label' => 'Recorded sourced market evidence',
                'status' => 'AVAILABLE',
                'value' => $insightCount,
                'unit' => 'records',
                'numerator' => $insightCount,
                'denominator' => null,
                'definition' => 'Count of tenant-scoped market insight records with source references; this is evidence volume and is not an evidence-sufficiency rate.',
                'sources' => ['insight_sources', 'market_insights'],
            ],
        ];

        $approvalQueue = $isApprover
            ? $this->approvalQueue($tenantId, $actorId)
            : [];

        return [
            'data' => [
                'generated_at' => now()->toIso8601String(),
                'tenant_role' => $tenantContext->membershipRole(),
                'initiative_funnel' => $initiativeStatuses,
                'kpis' => $kpis,
                'range_lifecycle_mix' => $lifecycleMix,
                'range_category_depth' => $categoryDepth,
                'evidence_register' => [
                    'source_count' => $sourceCount,
                    'insight_count' => $insightCount,
                    'sufficiency_status' => 'UNAVAILABLE',
                    'sufficiency_reason' => 'The approved requirements catalogue does not yet store per-initiative evidence requirements, so a valid sufficiency denominator cannot be calculated.',
                ],
                'work_queue' => [
                    'approval_scope' => $isApprover ? 'TENANT_APPROVALS' : 'NOT_AUTHORIZED_FOR_TENANT_APPROVALS',
                    'approvals' => $approvalQueue,
                    'my_work' => $this->myWorkQueue($tenantId, $actorId),
                ],
                'unavailable_kpis' => $this->unavailableKpis(),
            ],
        ];
    }

    private function approvalQueue(string $tenantId, string $actorId): array
    {
        $items = [];

        $initiatives = DB::table('initiatives')
            ->where('tenant_id', $tenantId)
            ->where('owner_user_id', '<>', $actorId)
            ->whereIn('status', ['SCREENING', 'ASSESSMENT'])
            ->orderBy('created_at')
            ->limit(25)
            ->get(['id', 'code', 'title', 'status', 'owner_user_id', 'created_at']);

        foreach ($initiatives as $initiative) {
            $items[] = [
                'type' => 'INITIATIVE_DECISION',
                'id' => $initiative->id,
                'code' => $initiative->code,
                'title' => $initiative->title,
                'stage' => $initiative->status,
                'created_at' => $initiative->created_at,
                'href' => '/product/initiatives',
            ];
        }

        $brandVersions = DB::table('brand_strategy_versions AS v')
            ->join('brands AS b', function ($join): void {
                $join->on('b.tenant_id', '=', 'v.tenant_id')
                    ->on('b.id', '=', 'v.brand_id');
            })
            ->where('v.tenant_id', $tenantId)
            ->where('v.status', 'PENDING_APPROVAL')
            ->where('v.created_by', '<>', $actorId)
            ->whereNotExists(function ($query) use ($tenantId): void {
                $query->select(DB::raw('1'))
                    ->from('merchandising_decisions AS md')
                    ->where('md.tenant_id', $tenantId)
                    ->whereColumn('md.object_id', 'v.id')
                    ->where('md.object_type', 'BRAND_STRATEGY')
                    ->where('md.decision', 'APPROVE');
            })
            ->orderBy('v.created_at')
            ->limit(25)
            ->get(['v.id', 'v.version_no', 'v.created_at', 'b.code', 'b.name']);

        foreach ($brandVersions as $version) {
            $items[] = [
                'type' => 'BRAND_STRATEGY_APPROVAL',
                'id' => $version->id,
                'code' => $version->code,
                'title' => $version->name.' · strategy v'.$version->version_no,
                'stage' => 'PENDING_APPROVAL',
                'created_at' => $version->created_at,
                'href' => '/product/brand-range',
            ];
        }

        $collectionVersions = DB::table('collection_versions AS v')
            ->join('collections AS c', function ($join): void {
                $join->on('c.tenant_id', '=', 'v.tenant_id')
                    ->on('c.id', '=', 'v.collection_id');
            })
            ->where('v.tenant_id', $tenantId)
            ->where('v.status', 'PENDING_APPROVAL')
            ->where('v.created_by', '<>', $actorId)
            ->whereNotExists(function ($query) use ($tenantId): void {
                $query->select(DB::raw('1'))
                    ->from('merchandising_decisions AS md')
                    ->where('md.tenant_id', $tenantId)
                    ->whereColumn('md.object_id', 'v.id')
                    ->where('md.object_type', 'COLLECTION_VERSION')
                    ->where('md.decision', 'APPROVE');
            })
            ->orderBy('v.created_at')
            ->limit(25)
            ->get(['v.id', 'v.version_no', 'v.created_at', 'c.code', 'c.name']);

        foreach ($collectionVersions as $version) {
            $items[] = [
                'type' => 'COLLECTION_VERSION_APPROVAL',
                'id' => $version->id,
                'code' => $version->code,
                'title' => $version->name.' · v'.$version->version_no,
                'stage' => 'PENDING_APPROVAL',
                'created_at' => $version->created_at,
                'href' => '/product/brand-range',
            ];
        }

        $promotions = DB::table('promotions AS p')
            ->join('price_lists AS pl', function ($join): void {
                $join->on('pl.tenant_id', '=', 'p.tenant_id')
                    ->on('pl.id', '=', 'p.price_list_id');
            })
            ->where('p.tenant_id', $tenantId)
            ->where('p.status', 'DRAFT')
            ->where('p.created_by', '<>', $actorId)
            ->where('pl.status', 'PUBLISHED')
            ->orderBy('p.created_at')
            ->limit(25)
            ->get(['p.id', 'p.code', 'p.created_at']);

        foreach ($promotions as $promotion) {
            $items[] = [
                'type' => 'PROMOTION_APPROVAL',
                'id' => $promotion->id,
                'code' => $promotion->code,
                'title' => $promotion->code,
                'stage' => 'PENDING_APPROVAL',
                'created_at' => $promotion->created_at,
                'href' => '/product/planning-workbench',
            ];
        }

        $forecastAdjustments = DB::table('forecast_adjustments AS fa')
            ->where('fa.tenant_id', $tenantId)
            ->where('fa.authored_by', '<>', $actorId)
            ->whereNotExists(function ($query) use ($tenantId): void {
                $query->select(DB::raw('1'))
                    ->from('merchandising_decisions AS md')
                    ->where('md.tenant_id', $tenantId)
                    ->whereColumn('md.object_id', 'fa.id')
                    ->where('md.object_type', 'FORECAST_ADJUSTMENT')
                    ->where('md.decision', 'APPROVE_ADJUSTMENT');
            })
            ->orderBy('fa.created_at')
            ->limit(25)
            ->get(['fa.id', 'fa.created_at']);

        foreach ($forecastAdjustments as $adjustment) {
            $items[] = [
                'type' => 'FORECAST_ADJUSTMENT_APPROVAL',
                'id' => $adjustment->id,
                'code' => Str::limit($adjustment->id, 12, ''),
                'title' => 'Forecast adjustment requires review',
                'stage' => 'PENDING_APPROVAL',
                'created_at' => $adjustment->created_at,
                'href' => '/product/planning-workbench',
            ];
        }

        $scenarios = DB::table('otb_scenarios AS s')
            ->where('s.tenant_id', $tenantId)
            ->where('s.created_by', '<>', $actorId)
            ->where('s.status', 'SIMULATED')
            ->whereNotExists(function ($query) use ($tenantId): void {
                $query->select(DB::raw('1'))
                    ->from('merchandising_decisions AS md')
                    ->where('md.tenant_id', $tenantId)
                    ->whereColumn('md.object_id', 's.id')
                    ->where('md.object_type', 'OTB_SCENARIO')
                    ->where('md.decision', 'APPROVE_SCENARIO');
            })
            ->orderBy('s.created_at')
            ->limit(25)
            ->get(['s.id', 's.created_at']);

        foreach ($scenarios as $scenario) {
            $items[] = [
                'type' => 'OTB_SCENARIO_APPROVAL',
                'id' => $scenario->id,
                'code' => Str::limit($scenario->id, 12, ''),
                'title' => 'Open-to-buy scenario requires review',
                'stage' => 'PENDING_APPROVAL',
                'created_at' => $scenario->created_at,
                'href' => '/product/planning-workbench',
            ];
        }

        $briefs = DB::table('product_briefs')
            ->where('tenant_id', $tenantId)
            ->where('status', 'SUBMITTED_FOR_REVIEW')
            ->where('owner_user_id', '<>', $actorId)
            ->orderBy('updated_at')
            ->limit(25)
            ->get(['id', 'initiative_id', 'status', 'updated_at']);

        foreach ($briefs as $brief) {
            $items[] = [
                'type' => 'PRODUCT_BRIEF_APPROVAL',
                'id' => $brief->id,
                'code' => Str::limit($brief->id, 12, ''),
                'title' => 'Product brief requires independent decision',
                'stage' => $brief->status,
                'created_at' => $brief->updated_at,
                'href' => '/product/product-development',
            ];
        }

        usort($items, fn (array $left, array $right): int => strcmp(
            (string) $left['created_at'],
            (string) $right['created_at'],
        ));

        return array_slice($items, 0, 100);
    }

    private function myWorkQueue(string $tenantId, string $actorId): array
    {
        $items = [];

        $initiatives = DB::table('initiatives')
            ->where('tenant_id', $tenantId)
            ->where('owner_user_id', $actorId)
            ->whereIn('status', ['DRAFT', 'RESEARCH', 'ASSESSMENT', 'CONDITIONALLY_APPROVED'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'code', 'title', 'status', 'updated_at']);

        foreach ($initiatives as $initiative) {
            $items[] = [
                'type' => 'MY_INITIATIVE',
                'id' => $initiative->id,
                'code' => $initiative->code,
                'title' => $initiative->title,
                'stage' => $initiative->status,
                'created_at' => $initiative->updated_at,
                'href' => '/product/initiatives',
            ];
        }

        $briefs = DB::table('product_briefs')
            ->where('tenant_id', $tenantId)
            ->where('owner_user_id', $actorId)
            ->whereIn('status', ['DRAFT', 'SUBMITTED_FOR_REVIEW', 'REJECTED'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'status', 'updated_at']);

        foreach ($briefs as $brief) {
            $items[] = [
                'type' => 'MY_PRODUCT_BRIEF',
                'id' => $brief->id,
                'code' => Str::limit($brief->id, 12, ''),
                'title' => 'Product brief · '.$brief->status,
                'stage' => $brief->status,
                'created_at' => $brief->updated_at,
                'href' => '/product/product-development',
            ];
        }

        $rangePlans = DB::table('range_plans')
            ->where('tenant_id', $tenantId)
            ->where('owner_user_id', $actorId)
            ->where('status', 'DRAFT')
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'code', 'status', 'updated_at']);

        foreach ($rangePlans as $rangePlan) {
            $items[] = [
                'type' => 'MY_RANGE_PLAN',
                'id' => $rangePlan->id,
                'code' => $rangePlan->code,
                'title' => 'Range plan · '.$rangePlan->code,
                'stage' => $rangePlan->status,
                'created_at' => $rangePlan->updated_at,
                'href' => '/product/brand-range',
            ];
        }

        return $items;
    }

    private function unavailableKpis(): array
    {
        return [
            [
                'key' => 'evidence_sufficiency',
                'reason' => 'No approved evidence-requirement catalogue exists to define a denominator by initiative type, stage or market.',
                'dependency' => 'WP-04 requirements model',
            ],
            [
                'key' => 'forecast_bias_and_wape',
                'reason' => 'The current slice stores forecast baselines and overrides but has no authoritative actual sales series or stockout treatment.',
                'dependency' => 'WP-12 commerce actuals',
            ],
            [
                'key' => 'sell_through',
                'reason' => 'Units sold and units available for sale are not yet sourced from posted sales and inventory availability.',
                'dependency' => 'WP-12 commerce and inventory actuals',
            ],
            [
                'key' => 'gross_and_contribution_margin',
                'reason' => 'Target margins and price-list cost fields are planning inputs, not posted cost and revenue actuals.',
                'dependency' => 'WP-13 finance and cost ledger',
            ],
            [
                'key' => 'otb_utilization',
                'reason' => 'The current OTB data is a planning simulation; authorized purchase commitments and finance postings are not yet authoritative source records.',
                'dependency' => 'WP-07 procurement and WP-13 finance',
            ],
            [
                'key' => 'markdown_exposure',
                'reason' => 'No posted markdown history or inventory-age ledger is available in this slice.',
                'dependency' => 'WP-12 commerce and inventory actuals',
            ],
            [
                'key' => 'returns_and_fit_feedback',
                'reason' => 'No authoritative return transactions or approved sample-fit outcomes are available in this slice.',
                'dependency' => 'WP-05 sample/fit and WP-12 returns',
            ],
            [
                'key' => 'range_to_capacity_fit',
                'reason' => 'No validated factory capacity, material availability or operation-routing feasibility is available in this slice.',
                'dependency' => 'WP-06 production planning and WP-07 sourcing',
            ],
        ];
    }
}
