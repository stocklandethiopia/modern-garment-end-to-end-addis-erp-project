<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Services\Merchandising\EvidencePolicyService;
use App\Services\Merchandising\EvidenceSufficiencyService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvidencePolicyController extends Controller
{
    private const INITIATIVE_TYPES = [
        'NEW_BRAND',
        'NEW_COLLECTION',
        'CATEGORY_EXPANSION',
        'MARKET_ENTRY',
        'PRODUCT_INNOVATION',
        'CUSTOMER_OPPORTUNITY',
        'CAPABILITY_LED',
        'REPLENISHMENT',
        'SUSTAINABILITY_IMPROVEMENT',
        'QUALITY_IMPROVEMENT',
    ];

    private const INSIGHT_TYPES = [
        'OBSERVED_FACT',
        'SURVEY_RESULT',
        'QUALITATIVE_EVIDENCE',
        'EXTERNAL_BENCHMARK',
        'HYPOTHESIS',
        'FORECAST_SIGNAL',
        'CUSTOMER_REQUEST',
        'FIT_OR_QUALITY_FEEDBACK',
        'SUPPLY_OR_CAPACITY_SIGNAL',
    ];

    public function index(TenantContext $tenantContext, EvidencePolicyService $service): JsonResponse
    {
        return response()->json(['data' => $service->index($tenantContext->tenantId())]);
    }

    public function store(Request $request, TenantContext $tenantContext, EvidencePolicyService $service): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Only a tenant owner or administrator may configure evidence policies.');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9][A-Z0-9._-]+$/'],
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'initiative_type' => ['required', Rule::in(self::INITIATIVE_TYPES)],
            'stage' => ['required', Rule::in(['RESEARCH', 'ASSESSMENT'])],
            'target_market' => ['nullable', 'string', 'min:2', 'max:160'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['required', 'string', 'min:10', 'max:5000'],
            'rules' => ['required', 'array', 'min:1', 'max:20'],
            'rules.*.requirement_code' => ['required', 'string', 'max:80', 'distinct'],
            'rules.*.insight_type' => ['required', Rule::in(self::INSIGHT_TYPES)],
            'rules.*.minimum_records' => ['required', 'integer', 'min:1', 'max:500'],
            'rules.*.minimum_distinct_sources' => ['required', 'integer', 'min:1', 'max:500'],
            'rules.*.minimum_confidence' => ['nullable', 'numeric', 'between:0,1'],
            'rules.*.minimum_source_reliability' => ['nullable', 'numeric', 'between:0,1'],
            'rules.*.max_age_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'rules.*.require_geography' => ['sometimes', 'boolean'],
            'rules.*.require_target_segment' => ['sometimes', 'boolean'],
            'rules.*.require_sample_denominator' => ['sometimes', 'boolean'],
            'rules.*.is_mandatory' => ['sometimes', 'boolean'],
        ]);

        $mandatoryCount = 0;
        foreach ($data['rules'] as $index => $rule) {
            if ($rule['minimum_distinct_sources'] > $rule['minimum_records']) {
                abort(422, 'Each rule minimum distinct-source count must not exceed its minimum record count (rule '.($index + 1).').');
            }

            if (($rule['is_mandatory'] ?? true) === true) {
                $mandatoryCount++;
            }
        }

        abort_unless($mandatoryCount > 0, 422, 'At least one rule must be mandatory.');

        $policy = $service->create(
            $tenantContext->tenantId(),
            (string) $request->user()->getAuthIdentifier(),
            $data,
        );

        return response()->json(['data' => $policy], 201);
    }

    public function submit(
        string $policyId,
        Request $request,
        TenantContext $tenantContext,
        EvidencePolicyService $service,
    ): JsonResponse {
        $policy = $service->submit(
            $tenantContext->tenantId(),
            $policyId,
            (string) $request->user()->getAuthIdentifier(),
            $tenantContext->canAdministerTenant(),
        );

        return response()->json(['data' => $policy]);
    }

    public function decide(
        string $policyId,
        Request $request,
        TenantContext $tenantContext,
        EvidencePolicyService $service,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or administrator decision is required.');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['APPROVE', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $policy = $service->decide(
            $tenantContext->tenantId(),
            $policyId,
            (string) $request->user()->getAuthIdentifier(),
            $data['decision'],
            $data['reason'],
        );

        return response()->json(['data' => $policy]);
    }

    public function coverage(
        string $initiativeId,
        Request $request,
        TenantContext $tenantContext,
        EvidencePolicyService $policyService,
        EvidenceSufficiencyService $sufficiencyService,
    ): JsonResponse {
        $data = $request->validate([
            'stage' => ['sometimes', Rule::in(['RESEARCH', 'ASSESSMENT'])],
        ]);

        $coverage = $policyService->coverage(
            $tenantContext->tenantId(),
            $initiativeId,
            $data['stage'] ?? 'ASSESSMENT',
            $sufficiencyService,
        );

        return response()->json(['data' => $coverage]);
    }
}
