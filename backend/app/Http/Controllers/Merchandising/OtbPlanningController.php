<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OtbPlanningController extends Controller
{
    private const FORMULA_VERSION = 'BALANCED_OTB_V1';

    public function policies(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('otb_policies')
                ->where('tenant_id', $tenantContext->tenantId())
                ->orderByDesc('created_at')
                ->paginate(50),
        ]);
    }

    public function createPolicy(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'legal_entity_id' => [
                'required',
                'uuid',
                Rule::exists('legal_entities', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/',
                Rule::unique('otb_policies', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'budget_basis' => ['required', Rule::in(['RETAIL', 'COST'])],
            'valuation_basis' => ['required', Rule::in(['RETAIL', 'COST'])],
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'effective_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:effective_from'],
            'inclusion_rules' => ['required', 'array'],
            'inclusion_rules.commitments_mode' => ['required', Rule::in(['ADDITIONAL', 'INCLUDED_IN_ON_ORDER'])],
            'inclusion_rules.actual_receipts_reduce_otb' => ['required', 'boolean'],
            'approval_thresholds' => ['required', 'array'],
        ]);

        abort_unless(
            $data['budget_basis'] === $data['valuation_basis'],
            422,
            'Open-to-buy inputs must use the same budget and valuation basis.',
        );

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();
        $code = strtoupper($data['code']);

        DB::transaction(function () use ($tenantId, $data, $id, $actor, $code): void {
            DB::table('otb_policies')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'],
                'code' => $code,
                'budget_basis' => $data['budget_basis'],
                'formula_version' => self::FORMULA_VERSION,
                'valuation_basis' => $data['valuation_basis'],
                'inclusion_rules' => json_encode($data['inclusion_rules'], JSON_THROW_ON_ERROR),
                'approval_thresholds' => json_encode($data['approval_thresholds'], JSON_THROW_ON_ERROR),
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $actor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'OTB_POLICY_CREATED', 'OTB_POLICY', $id, 'Draft policy created; no purchase authority is created.');
        });

        return response()->json([
            'data' => DB::table('otb_policies')->where('tenant_id', $tenantId)->where('id', $id)->first(),
        ], 201);
    }

    public function activatePolicy(string $policyId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'A tenant administrator must activate an OTB policy.');

        $policy = DB::transaction(function () use ($tenantId, $policyId, $approver): object {
            $policySeed = DB::table('otb_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $policyId)
                ->first();

            abort_if($policySeed === null, 404);

            DB::select(
                'SELECT pg_advisory_xact_lock(hashtext(?))',
                ['otb-policy-entity:'.$tenantId.':'.$policySeed->legal_entity_id],
            );

            $policy = DB::table('otb_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $policyId)
                ->lockForUpdate()
                ->first();

            abort_if($policy === null, 404);
            abort_unless($policy->status === 'DRAFT', 422, 'Only a draft OTB policy can be activated.');
            abort_unless($policy->created_by !== $approver, 403, 'The policy author cannot activate their own OTB policy.');

            $rules = json_decode($policy->inclusion_rules, true, 512, JSON_THROW_ON_ERROR);
            abort_unless(
                in_array($rules['commitments_mode'] ?? null, ['ADDITIONAL', 'INCLUDED_IN_ON_ORDER'], true)
                    && array_key_exists('actual_receipts_reduce_otb', $rules),
                422,
                'The policy must define commitment and receipt inclusion rules.',
            );

            $overlap = DB::table('otb_policies')
                ->where('tenant_id', $tenantId)
                ->where('legal_entity_id', $policy->legal_entity_id)
                ->where('status', 'ACTIVE')
                ->where('id', '<>', $policyId)
                ->where(function ($query) use ($policy): void {
                    $query->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', $policy->effective_from);
                })
                ->when(
                    $policy->effective_to !== null,
                    fn ($query) => $query->whereDate('effective_from', '<=', $policy->effective_to),
                )
                ->exists();

            abort_unless(! $overlap, 422, 'Another active OTB policy overlaps this entity and effective period.');

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'OTB_POLICY',
                'object_id' => $policyId,
                'object_version' => 1,
                'decision' => 'ACTIVATE',
                'reason' => 'OTB policy independently activated.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);

            DB::table('otb_policies')->where('tenant_id', $tenantId)->where('id', $policyId)->update([
                'status' => 'ACTIVE',
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $approver, 'OTB_POLICY_ACTIVATED', 'OTB_POLICY', $policyId, 'Independent policy activation.');

            return DB::table('otb_policies')->where('tenant_id', $tenantId)->where('id', $policyId)->first();
        });

        return response()->json(['data' => $policy]);
    }

    public function periods(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('otb_periods as p')
                ->join('otb_policies as o', fn ($join) => $join
                    ->on('o.id', '=', 'p.otb_policy_id')
                    ->on('o.tenant_id', '=', 'p.tenant_id'))
                ->where('p.tenant_id', $tenantContext->tenantId())
                ->orderByDesc('p.period_start')
                ->select(['p.*', 'o.code as policy_code', 'o.budget_basis', 'o.formula_version'])
                ->paginate(50),
        ]);
    }

    public function createPeriod(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'otb_policy_id' => [
                'required',
                'uuid',
                Rule::exists('otb_policies', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'ACTIVE')),
            ],
            'period_start' => ['required', 'date_format:Y-m-d'],
            'period_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            'currency' => ['required', 'string', 'size:3', 'alpha:upper'],
            'beginning_inventory' => ['required', 'numeric', 'min:0'],
            'planned_sales' => ['required', 'numeric', 'min:0'],
            'planned_markdowns' => ['required', 'numeric', 'min:0'],
            'planned_ending_inventory' => ['required', 'numeric', 'min:0'],
            'on_order_purchases' => ['required', 'numeric', 'min:0'],
            'commitments' => ['required', 'numeric', 'min:0'],
            'actual_receipts' => ['required', 'numeric', 'min:0'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $period = DB::transaction(function () use ($tenantId, $data, $id, $actor): object {
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', [
                'otb-period:'.$tenantId.':'.$data['otb_policy_id'].':'.$data['period_start'],
            ]);

            $policy = DB::table('otb_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['otb_policy_id'])
                ->first();

            abort_if($policy === null, 404);
            abort_unless($policy->status === 'ACTIVE', 422, 'The selected OTB policy is not active.');
            abort_unless($data['period_start'] >= $policy->effective_from, 422, 'The period starts before the policy effective date.');
            abort_unless(
                $policy->effective_to === null || $data['period_end'] <= $policy->effective_to,
                422,
                'The period ends after the policy effective date.',
            );

            $rules = json_decode($policy->inclusion_rules, true, 512, JSON_THROW_ON_ERROR);
            $input = [
                'beginning_inventory' => $this->decimal($data['beginning_inventory']),
                'planned_sales' => $this->decimal($data['planned_sales']),
                'planned_markdowns' => $this->decimal($data['planned_markdowns']),
                'planned_ending_inventory' => $this->decimal($data['planned_ending_inventory']),
                'on_order_purchases' => $this->decimal($data['on_order_purchases']),
                'commitments' => $this->decimal($data['commitments']),
                'actual_receipts' => $this->decimal($data['actual_receipts']),
            ];
            $calculation = $this->calculate($input, $rules);

            DB::table('otb_periods')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'otb_policy_id' => $data['otb_policy_id'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'currency' => $data['currency'],
                ...$data,
                'available_budget' => $calculation['available_budget'],
                'status' => 'DRAFT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'OTB_PERIOD_CREATED', 'OTB_PERIOD', $id, 'Draft OTB period calculated from a single approved valuation basis.');

            return DB::table('otb_periods')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json([
            'data' => $period,
            'formula_version' => self::FORMULA_VERSION,
        ], 201);
    }

    public function simulateScenario(string $periodId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'range_plan_id' => [
                'nullable',
                'uuid',
                Rule::exists('range_plans', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('status', 'FROZEN')),
            ],
            'forecast_run_id' => [
                'nullable',
                'uuid',
                Rule::exists('demand_forecast_runs', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('status', 'PUBLISHED')),
            ],
            'assumptions' => ['required', 'array'],
            'assumptions.beginning_inventory' => ['sometimes', 'numeric', 'min:0'],
            'assumptions.planned_sales' => ['sometimes', 'numeric', 'min:0'],
            'assumptions.planned_markdowns' => ['sometimes', 'numeric', 'min:0'],
            'assumptions.planned_ending_inventory' => ['sometimes', 'numeric', 'min:0'],
            'assumptions.on_order_purchases' => ['sometimes', 'numeric', 'min:0'],
            'assumptions.commitments' => ['sometimes', 'numeric', 'min:0'],
            'assumptions.actual_receipts' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        $scenario = DB::transaction(function () use ($tenantId, $periodId, $data, $id, $actor): object {
            $period = DB::table('otb_periods')
                ->where('tenant_id', $tenantId)
                ->where('id', $periodId)
                ->first();

            abort_if($period === null, 404);

            $policy = DB::table('otb_policies')
                ->where('tenant_id', $tenantId)
                ->where('id', $period->otb_policy_id)
                ->first();

            abort_if($policy === null, 404);
            abort_unless($policy->status === 'ACTIVE', 422, 'The OTB policy is no longer active.');

            $input = [
                'beginning_inventory' => $this->decimal($period->beginning_inventory),
                'planned_sales' => $this->decimal($period->planned_sales),
                'planned_markdowns' => $this->decimal($period->planned_markdowns),
                'planned_ending_inventory' => $this->decimal($period->planned_ending_inventory),
                'on_order_purchases' => $this->decimal($period->on_order_purchases),
                'commitments' => $this->decimal($period->commitments),
                'actual_receipts' => $this->decimal($period->actual_receipts),
            ];

            foreach ($data['assumptions'] as $key => $value) {
                $input[$key] = $this->decimal($value);
            }

            $rules = json_decode($policy->inclusion_rules, true, 512, JSON_THROW_ON_ERROR);
            $calculation = $this->calculate($input, $rules);
            $result = [
                'formula_version' => self::FORMULA_VERSION,
                'budget_basis' => $policy->budget_basis,
                'valuation_basis' => $policy->valuation_basis,
                'components' => $input,
                'inclusion_rules' => $rules,
                'calculation' => $calculation,
                'warning' => 'A scenario is a planning simulation only; it is not a purchase authorization or procurement commitment.',
            ];

            DB::table('otb_scenarios')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'otb_period_id' => $periodId,
                'range_plan_id' => $data['range_plan_id'] ?? null,
                'forecast_run_id' => $data['forecast_run_id'] ?? null,
                'assumptions' => json_encode($data['assumptions'], JSON_THROW_ON_ERROR),
                'result' => json_encode($result, JSON_THROW_ON_ERROR),
                'status' => 'SIMULATED',
                'created_by' => $actor,
                'created_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'OTB_SCENARIO_SIMULATED', 'OTB_SCENARIO', $id, 'Scenario saved as a non-posting simulation.');

            return DB::table('otb_scenarios')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });

        return response()->json(['data' => $scenario], 201);
    }

    public function approveScenario(string $scenarioId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'A tenant administrator must approve an OTB scenario.');

        DB::transaction(function () use ($tenantId, $scenarioId, $approver): void {
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', ['otb-scenario:'.$tenantId.':'.$scenarioId]);

            $scenario = DB::table('otb_scenarios')
                ->where('tenant_id', $tenantId)
                ->where('id', $scenarioId)
                ->first();

            abort_if($scenario === null, 404);
            abort_unless($scenario->created_by !== $approver, 403, 'The scenario author cannot approve their own scenario.');

            $alreadyApproved = DB::table('merchandising_decisions')
                ->where('tenant_id', $tenantId)
                ->where('object_type', 'OTB_SCENARIO')
                ->where('object_id', $scenarioId)
                ->where('decision', 'APPROVE_SCENARIO')
                ->exists();

            abort_unless(! $alreadyApproved, 422, 'This OTB scenario has already been approved.');

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'OTB_SCENARIO',
                'object_id' => $scenarioId,
                'object_version' => 1,
                'decision' => 'APPROVE_SCENARIO',
                'reason' => 'OTB scenario independently approved for planning use only.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);

            $this->audit($tenantId, $approver, 'OTB_SCENARIO_APPROVED', 'OTB_SCENARIO', $scenarioId, 'Approval does not create a purchase order or accounting entry.');
        });

        return response()->json(['status' => 'APPROVED_FOR_PLANNING_ONLY']);
    }

    private function calculate(array $input, array $rules): array
    {
        $commitmentsDeduction = ($rules['commitments_mode'] ?? 'ADDITIONAL') === 'ADDITIONAL'
            ? $input['commitments']
            : '0.0000';
        $receiptDeduction = ($rules['actual_receipts_reduce_otb'] ?? true) === true
            ? $input['actual_receipts']
            : '0.0000';

        // PostgreSQL NUMERIC performs the calculation without a binary-float
        // conversion; values remain exact to the four-decimal ledger scale.
        $result = DB::selectOne(
            'SELECT (
                ?::numeric + ?::numeric + ?::numeric
                - ?::numeric - ?::numeric - ?::numeric - ?::numeric
            )::numeric(20,4) AS available_budget',
            [
                $input['planned_ending_inventory'],
                $input['planned_sales'],
                $input['planned_markdowns'],
                $input['beginning_inventory'],
                $input['on_order_purchases'],
                $commitmentsDeduction,
                $receiptDeduction,
            ],
        );

        return [
            'available_budget' => (string) $result->available_budget,
            'interpretation' => 'Planned ending inventory + planned sales + planned markdowns - beginning inventory - open on-order purchases, adjusted for separately identified commitments and actual receipts according to the selected policy.',
        ];
    }

    private function decimal(int|float|string $value): string
    {
        $raw = trim((string) $value);

        if (! preg_match('/^-?\\d+(?:\\.\\d{1,4})?$/', $raw)) {
            abort(422, 'OTB values must be numeric with at most four decimal places.');
        }

        $negative = str_starts_with($raw, '-');
        $unsigned = ltrim($raw, '-');
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $whole = ltrim($whole, '0');
        $whole = $whole === '' ? '0' : $whole;
        abort_unless(strlen($whole) <= 16, 422, 'OTB values exceed the supported numeric precision.');
        $normalized = $whole.'.'.str_pad($fraction, 4, '0');

        return $negative && $normalized !== '0.0000' ? '-'.$normalized : $normalized;
    }

    private function audit(string $tenantId, string $actorId, string $action, string $objectType, string $objectId, string $reason): void
    {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'OTB_PLANNING'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
