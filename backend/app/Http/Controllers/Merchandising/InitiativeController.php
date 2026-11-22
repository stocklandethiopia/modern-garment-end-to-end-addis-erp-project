<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Services\Merchandising\EvidenceSufficiencyService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InitiativeController extends Controller
{
    private const TYPES = [
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

    public function index(TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();

        $items = DB::table('initiatives')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate(25);

        $items->setCollection($items->getCollection()->map(function (object $initiative) use ($tenantId): object {
            $snapshot = DB::table('initiative_versions')
                ->where('tenant_id', $tenantId)
                ->where('initiative_id', $initiative->id)
                ->where('version_no', $initiative->current_version)
                ->value('snapshot');

            $initiative->current_snapshot = is_string($snapshot)
                ? json_decode($snapshot, true, 512, JSON_THROW_ON_ERROR)
                : $snapshot;

            return $initiative;
        }));

        return response()->json($items);
    }

    public function show(string $initiativeId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $initiative = DB::table('initiatives')
            ->where('tenant_id', $tenantId)
            ->where('id', $initiativeId)
            ->first();

        abort_if($initiative === null, 404);

        $versions = DB::table('initiative_versions')
            ->where('tenant_id', $tenantId)
            ->where('initiative_id', $initiativeId)
            ->orderByDesc('version_no')
            ->get();

        $decisions = DB::table('initiative_decisions')
            ->where('tenant_id', $tenantId)
            ->where('initiative_id', $initiativeId)
            ->orderByDesc('decided_at')
            ->get();

        $insights = DB::table('market_insights as i')
            ->join('insight_sources as s', function ($join): void {
                $join->on('s.id', '=', 'i.source_id')->on('s.tenant_id', '=', 'i.tenant_id');
            })
            ->where('i.tenant_id', $tenantId)
            ->where('i.initiative_id', $initiativeId)
            ->orderByDesc('i.observed_at')
            ->limit(100)
            ->get([
                'i.id',
                'i.insight_type',
                'i.title',
                'i.summary',
                'i.observed_at',
                'i.geography',
                'i.target_segment',
                'i.metric_name',
                'i.metric_value_numeric',
                'i.metric_value_text',
                'i.metric_unit',
                'i.confidence',
                's.source_name',
            ]);

        return response()->json([
            'data' => $initiative,
            'versions' => $versions,
            'decisions' => $decisions,
            'insights' => $insights,
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('initiatives', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'initiative_type' => ['required', Rule::in(self::TYPES)],
            'title' => ['required', 'string', 'max:200'],
            'problem_statement' => ['required', 'string', 'min:10', 'max:10000'],
            'objective' => ['required', 'string', 'min:5', 'max:10000'],
            'target_market' => ['required', 'string', 'max:160'],
            'target_consumer' => ['nullable', 'string', 'max:160'],
            'expected_benefit' => ['nullable', 'string', 'max:5000'],
            'sponsor_entity_id' => [
                'nullable',
                'uuid',
                Rule::exists('legal_entities', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ]);

        $initiativeId = (string) Str::uuid();
        $versionId = (string) Str::uuid();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $now = now();

        DB::transaction(function () use ($data, $tenantId, $initiativeId, $versionId, $actorId, $now): void {
            DB::table('initiatives')->insert([
                'id' => $initiativeId,
                'tenant_id' => $tenantId,
                'sponsor_entity_id' => $data['sponsor_entity_id'] ?? null,
                'owner_user_id' => $actorId,
                'code' => $data['code'],
                'initiative_type' => $data['initiative_type'],
                'title' => $data['title'],
                'problem_statement' => $data['problem_statement'],
                'objective' => $data['objective'],
                'target_market' => $data['target_market'],
                'target_consumer' => $data['target_consumer'] ?? null,
                'expected_benefit' => $data['expected_benefit'] ?? null,
                'status' => 'DRAFT',
                'current_version' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('initiative_versions')->insert([
                'id' => $versionId,
                'tenant_id' => $tenantId,
                'initiative_id' => $initiativeId,
                'version_no' => 1,
                'snapshot' => json_encode($data, JSON_THROW_ON_ERROR),
                'change_reason' => 'Initial initiative proposal',
                'created_by' => $actorId,
                'created_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'INITIATIVE_DRAFT_CREATED', 'INITIATIVE', $initiativeId, 'Initial initiative proposal');
        });

        return response()->json([
            'data' => DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->first(),
            'version' => 1,
        ], 201);
    }

    public function submit(string $initiativeId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $canAdministerTenant = $tenantContext->canAdministerTenant();

        $initiative = DB::transaction(function () use ($initiativeId, $tenantId, $actorId, $canAdministerTenant): object {
            $initiative = DB::table('initiatives')
                ->where('tenant_id', $tenantId)
                ->where('id', $initiativeId)
                ->lockForUpdate()
                ->first();

            abort_if($initiative === null, 404);
            abort_unless(
                $initiative->owner_user_id === $actorId || $canAdministerTenant,
                403,
                'Only the initiative owner or a tenant administrator may submit it for screening.',
            );
            abort_unless($initiative->status === 'DRAFT', 422, 'Only a draft initiative can be submitted for screening.');

            DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->update([
                'status' => 'SCREENING',
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'INITIATIVE_SUBMITTED_FOR_SCREENING', 'INITIATIVE', $initiativeId, 'Sponsor submitted the initiative for screening');

            return DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->first();
        });

        return response()->json(['data' => $initiative]);
    }

    public function advanceToAssessment(string $initiativeId, Request $request, TenantContext $tenantContext, EvidenceSufficiencyService $evidenceSufficiency): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $result = DB::transaction(function () use ($initiativeId, $tenantId, $actorId, $evidenceSufficiency): array {
            $initiative = DB::table('initiatives')
                ->where('tenant_id', $tenantId)
                ->where('id', $initiativeId)
                ->lockForUpdate()
                ->first();

            abort_if($initiative === null, 404);
            abort_unless($initiative->status === 'RESEARCH', 422, 'Only an initiative approved for research can enter assessment.');

            $coverage = $evidenceSufficiency->evaluate($tenantId, $initiative, 'ASSESSMENT');

            if ($coverage['status'] === 'NOT_CONFIGURED') {
                $hasEvidence = DB::table('market_insights')
                    ->where('tenant_id', $tenantId)
                    ->where('initiative_id', $initiativeId)
                    ->exists();

                abort_unless($hasEvidence, 422, 'At least one sourced market insight is required before assessment.');
            } else {
                abort_unless(
                    $coverage['status'] === 'MET',
                    422,
                    'Configured assessment evidence requirements are not satisfied.',
                );
            }

            DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->update([
                'status' => 'ASSESSMENT',
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'INITIATIVE_MOVED_TO_ASSESSMENT', 'INITIATIVE', $initiativeId, 'Research evidence attached; assessment started');

            return [
                'initiative' => DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->first(),
                'evidence_coverage' => $coverage,
            ];
        });

        return response()->json([
            'data' => $result['initiative'],
            'evidence_coverage' => $result['evidence_coverage'],
        ]);
    }

    public function decide(string $initiativeId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or admin approval is required.');

        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'decision' => ['required', Rule::in([
                'APPROVE_RESEARCH',
                'APPROVE',
                'APPROVE_CONDITIONAL',
                'RETURN_TO_RESEARCH',
                'PARK',
                'REJECT',
            ])],
            'reason' => ['required', 'string', 'min:10', 'max:5000'],
            'actions' => ['nullable', 'array', 'max:20'],
            'actions.*' => ['string', 'max:500'],
            'review_date' => ['nullable', 'date'],
        ]);

        $initiative = DB::transaction(function () use ($initiativeId, $tenantId, $actorId, $data): object {
            $initiative = DB::table('initiatives')
                ->where('tenant_id', $tenantId)
                ->where('id', $initiativeId)
                ->lockForUpdate()
                ->first();

            abort_if($initiative === null, 404);
            abort_unless(
                $actorId !== $initiative->owner_user_id,
                403,
                'The initiative creator cannot approve their own screening or assessment decision.',
            );

            $stage = $initiative->status;
            $nextStatus = match ([$stage, $data['decision']]) {
                ['SCREENING', 'APPROVE_RESEARCH'] => 'RESEARCH',
                ['SCREENING', 'PARK'] => 'PARKED',
                ['SCREENING', 'REJECT'] => 'REJECTED',
                ['ASSESSMENT', 'APPROVE'] => 'APPROVED',
                ['ASSESSMENT', 'APPROVE_CONDITIONAL'] => 'CONDITIONALLY_APPROVED',
                ['ASSESSMENT', 'RETURN_TO_RESEARCH'] => 'RESEARCH',
                ['ASSESSMENT', 'PARK'] => 'PARKED',
                ['ASSESSMENT', 'REJECT'] => 'REJECTED',
                default => null,
            };

            abort_if($nextStatus === null, 422, 'This decision is not allowed at the current initiative stage.');

            DB::table('initiative_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'initiative_id' => $initiativeId,
                'initiative_version' => $initiative->current_version,
                'stage' => $stage,
                'decision' => $data['decision'],
                'reason' => $data['reason'],
                'actions' => json_encode($data['actions'] ?? [], JSON_THROW_ON_ERROR),
                'review_date' => $data['review_date'] ?? null,
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->update([
                'status' => $nextStatus,
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'INITIATIVE_DECISION_RECORDED', 'INITIATIVE', $initiativeId, $data['reason']);

            return DB::table('initiatives')->where('tenant_id', $tenantId)->where('id', $initiativeId)->first();
        });

        return response()->json(['data' => $initiative]);
    }

    private function writeAudit(
        string $tenantId,
        string $actorId,
        string $action,
        string $objectType,
        string $objectId,
        string $reason,
    ): void {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'INITIATIVE'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
