<?php

namespace App\Services\Agents;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentGovernanceService
{
    public function startRun(string $tenantId, ?string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('agent_run_audits')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'run_key' => $data['run_key'],
            'agent_code' => $data['agent_code'],
            'workflow_code' => $data['workflow_code'],
            'provider_code' => $data['provider_code'],
            'model_code' => $data['model_code'] ?? null,
            'model_version' => $data['model_version'] ?? null,
            'execution_mode' => $data['execution_mode'],
            'risk_tier' => $data['risk_tier'],
            'input_sha256' => strtolower($data['input_sha256']),
            'context_references' => json_encode($data['context_references'] ?? [], JSON_THROW_ON_ERROR),
            'tool_trace' => json_encode([], JSON_THROW_ON_ERROR),
            'policy_snapshot' => json_encode($data['policy_snapshot'] ?? [], JSON_THROW_ON_ERROR),
            'token_usage' => json_encode([], JSON_THROW_ON_ERROR),
            'status' => 'STARTED',
            'started_by' => $actorId,
            'started_at' => now(),
            'created_at' => now(),
        ]);

        return DB::table('agent_run_audits')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function finishRun(string $tenantId, string $runId, string $status, string $outputSha256, array $usage, array $toolTrace = []): object
    {
        if (! in_array($status, ['SUCCEEDED', 'FAILED', 'CANCELLED', 'DENIED'], true)) {
            throw ValidationException::withMessages(['status' => 'Unsupported agent run terminal status.']);
        }
        if (! preg_match('/^[0-9a-f]{64}$/i', $outputSha256)) {
            throw ValidationException::withMessages(['output_sha256' => 'An output hash is required to finalize an agent run.']);
        }

        return DB::transaction(function () use ($tenantId, $runId, $status, $outputSha256, $usage, $toolTrace): object {
            $run = DB::table('agent_run_audits')->where('tenant_id', $tenantId)->where('id', $runId)->lockForUpdate()->first();
            if ($run === null || $run->status !== 'STARTED') {
                throw ValidationException::withMessages(['run' => 'Only a started agent run can be finalized.']);
            }
            DB::table('agent_run_audits')->where('tenant_id', $tenantId)->where('id', $runId)->update([
                'status' => $status,
                'output_sha256' => strtolower($outputSha256),
                'token_usage' => json_encode($usage, JSON_THROW_ON_ERROR),
                'tool_trace' => json_encode($toolTrace, JSON_THROW_ON_ERROR),
                'completed_at' => now(),
            ]);

            return DB::table('agent_run_audits')->where('tenant_id', $tenantId)->where('id', $runId)->first();
        }, 3);
    }

    public function proposeAction(string $tenantId, string $actorId, array $data): object
    {
        $run = DB::table('agent_run_audits')->where('tenant_id', $tenantId)->where('id', $data['agent_run_id'])->first();
        if ($run === null || $run->status !== 'SUCCEEDED') {
            throw ValidationException::withMessages(['agent_run_id' => 'An action proposal requires a successful, tenant-scoped agent run.']);
        }

        $risk = strtoupper($data['risk_tier']);
        if (! in_array($risk, ['A0', 'A1', 'A2', 'A3', 'A4'], true)) {
            throw ValidationException::withMessages(['risk_tier' => 'Unsupported action risk tier.']);
        }
        if ($risk === 'A4') {
            throw ValidationException::withMessages(['risk_tier' => 'A4 actions require professional or legal authorization outside autonomous execution. The agent may produce an advisory record only.']);
        }

        $id = (string) Str::uuid();
        DB::table('agent_action_proposals')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'agent_run_id' => $run->id,
            'proposal_key' => $data['proposal_key'],
            'capability_code' => $data['capability_code'],
            'target_record_type' => $data['target_record_type'],
            'target_record_id' => $data['target_record_id'] ?? null,
            'action_type' => $data['action_type'],
            'risk_tier' => $risk,
            'proposed_payload' => json_encode($data['proposed_payload'], JSON_THROW_ON_ERROR),
            'expected_preconditions' => json_encode($data['expected_preconditions'] ?? [], JSON_THROW_ON_ERROR),
            'reason' => $data['reason'],
            'status' => in_array($risk, ['A2', 'A3'], true) ? 'AWAITING_APPROVAL' : 'PROPOSED',
            'expires_at' => $data['expires_at'] ?? now()->addHours(24),
            'created_at' => now(),
        ]);

        return DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function decideAction(string $tenantId, string $actorId, string $proposalId, string $decision, string $reason, ?array $approvedPayload = null): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $proposalId, $decision, $reason, $approvedPayload): object {
            $proposal = DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $proposalId)->lockForUpdate()->first();
            if ($proposal === null || $proposal->status !== 'AWAITING_APPROVAL') {
                throw ValidationException::withMessages(['proposal' => 'Only an action awaiting approval can receive a decision.']);
            }
            if (in_array($proposal->risk_tier, ['A0', 'A1'], true)) {
                throw ValidationException::withMessages(['risk_tier' => 'Read/draft actions do not enter the material-action approval path.']);
            }
            $run = DB::table('agent_run_audits')->where('tenant_id', $tenantId)->where('id', $proposal->agent_run_id)->first();
            if ($run === null || (string) $run->started_by === $actorId) {
                throw ValidationException::withMessages(['approver' => 'The proposer or initiating user cannot approve their own agent action.']);
            }
            if (! in_array($decision, ['APPROVE', 'REJECT', 'REQUEST_CHANGES'], true) || trim($reason) === '') {
                throw ValidationException::withMessages(['decision' => 'An explicit approval, rejection or change request with a reason is required.']);
            }

            $id = (string) Str::uuid();
            DB::table('agent_approval_decisions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'proposal_id' => $proposalId,
                'decision' => $decision,
                'decision_reason' => $reason,
                'approved_payload' => $approvedPayload === null ? null : json_encode($approvedPayload, JSON_THROW_ON_ERROR),
                'actor_user_id' => $actorId,
                'decided_at' => now(),
            ]);
            DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $proposalId)->update([
                'status' => $decision === 'APPROVE' ? 'APPROVED' : ($decision === 'REJECT' ? 'REJECTED' : 'PROPOSED'),
            ]);
            DB::table('agent_action_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'proposal_id' => $proposalId,
                'event_key' => 'agent-approval:'.$proposalId,
                'event_type' => $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED',
                'event_payload' => json_encode(['decision_id' => $id, 'reason' => $reason], JSON_THROW_ON_ERROR),
                'actor_user_id' => $actorId,
                'occurred_at' => now(),
                'created_at' => now(),
            ]);

            return DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $proposalId)->first();
        }, 3);
    }

    /**
     * Execute only enumerated, draft-creating business capabilities.
     * This deliberately has no generic SQL, shell, HTTP or arbitrary tool execution.
     */
    public function executeApprovedDraft(string $tenantId, string $actorId, string $proposalId, array $services): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $proposalId, $services): object {
            $proposal = DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $proposalId)->lockForUpdate()->first();
            if ($proposal === null || $proposal->status !== 'APPROVED') {
                throw ValidationException::withMessages(['proposal' => 'Only a human-approved proposal can be executed.']);
            }
            if ($proposal->risk_tier === 'A4') {
                throw ValidationException::withMessages(['risk_tier' => 'A4 actions are never autonomously executed.']);
            }

            $approval = DB::table('agent_approval_decisions')->where('tenant_id', $tenantId)
                ->where('proposal_id', $proposalId)->where('decision', 'APPROVE')->orderByDesc('decided_at')->first();
            if ($approval === null || $approval->actor_user_id === $actorId) {
                throw ValidationException::withMessages(['approval' => 'A distinct human approver decision is required before execution.']);
            }
            if ($proposal->expires_at !== null && now()->greaterThan($proposal->expires_at)) {
                DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $proposalId)->update(['status' => 'EXPIRED']);
                throw ValidationException::withMessages(['proposal' => 'The action approval has expired.']);
            }

            $payload = $approval->approved_payload !== null
                ? json_decode($approval->approved_payload, true, 512, JSON_THROW_ON_ERROR)
                : json_decode($proposal->proposed_payload, true, 512, JSON_THROW_ON_ERROR);

            $result = match ($proposal->capability_code) {
                'create_purchase_order_draft' => $services['sourcing']->createPurchaseOrder($tenantId, $actorId, $payload),
                'create_customer_order_draft' => $services['finished_goods']->createCustomerOrder($tenantId, $actorId, $payload),
                'create_quality_inspection_draft' => $services['quality']->createInspection($tenantId, $actorId, $payload),
                default => throw ValidationException::withMessages(['capability_code' => 'The requested agent capability is not on the approved allowlist.']),
            };

            $resultId = is_object($result) && isset($result->id) ? (string) $result->id : null;
            if ($resultId === null) {
                throw ValidationException::withMessages(['result' => 'The approved capability did not return a valid domain record reference.']);
            }

            $eventKey = 'agent-execution:'.$proposalId;
            DB::table('agent_action_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'proposal_id' => $proposalId,
                'event_key' => $eventKey,
                'event_type' => 'EXECUTED',
                'domain_service' => $proposal->capability_code,
                'result_reference_type' => $proposal->target_record_type,
                'result_reference_id' => $resultId,
                'event_payload' => json_encode(['created_record_id' => $resultId], JSON_THROW_ON_ERROR),
                'actor_user_id' => $actorId,
                'occurred_at' => now(),
                'created_at' => now(),
            ]);
            DB::table('agent_action_proposals')->where('tenant_id', $tenantId)->where('id', $proposalId)->update(['status' => 'EXECUTED']);

            return $result;
        }, 3);
    }
}
