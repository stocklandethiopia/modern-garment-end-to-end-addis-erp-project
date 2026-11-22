<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InsightSourceController extends Controller
{
    private const TYPES = [
        'STORE_OBSERVATION',
        'CUSTOMER_FEEDBACK',
        'WHOLESALE_REQUEST',
        'ECOMMERCE_ANALYTICS',
        'SURVEY',
        'COMPETITOR_RESEARCH',
        'EXTERNAL_BENCHMARK',
        'INTERNAL_SYSTEM',
        'SUPPLIER_SIGNAL',
        'PRODUCTION_CAPABILITY',
        'OTHER',
    ];

    public function index(TenantContext $tenantContext): JsonResponse
    {
        $sources = DB::table('insight_sources')
            ->where('tenant_id', $tenantContext->tenantId())
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($sources);
    }

    public function store(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'source_type' => ['required', Rule::in(self::TYPES)],
            'source_name' => ['required', 'string', 'max:200'],
            'source_reference' => ['nullable', 'string', 'max:1000'],
            'capture_method' => ['required', 'string', 'max:80'],
            'geography' => ['nullable', 'string', 'max:160'],
            'reliability_score' => ['nullable', 'numeric', 'between:0,1'],
            'permitted_use_basis' => ['required', 'string', 'min:3', 'max:200'],
            'limitations' => ['nullable', 'string', 'max:5000'],
        ]);

        $sourceId = (string) Str::uuid();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $now = now();

        DB::transaction(function () use ($data, $tenantId, $sourceId, $actorId, $now): void {
            DB::table('insight_sources')->insert([
                'id' => $sourceId,
                'tenant_id' => $tenantId,
                ...$data,
                'created_by' => $actorId,
                'created_at' => $now,
            ]);

            DB::table('audit_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'actor_user_id' => $actorId,
                'action' => 'INSIGHT_SOURCE_RECORDED',
                'object_type' => 'INSIGHT_SOURCE',
                'object_id' => $sourceId,
                'scope' => json_encode(['source_type' => $data['source_type']], JSON_THROW_ON_ERROR),
                'reason' => 'Market insight source provenance recorded',
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return response()->json([
            'data' => DB::table('insight_sources')->where('tenant_id', $tenantId)->where('id', $sourceId)->first(),
        ], 201);
    }
}
