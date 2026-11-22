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
            'source_system' => ['nullable', 'string', 'min:2', 'max:80'],
            'source_event_id' => ['nullable', 'string', 'min:1', 'max:160'],
        ]);

        $hasSourceSystem = isset($data['source_system']) && trim($data['source_system']) !== '';
        $hasSourceEventId = isset($data['source_event_id']) && trim($data['source_event_id']) !== '';
        abort_if(
            $hasSourceSystem !== $hasSourceEventId,
            422,
            'source_system and source_event_id must be supplied together for idempotent event ingestion.',
        );

        if (isset($data['sample_size'], $data['denominator'])) {
            abort_if($data['denominator'] < $data['sample_size'], 422, 'The denominator cannot be smaller than the sample size.');
        }

        if ($hasSourceSystem) {
            $data['source_system'] = strtoupper(trim($data['source_system']));
            $data['source_event_id'] = trim($data['source_event_id']);
        } else {
            $data['source_system'] = null;
            $data['source_event_id'] = null;
        }

        $insightId = (string) Str::uuid();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $now = now();
        $eventId = $data['source_event_id'];
        $sourceSystem = $data['source_system'];
        $payload = $data;
        unset($payload['source_system'], $payload['source_event_id']);
        ksort($payload);
        $payloadHash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        $result = DB::transaction(function () use (
            $data,
            $tenantId,
            $insightId,
            $actorId,
            $now,
            $eventId,
            $sourceSystem,
            $payloadHash,
        ): array {
            if ($eventId !== null && $sourceSystem !== null) {
                DB::selectOne(
                    'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                    [$tenantId.':market-insight:'.$sourceSystem.':'.$eventId],
                );

                $receipt = DB::table('market_insight_event_receipts')
                    ->where('tenant_id', $tenantId)
                    ->where('source_system', $sourceSystem)
                    ->where('source_event_id', $eventId)
                    ->lockForUpdate()
                    ->first();

                if ($receipt !== null) {
                    abort_unless(
                        hash_equals($receipt->payload_sha256, $payloadHash),
                        409,
                        'This source event ID was previously received with a different payload. Use a new event ID for a changed observation.',
                    );

                    DB::table('market_insight_event_receipts')
                        ->where('tenant_id', $tenantId)
                        ->where('id', $receipt->id)
                        ->update([
                            'duplicate_count' => DB::raw('duplicate_count + 1'),
                            'last_received_at' => $now,
                            'updated_at' => $now,
                        ]);

                    DB::table('audit_events')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'actor_user_id' => $actorId,
                        'action' => 'MARKET_INSIGHT_EVENT_DUPLICATE_DEDUPLICATED',
                        'object_type' => 'MARKET_INSIGHT',
                        'object_id' => $receipt->market_insight_id,
                        'scope' => json_encode([
                            'source_system' => $sourceSystem,
                            'source_event_id' => $eventId,
                            'duplicate_count' => (int) $receipt->duplicate_count + 1,
                        ], JSON_THROW_ON_ERROR),
                        'reason' => 'A replayed source event was deduplicated; the original insight was retained.',
                        'correlation_id' => (string) Str::uuid(),
                        'occurred_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    return [
                        'insight' => DB::table('market_insights')
                            ->where('tenant_id', $tenantId)
                            ->where('id', $receipt->market_insight_id)
                            ->first(),
                        'deduplicated' => true,
                        'duplicate_count' => (int) $receipt->duplicate_count + 1,
                    ];
                }
            }

            DB::table('market_insights')->insert([
                'id' => $insightId,
                'tenant_id' => $tenantId,
                ...$data,
                'captured_at' => $now,
                'analyst_user_id' => $actorId,
                'created_at' => $now,
            ]);

            if ($eventId !== null && $sourceSystem !== null) {
                DB::table('market_insight_event_receipts')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'source_system' => $sourceSystem,
                    'source_event_id' => $eventId,
                    'market_insight_id' => $insightId,
                    'payload_sha256' => $payloadHash,
                    'duplicate_count' => 0,
                    'first_received_at' => $now,
                    'last_received_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

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
                    'source_system' => $sourceSystem,
                    'source_event_id' => $eventId,
                ], JSON_THROW_ON_ERROR),
                'reason' => 'Market insight captured with source and permitted-use basis.',
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return [
                'insight' => DB::table('market_insights')
                    ->where('tenant_id', $tenantId)
                    ->where('id', $insightId)
                    ->first(),
                'deduplicated' => false,
                'duplicate_count' => 0,
            ];
        });

        return response()->json([
            'data' => $result['insight'],
            'deduplicated' => $result['deduplicated'],
            'duplicate_count' => $result['duplicate_count'],
        ], $result['deduplicated'] ? 200 : 201);
    }
}
