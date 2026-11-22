<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DemandPlanningController extends Controller
{
    public function runs(TenantContext $tenantContext): JsonResponse
    {
        return response()->json([
            'data' => DB::table('demand_forecast_runs')
                ->where('tenant_id', $tenantContext->tenantId())
                ->orderByDesc('created_at')
                ->paginate(50),
        ]);
    }

    public function showRun(string $runId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $run = DB::table('demand_forecast_runs')
            ->where('tenant_id', $tenantId)
            ->where('id', $runId)
            ->first();

        abort_if($run === null, 404);

        $forecasts = DB::table('demand_forecasts')
            ->where('tenant_id', $tenantId)
            ->where('forecast_run_id', $runId)
            ->orderBy('time_bucket')
            ->get();

        $adjustments = DB::table('forecast_adjustments as a')
            ->leftJoin('merchandising_decisions as d', function ($join): void {
                $join->on('d.tenant_id', '=', 'a.tenant_id')
                    ->on('d.object_id', '=', 'a.id')
                    ->where('d.object_type', '=', 'FORECAST_ADJUSTMENT')
                    ->where('d.decision', '=', 'APPROVE_ADJUSTMENT');
            })
            ->where('a.tenant_id', $tenantId)
            ->whereIn('a.forecast_id', $forecasts->pluck('id'))
            ->orderByDesc('a.created_at')
            ->select(['a.*', DB::raw("CASE WHEN d.id IS NULL THEN false ELSE true END AS is_approved")])
            ->get();

        return response()->json([
            'data' => $run,
            'forecasts' => $forecasts,
            'adjustments' => $adjustments,
        ]);
    }

    public function publishRun(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'grain' => ['required', Rule::in(['STYLE_WEEK', 'STYLE_MONTH', 'CATEGORY_WEEK', 'CATEGORY_MONTH', 'CHANNEL_CATEGORY_WEEK', 'ACCOUNT_STYLE_WEEK', 'STORE_STYLE_WEEK'])],
            'method' => ['required', Rule::in(['MANUAL_CONSENSUS', 'STATISTICAL_BASELINE', 'EXTERNAL_MODEL_IMPORT'])],
            'model_version' => ['nullable', 'string', 'max:80'],
            'horizon' => ['required', 'string', 'max:80'],
            'scenario_code' => ['required', 'string', 'max:80'],
            'forecast_start' => ['required', 'date_format:Y-m-d'],
            'forecast_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:forecast_start'],
            'assumptions' => ['required', 'array'],
            'forecasts' => ['required', 'array', 'min:1', 'max:1000'],
            'forecasts.*.dimension_refs' => ['required', 'array'],
            'forecasts.*.time_bucket' => ['required', 'date_format:Y-m-d'],
            'forecasts.*.forecast_quantity' => ['required', 'numeric', 'min:0'],
            'forecasts.*.forecast_value' => ['nullable', 'numeric', 'min:0'],
            'forecasts.*.currency' => ['nullable', 'string', 'size:3', 'alpha:upper'],
            'forecasts.*.confidence_low' => ['nullable', 'numeric', 'min:0'],
            'forecasts.*.confidence_high' => ['nullable', 'numeric', 'min:0'],
        ]);

        $seen = [];
        foreach ($data['forecasts'] as $index => $forecast) {
            abort_unless(
                $forecast['time_bucket'] >= $data['forecast_start'] && $forecast['time_bucket'] <= $data['forecast_end'],
                422,
                'Every forecast time bucket must fall within the run horizon.',
            );

            if (isset($forecast['confidence_low'], $forecast['confidence_high'])) {
                abort_unless(
                    (float) $forecast['confidence_high'] >= (float) $forecast['confidence_low'],
                    422,
                    'Forecast confidence upper bound cannot be lower than its lower bound.',
                );
            }

            $dimensions = $forecast['dimension_refs'];
            $this->sortRecursive($dimensions);
            $key = $forecast['time_bucket'].'|'.json_encode($dimensions, JSON_THROW_ON_ERROR);
            abort_unless(! isset($seen[$key]), 422, 'A forecast run cannot contain duplicate dimension/time-bucket rows.');
            $seen[$key] = true;
        }

        $runId = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();

        DB::transaction(function () use ($tenantId, $data, $runId, $actor): void {
            DB::table('demand_forecast_runs')->insert([
                'id' => $runId,
                'tenant_id' => $tenantId,
                'grain' => $data['grain'],
                'method' => $data['method'],
                'model_version' => $data['model_version'] ?? null,
                'horizon' => $data['horizon'],
                'scenario_code' => trim($data['scenario_code']),
                'forecast_start' => $data['forecast_start'],
                'forecast_end' => $data['forecast_end'],
                'status' => 'PUBLISHED',
                'assumptions' => json_encode($data['assumptions'], JSON_THROW_ON_ERROR),
                'created_by' => $actor,
                'created_at' => now(),
            ]);

            $rows = [];
            foreach ($data['forecasts'] as $forecast) {
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'forecast_run_id' => $runId,
                    'dimension_refs' => json_encode($forecast['dimension_refs'], JSON_THROW_ON_ERROR),
                    'time_bucket' => $forecast['time_bucket'],
                    'forecast_quantity' => $forecast['forecast_quantity'],
                    'forecast_value' => $forecast['forecast_value'] ?? null,
                    'currency' => $forecast['currency'] ?? null,
                    'confidence_low' => $forecast['confidence_low'] ?? null,
                    'confidence_high' => $forecast['confidence_high'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('demand_forecasts')->insert($rows);
            $this->audit($tenantId, $actor, 'DEMAND_FORECAST_PUBLISHED', 'FORECAST_RUN', $runId, 'Forecast run and all forecast rows published atomically.');
        });

        return response()->json([
            'data' => DB::table('demand_forecast_runs')->where('tenant_id', $tenantId)->where('id', $runId)->first(),
            'row_count' => count($data['forecasts']),
        ], 201);
    }

    public function createAdjustment(string $forecastId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'baseline_value' => ['required', 'numeric', 'min:0'],
            'override_value' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'evidence_reference' => ['nullable', 'string', 'max:1000'],
        ]);

        $forecast = DB::table('demand_forecasts')
            ->where('tenant_id', $tenantId)
            ->where('id', $forecastId)
            ->first();

        abort_if($forecast === null, 404);
        abort_unless(
            round((float) $data['baseline_value'], 4) === round((float) $forecast->forecast_quantity, 4),
            422,
            'The adjustment baseline must match the published forecast quantity.',
        );

        $id = (string) Str::uuid();
        $actor = (string) $request->user()->getAuthIdentifier();
        $baseline = round((float) $data['baseline_value'], 4);
        $override = round((float) $data['override_value'], 4);

        DB::transaction(function () use ($tenantId, $data, $id, $actor, $forecastId, $baseline, $override): void {
            DB::table('forecast_adjustments')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'forecast_id' => $forecastId,
                'baseline_value' => number_format($baseline, 4, '.', ''),
                'override_value' => number_format($override, 4, '.', ''),
                'delta_value' => number_format($override - $baseline, 4, '.', ''),
                'reason' => trim($data['reason']),
                'evidence_reference' => $data['evidence_reference'] ?? null,
                'authored_by' => $actor,
                'approved_by' => null,
                'created_at' => now(),
            ]);

            $this->audit($tenantId, $actor, 'FORECAST_ADJUSTMENT_CREATED', 'FORECAST_ADJUSTMENT', $id, $data['reason']);
        });

        return response()->json([
            'data' => DB::table('forecast_adjustments')->where('tenant_id', $tenantId)->where('id', $id)->first(),
            'approval_status' => 'PENDING',
        ], 201);
    }

    public function approveAdjustment(string $adjustmentId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $approver = (string) $request->user()->getAuthIdentifier();
        abort_unless($tenantContext->canAdministerTenant(), 403, 'A tenant administrator must approve forecast adjustments.');

        DB::transaction(function () use ($tenantId, $adjustmentId, $approver): void {
            DB::select("SELECT pg_advisory_xact_lock(hashtext(?))", ['forecast-adjustment:'.$tenantId.':'.$adjustmentId]);

            $adjustment = DB::table('forecast_adjustments')
                ->where('tenant_id', $tenantId)
                ->where('id', $adjustmentId)
                ->first();

            abort_if($adjustment === null, 404);
            abort_unless($adjustment->authored_by !== $approver, 403, 'The adjustment author cannot approve their own forecast adjustment.');

            $alreadyApproved = DB::table('merchandising_decisions')
                ->where('tenant_id', $tenantId)
                ->where('object_type', 'FORECAST_ADJUSTMENT')
                ->where('object_id', $adjustmentId)
                ->where('decision', 'APPROVE_ADJUSTMENT')
                ->exists();

            abort_unless(! $alreadyApproved, 422, 'This forecast adjustment has already been approved.');

            DB::table('merchandising_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'object_type' => 'FORECAST_ADJUSTMENT',
                'object_id' => $adjustmentId,
                'object_version' => 1,
                'decision' => 'APPROVE_ADJUSTMENT',
                'reason' => 'Forecast adjustment independently approved.',
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $approver,
                'decided_at' => now(),
            ]);

            $this->audit($tenantId, $approver, 'FORECAST_ADJUSTMENT_APPROVED', 'FORECAST_ADJUSTMENT', $adjustmentId, 'Independent approval; published baseline remains immutable.');
        });

        return response()->json([
            'data' => DB::table('forecast_adjustments')->where('tenant_id', $tenantId)->where('id', $adjustmentId)->first(),
            'approval_status' => 'APPROVED',
        ]);
    }

    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursive($item);
            }
        }

        if (! array_is_list($value)) {
            ksort($value);
        }
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
            'scope' => json_encode(['module' => 'DEMAND_PLANNING'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
