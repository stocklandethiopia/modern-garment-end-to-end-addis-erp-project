<?php

namespace App\Services\Merchandising;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvidencePolicyService
{
    public function index(string $tenantId): mixed
    {
        $ruleCounts = DB::table('initiative_evidence_policy_rules')
            ->select('tenant_id', 'policy_id', DB::raw('COUNT(*) AS rule_count'))
            ->where('tenant_id', $tenantId)
            ->groupBy('tenant_id', 'policy_id');

        return DB::table('initiative_evidence_policies as p')
            ->leftJoinSub($ruleCounts, 'rc', function ($join): void {
                $join->on('rc.tenant_id', '=', 'p.tenant_id')
                    ->on('rc.policy_id', '=', 'p.id');
            })
            ->where('p.tenant_id', $tenantId)
            ->select(['p.*', DB::raw('COALESCE(rc.rule_count, 0) AS rule_count')])
            ->orderByDesc('p.created_at')
            ->paginate(50);
    }

    public function create(string $tenantId, string $actorId, array $data): array
    {
        $policyId = (string) Str::uuid();
        $now = now();

        return DB::transaction(function () use ($tenantId, $actorId, $data, $policyId, $now): array {
            DB::selectOne(
                'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$tenantId.':'.$data['code']],
            );

            $version = (int) DB::table('initiative_evidence_policies')
                ->where('tenant_id', $tenantId)
                ->where('code', $data['code'])
                ->max('version_no') + 1;

            DB::table('initiative_evidence_policies')->insert([
                'id' => $policyId,
                'tenant_id' => $tenantId,
                'code' => $data['code'],
                'name' => $data['name'],
                'initiative_type' => $data['initiative_type'],
                'stage' => $data['stage'],
                'target_market' => $data['target_market'] ?? null,
                'version_no' => $version,
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'status' => 'DRAFT',
                'change_reason' => $data['change_reason'],
                'created_by' => $actorId,
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($data['rules'] as $rule) {
                DB::table('initiative_evidence_policy_rules')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'policy_id' => $policyId,
                    'requirement_code' => $rule['requirement_code'],
                    'insight_type' => $rule['insight_type'],
                    'minimum_records' => $rule['minimum_records'],
                    'minimum_distinct_sources' => $rule['minimum_distinct_sources'],
                    'minimum_confidence' => $rule['minimum_confidence'] ?? null,
                    'minimum_source_reliability' => $rule['minimum_source_reliability'] ?? null,
                    'max_age_days' => $rule['max_age_days'] ?? null,
                    'require_geography' => $rule['require_geography'] ?? false,
                    'require_target_segment' => $rule['require_target_segment'] ?? false,
                    'require_sample_denominator' => $rule['require_sample_denominator'] ?? false,
                    'is_mandatory' => $rule['is_mandatory'] ?? true,
                    'created_at' => $now,
                ]);
            }

            $this->audit(
                $tenantId,
                $actorId,
                'EVIDENCE_POLICY_DRAFT_CREATED',
                'EVIDENCE_POLICY',
                $policyId,
                'Versioned initiative evidence requirements configured in draft form.',
            );

            return $this->withRules($tenantId, $policyId);
        });
    }

    public function submit(string $tenantId, string $policyId, string $actorId, bool $canAdminister): array
    {
        return DB::transaction(function () use ($tenantId, $policyId, $actorId, $canAdminister): array {
            $policy = DB::table('initiative_evidence_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $policyId)
                ->lockForUpdate()
                ->first();

            abort_if($policy === null, 404);
            abort_unless($policy->created_by === $actorId || $canAdminister, 403);
            abort_unless($policy->status === 'DRAFT', 422, 'Only a draft evidence policy can be submitted.');

            $mandatoryCount = DB::table('initiative_evidence_policy_rules')
                ->where('tenant_id', $tenantId)
                ->where('policy_id', $policyId)
                ->where('is_mandatory', true)
                ->count();

            abort_unless($mandatoryCount > 0, 422, 'At least one mandatory evidence requirement is required.');

            DB::table('initiative_evidence_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $policyId)
                ->update([
                    'status' => 'PENDING_APPROVAL',
                    'updated_at' => now(),
                ]);

            $this->audit(
                $tenantId,
                $actorId,
                'EVIDENCE_POLICY_SUBMITTED',
                'EVIDENCE_POLICY',
                $policyId,
                'Evidence policy submitted for independent approval.',
            );

            return $this->withRules($tenantId, $policyId);
        });
    }

    public function decide(
        string $tenantId,
        string $policyId,
        string $actorId,
        string $decision,
        string $reason,
    ): array {
        return DB::transaction(function () use ($tenantId, $policyId, $actorId, $decision, $reason): array {
            $policy = DB::table('initiative_evidence_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $policyId)
                ->lockForUpdate()
                ->first();

            abort_if($policy === null, 404);
            abort_unless($policy->status === 'PENDING_APPROVAL', 422, 'Evidence policy is not awaiting a decision.');
            abort_unless($policy->created_by !== $actorId, 403, 'The policy author cannot approve or reject their own policy.');

            $mandatoryCount = DB::table('initiative_evidence_policy_rules')
                ->where('tenant_id', $tenantId)
                ->where('policy_id', $policyId)
                ->where('is_mandatory', true)
                ->count();

            abort_unless($mandatoryCount > 0, 422, 'At least one mandatory evidence requirement is required.');

            $status = $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED';
            $approvedBy = null;
            $approvedAt = null;

            if ($decision === 'APPROVE') {
                DB::selectOne(
                    'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                    [
                        $tenantId.':evidence-policy:'.$policy->initiative_type.':'.$policy->stage.':'.($policy->target_market ?? '*'),
                    ],
                );

                $overlap = $this->hasApprovedOverlap($tenantId, $policy);
                abort_unless(
                    ! $overlap,
                    422,
                    'An approved evidence policy already overlaps this initiative type, stage, market and effective period.',
                );
                $approvedBy = $actorId;
                $approvedAt = now();
            }

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'EVIDENCE_POLICY',
                'object_id' => $policyId,
                'object_version' => $policy->version_no,
                'decision' => $decision,
                'reason' => $reason,
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            DB::table('initiative_evidence_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $policyId)
                ->update([
                    'status' => $status,
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                    'updated_at' => now(),
                ]);

            $this->audit(
                $tenantId,
                $actorId,
                $decision === 'APPROVE' ? 'EVIDENCE_POLICY_APPROVED' : 'EVIDENCE_POLICY_REJECTED',
                'EVIDENCE_POLICY',
                $policyId,
                $reason,
            );

            return $this->withRules($tenantId, $policyId);
        });
    }

    public function coverage(string $tenantId, string $initiativeId, string $stage, EvidenceSufficiencyService $sufficiency): array
    {
        $initiative = DB::table('initiatives')
            ->where('tenant_id', $tenantId)
            ->where('id', $initiativeId)
            ->first();

        abort_if($initiative === null, 404);

        return [
            'initiative_id' => $initiativeId,
            'stage' => $stage,
            'evidence_coverage' => $sufficiency->evaluate($tenantId, $initiative, $stage),
        ];
    }

    private function hasApprovedOverlap(string $tenantId, object $policy): bool
    {
        $query = DB::table('initiative_evidence_policies')
            ->where('tenant_id', $tenantId)
            ->where('initiative_type', $policy->initiative_type)
            ->where('stage', $policy->stage)
            ->where('status', 'APPROVED');

        if ($policy->target_market === null) {
            $query->whereNull('target_market');
        } else {
            $query->where('target_market', $policy->target_market);
        }

        if ($policy->effective_to !== null) {
            $query->whereDate('effective_from', '<=', $policy->effective_to);
        }

        $query->where(function ($query) use ($policy): void {
            $query->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $policy->effective_from);
        });

        return $query->exists();
    }

    private function withRules(string $tenantId, string $policyId): array
    {
        $policy = DB::table('initiative_evidence_policies')
            ->where('tenant_id', $tenantId)
            ->where('id', $policyId)
            ->first();

        return [
            'policy' => $policy,
            'rules' => DB::table('initiative_evidence_policy_rules')
                ->where('tenant_id', $tenantId)
                ->where('policy_id', $policyId)
                ->orderBy('requirement_code')
                ->get(),
        ];
    }

    private function audit(
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
            'scope' => json_encode(['object_type' => $objectType], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
