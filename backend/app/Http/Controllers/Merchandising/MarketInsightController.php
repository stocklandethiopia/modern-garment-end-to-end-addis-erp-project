<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MarketInsightController extends Controller
{
    private const TYPES = [
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

    public function index(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $query = DB::table('market_insights as i')
            ->join('insight_sources as s', function ($join): void {
                $join->on('s.id', '=', 'i.source_id')->on('s.tenant_id', '=', 'i.tenant_id');
            })
            ->where('i.tenant_id', $tenantContext->tenantId())
            ->select([
                'i.id',
                'i.initiative_id',
                'i.insight_type',
                'i.title',
                'i.summary',
                'i.observed_at',
                'i.captured_at',
                'i.geography',
                'i.target_segment',
                'i.channel_code',
                'i.category_code',
                'i.metric_name',
                'i.metric_value_numeric',
                'i.metric_value_text',
                'i.metric_unit',
                'i.sample_size',
                'i.denominator',
                'i.confidence',
                'i.limitations',
                's.id as source_id',
                's.source_name',
                's.source_type',
            ])
            ->orderByDesc('i.observed_at');

        if ($request->filled('initiative_id')) {
            $query->where('i.initiative_id', $request->string('initiative_id')->toString());
        }

        if ($request->filled('insight_type')) {
            $query->where('i.insight_type', $request->string('insight_type')->toString());
        }

        return response()->json($query->paginate(50));
    }

    public function store(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'source_id' => [
                'required',
                'uuid',
                Rule::exists('insight_sources', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'initiative_id' => [
                'nullable',
                'uuid',
                Rule::exists('initiatives', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'insight_type' => ['required', Rule::in(self::TYPES)],
            'title' => ['required', 'string', 'max:200'],
            'summary' => ['required', 'string', 'min:5', 'max:10000'],
            'observed_at' => ['required', 'date'],
            'geography' => ['nullable', 'string', 'max:160'],
            'target_segment' => ['nullable', 'string', 'max:160'],
            'channel_code' => ['nullable', 'string', 'max:60'],
            'category_code' => ['nullable', 'string', 'max:80'],
            'metric_name' => ['nullable', 'string', 'max:120'],
            'metric_value_numeric' => ['nullable', 'numeric'],
            'metric_value_text' => ['nullable', 'string', 'max:300'],
            'metric_unit' => ['nullable', 'string', 'max:40'],
            'sample_size' => ['nullable', 'integer', 'min:0'],
            'denominator' => ['nullable', 'integer', 'min:0'],
            'confidence' => ['nullable', 'numeric', 'between:0,1'],
            'limitations' => ['nullable', 'string', 'max:5000'],
            'privacy_basis' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        if (isset($data['sample_size'], $data['denominator'])) {
            abort_if($data['denominator'] < $data['sample_size'], 422, 'The denominator cannot be smaller than the sample size.');
        }

        $insightId = (string) Str::uuid();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $now = now();

        DB::transaction(function () use ($data, $tenantId, $insightId, $actorId, $now): void {
            DB::table('market_insights')->insert([
                'id' => $insightId,
                'tenant_id' => $tenantId,
                ...$data,
                'captured_at' => $now,
                'analyst_user_id' => $actorId,
                'created_at' => $now,
            ]);

            DB::table('audit_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'actor_user_id' => $actorId,
                'action' => 'MARKET_INSIGHT_CAPTURED',
                'object_type' => 'MARKET_INSIGHT',
                'object_id' => $insightId,
                'scope' => json_encode([
                    'insight_type' => $data['insight_type'],
                    'initiative_id' => $data['initiative_id'] ?? null,
                ], JSON_THROW_ON_ERROR),
                'reason' => 'Market insight captured with source and permitted-use basis',
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return response()->json([
            'data' => DB::table('market_insights')
                ->where('tenant_id', $tenantId)
                ->where('id', $insightId)
                ->first(),
        ], 201);
    }
}
