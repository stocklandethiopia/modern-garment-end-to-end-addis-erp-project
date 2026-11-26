<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Services\Agents\AgentGovernanceService;
use App\Services\Distribution\FinishedGoodsService;
use App\Services\Quality\QualityControlService;
use App\Services\Sourcing\SourcingService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AgentGovernanceController extends Controller
{
    public function __construct(
        private readonly AgentGovernanceService $agents,
        private readonly TenantContextResolver $tenant
    ) {
    }

    public function startRun(Request $request): JsonResponse
    {
        Gate::authorize('agent.propose-action');
        $data = $request->validate([
            'run_key' => ['required','string','max:180'],
            'agent_code' => ['required','string','max:100'],
            'workflow_code' => ['required','string','max:120'],
            'provider_code' => ['required','string','max:60'],
            'model_code' => ['nullable','string','max:120'],
            'model_version' => ['nullable','string','max:100'],
            'execution_mode' => ['required','in:READ_ONLY,DRAFT,TOOL_ASSISTED,APPROVAL_GATED'],
            'risk_tier' => ['required','in:A0,A1,A2,A3,A4'],
            'input_sha256' => ['required','string','size:64'],
            'context_references' => ['nullable','array'],
            'policy_snapshot' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->agents->startRun(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data
        )], 201);
    }

    public function finishRun(Request $request, string $runId): JsonResponse
    {
        Gate::authorize('agent.propose-action');
        $data = $request->validate([
            'status' => ['required','in:SUCCEEDED,FAILED,CANCELLED,DENIED'],
            'output_sha256' => ['required','string','size:64'],
            'usage' => ['nullable','array'],
            'tool_trace' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->agents->finishRun(
            $this->tenant->requireTenantId(), $runId, $data['status'],
            $data['output_sha256'], $data['usage'] ?? [], $data['tool_trace'] ?? []
        )]);
    }

    public function proposeAction(Request $request): JsonResponse
    {
        Gate::authorize('agent.propose-action');
        $data = $request->validate([
            'agent_run_id' => ['required','uuid'],
            'proposal_key' => ['required','string','max:180'],
            'capability_code' => ['required','in:create_purchase_order_draft,create_customer_order_draft,create_quality_inspection_draft'],
            'target_record_type' => ['required','string','max:100'],
            'target_record_id' => ['nullable','uuid'],
            'action_type' => ['required','string','max:80'],
            'risk_tier' => ['required','in:A0,A1,A2,A3,A4'],
            'proposed_payload' => ['required','array'],
            'expected_preconditions' => ['nullable','array'],
            'reason' => ['required','string','max:2000'],
            'expires_at' => ['nullable','date','after:now'],
        ]);

        return response()->json(['data' => $this->agents->proposeAction(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data
        )], 201);
    }

    public function decideAction(Request $request, string $proposalId): JsonResponse
    {
        Gate::authorize('agent.approve-action');
        $data = $request->validate([
            'decision' => ['required','in:APPROVE,REJECT,REQUEST_CHANGES'],
            'reason' => ['required','string','max:2000'],
            'approved_payload' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->agents->decideAction(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $proposalId,
            $data['decision'], $data['reason'], $data['approved_payload'] ?? null
        )]);
    }

    public function executeApprovedDraft(string $proposalId): JsonResponse
    {
        Gate::authorize('agent.execute-approved-draft');
        $result = $this->agents->executeApprovedDraft(
            $this->tenant->requireTenantId(),
            $this->tenant->requireActorId(),
            $proposalId,
            [
                'sourcing' => app(SourcingService::class),
                'finished_goods' => app(FinishedGoodsService::class),
                'quality' => app(QualityControlService::class),
            ]
        );

        return response()->json(['data' => $result], 201);
    }
}
