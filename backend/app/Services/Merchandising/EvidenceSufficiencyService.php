<?php

namespace App\Services\Merchandising;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EvidenceSufficiencyService
{
    public function evaluate(string $tenantId, object $initiative, string $stage): array
    {
        if (! in_array($stage, ['RESEARCH', 'ASSESSMENT'], true)) {
            throw new InvalidArgumentException('Unsupported initiative evidence stage.');
        }

        $policy = $this->resolvePolicy($tenantId, $initiative, $stage);

        if ($policy === null) {
            return [
                'status' => 'NOT_CONFIGURED',
                'coverage_percent' => null,
                'mandatory_rules' => 0,
                'mandatory_rules_met' => 0,
                'policy' => null,
                'rules' => [],
                'message' => 'No approved, effective evidence policy matches this initiative type, stage and market.',
            ];
        }

        $rules = DB::table('initiative_evidence_policy_rules')
            ->where('tenant_id', $tenantId)
            ->where('policy_id', $policy->id)
            ->orderBy('requirement_code')
            ->get();

        $results = $rules->map(function (object $rule) use ($tenantId, $initiative): array {
            $base = DB::table('market_insights as mi')
                ->join('insight_sources as src', function ($join): void {
                    $join->on('src.tenant_id', '=', 'mi.tenant_id')
                        ->on('src.id', '=', 'mi.source_id');
                })
                ->where('mi.tenant_id', $tenantId)
                ->where('mi.initiative_id', $initiative->id)
                ->where('mi.insight_type', $rule->insight_type);

            $observedCount = (clone $base)->count();
            $latestObservedAt = (clone $base)->max('mi.observed_at');
            $qualifying = clone $base;

            if ($rule->max_age_days !== null) {
                $qualifying->where('mi.observed_at', '>=', now()->subDays((int) $rule->max_age_days));
            }

            if ($rule->minimum_confidence !== null) {
                $qualifying->whereNotNull('mi.confidence')
                    ->where('mi.confidence', '>=', $rule->minimum_confidence);
            }

            if ($rule->minimum_source_reliability !== null) {
                $qualifying->whereNotNull('src.reliability_score')
                    ->where('src.reliability_score', '>=', $rule->minimum_source_reliability);
            }

            if ($rule->require_geography) {
                $qualifying->whereNotNull('mi.geography')
                    ->where('mi.geography', '<>', '');
            }

            if ($rule->require_target_segment) {
                $qualifying->whereNotNull('mi.target_segment')
                    ->where('mi.target_segment', '<>', '');
            }

            if ($rule->require_sample_denominator) {
                $qualifying->whereNotNull('mi.sample_size')
                    ->whereNotNull('mi.denominator')
                    ->where('mi.sample_size', '>', 0)
                    ->where('mi.denominator', '>', 0);
            }

            $qualifyingCount = (clone $qualifying)->count();
            $distinctSources = (clone $qualifying)->distinct()->count('mi.source_id');
            $gaps = [];

            if ($observedCount === 0) {
                $gaps[] = 'NO_MATCHING_INSIGHTS';
            }

            if ($qualifyingCount < (int) $rule->minimum_records) {
                $gaps[] = 'MINIMUM_QUALIFYING_RECORDS_NOT_MET';
            }

            if ($distinctSources < (int) $rule->minimum_distinct_sources) {
                $gaps[] = 'MINIMUM_DISTINCT_SOURCES_NOT_MET';
            }

            if ($rule->max_age_days !== null && $qualifyingCount < $observedCount) {
                $freshCount = (clone $base)
                    ->where('mi.observed_at', '>=', now()->subDays((int) $rule->max_age_days))
                    ->count();
                if ($freshCount < $observedCount) {
                    $gaps[] = 'STALE_EVIDENCE_PRESENT';
                }
            }

            if ($rule->minimum_confidence !== null) {
                $confidenceCount = (clone $base)
                    ->whereNotNull('mi.confidence')
                    ->where('mi.confidence', '>=', $rule->minimum_confidence)
                    ->count();
                if ($confidenceCount < $observedCount) {
                    $gaps[] = 'CONFIDENCE_THRESHOLD_NOT_MET';
                }
            }

            if ($rule->minimum_source_reliability !== null) {
                $reliableCount = (clone $base)
                    ->whereNotNull('src.reliability_score')
                    ->where('src.reliability_score', '>=', $rule->minimum_source_reliability)
                    ->count();
                if ($reliableCount < $observedCount) {
                    $gaps[] = 'SOURCE_RELIABILITY_THRESHOLD_NOT_MET';
                }
            }

            if ($rule->require_geography) {
                $geographyCount = (clone $base)
                    ->whereNotNull('mi.geography')
                    ->where('mi.geography', '<>', '')
                    ->count();
                if ($geographyCount < $observedCount) {
                    $gaps[] = 'GEOGRAPHY_MISSING';
                }
            }

            if ($rule->require_target_segment) {
                $segmentCount = (clone $base)
                    ->whereNotNull('mi.target_segment')
                    ->where('mi.target_segment', '<>', '')
                    ->count();
                if ($segmentCount < $observedCount) {
                    $gaps[] = 'TARGET_SEGMENT_MISSING';
                }
            }

            if ($rule->require_sample_denominator) {
                $sampleCount = (clone $base)
                    ->whereNotNull('mi.sample_size')
                    ->whereNotNull('mi.denominator')
                    ->where('mi.sample_size', '>', 0)
                    ->where('mi.denominator', '>', 0)
                    ->count();
                if ($sampleCount < $observedCount) {
                    $gaps[] = 'SAMPLE_DENOMINATOR_MISSING';
                }
            }

            $gaps = array_values(array_unique($gaps));
            $met = $qualifyingCount >= (int) $rule->minimum_records
                && $distinctSources >= (int) $rule->minimum_distinct_sources;

            return [
                'requirement_code' => $rule->requirement_code,
                'insight_type' => $rule->insight_type,
                'mandatory' => (bool) $rule->is_mandatory,
                'status' => $met ? 'MET' : 'GAP',
                'observed_records' => $observedCount,
                'qualifying_records' => $qualifyingCount,
                'distinct_sources' => $distinctSources,
                'minimum_records' => (int) $rule->minimum_records,
                'minimum_distinct_sources' => (int) $rule->minimum_distinct_sources,
                'minimum_confidence' => $rule->minimum_confidence === null ? null : (float) $rule->minimum_confidence,
                'minimum_source_reliability' => $rule->minimum_source_reliability === null ? null : (float) $rule->minimum_source_reliability,
                'max_age_days' => $rule->max_age_days === null ? null : (int) $rule->max_age_days,
                'required_dimensions' => [
                    'geography' => (bool) $rule->require_geography,
                    'target_segment' => (bool) $rule->require_target_segment,
                    'sample_and_denominator' => (bool) $rule->require_sample_denominator,
                ],
                'latest_observed_at' => $latestObservedAt,
                'gaps' => $gaps,
            ];
        })->values()->all();

        $mandatory = array_values(array_filter($results, fn (array $rule): bool => $rule['mandatory']));
        $mandatoryCount = count($mandatory);
        $mandatoryMet = count(array_filter($mandatory, fn (array $rule): bool => $rule['status'] === 'MET'));
        $coveragePercent = $mandatoryCount > 0
            ? round(($mandatoryMet / $mandatoryCount) * 100, 2)
            : null;

        return [
            'status' => $mandatoryCount > 0 && $mandatoryMet === $mandatoryCount ? 'MET' : 'GAP',
            'coverage_percent' => $coveragePercent,
            'mandatory_rules' => $mandatoryCount,
            'mandatory_rules_met' => $mandatoryMet,
            'policy' => [
                'id' => $policy->id,
                'code' => $policy->code,
                'name' => $policy->name,
                'version_no' => (int) $policy->version_no,
                'initiative_type' => $policy->initiative_type,
                'stage' => $policy->stage,
                'target_market' => $policy->target_market,
                'effective_from' => $policy->effective_from,
                'effective_to' => $policy->effective_to,
            ],
            'rules' => $results,
            'message' => $mandatoryMet === $mandatoryCount
                ? 'All mandatory evidence requirements are satisfied.'
                : 'One or more mandatory evidence requirements are not satisfied.',
        ];
    }

    private function resolvePolicy(string $tenantId, object $initiative, string $stage): ?object
    {
        $today = now()->toDateString();

        $query = DB::table('initiative_evidence_policies')
            ->where('tenant_id', $tenantId)
            ->where('initiative_type', $initiative->initiative_type)
            ->where('stage', $stage)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $today)
            ->where(function ($query) use ($today): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today);
            });

        $market = trim((string) $initiative->target_market);
        if ($market !== '') {
            $specific = (clone $query)
                ->where('target_market', $market)
                ->orderByDesc('version_no')
                ->first();

            if ($specific !== null) {
                return $specific;
            }
        }

        return $query->whereNull('target_market')
            ->orderByDesc('version_no')
            ->first();
    }
}
