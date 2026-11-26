<?php

namespace App\Services\Planning;

use App\Services\Support\DecimalQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IndustrialEngineeringService
{
    public function createWorkCentre(string $tenantId, string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('work_centres')->insert([
            'id' => $id, 'tenant_id' => $tenantId, 'site_id' => $data['site_id'],
            'work_centre_code' => $data['work_centre_code'],
            'department_code' => strtoupper($data['department_code']),
            'work_centre_type' => strtoupper($data['work_centre_type']),
            'name' => $data['name'],
            'machine_family' => $data['machine_family'] ?? null,
            'capabilities' => json_encode($data['capabilities'] ?? [], JSON_THROW_ON_ERROR),
            'capacity_unit' => $data['capacity_unit'] ?? 'MINUTES',
            'status' => 'ACTIVE',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('work_centres')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function createShift(string $tenantId, string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('work_shift_definitions')->insert([
            'id' => $id, 'tenant_id' => $tenantId, 'site_id' => $data['site_id'],
            'shift_code' => $data['shift_code'], 'shift_name' => $data['shift_name'],
            'starts_at' => $data['starts_at'], 'ends_at' => $data['ends_at'],
            'unpaid_break_minutes' => $data['unpaid_break_minutes'] ?? 0,
            'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('work_shift_definitions')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function createCapacitySlot(string $tenantId, string $actorId, array $data): object
    {
        $centre = DB::table('work_centres')->where('tenant_id', $tenantId)->where('id', $data['work_centre_id'])->where('status', 'ACTIVE')->first();
        $shift = DB::table('work_shift_definitions')->where('tenant_id', $tenantId)->where('id', $data['shift_id'])->where('active', true)->first();
        if ($centre === null || $shift === null) {
            throw ValidationException::withMessages(['capacity' => 'An active tenant work centre and shift are required.']);
        }

        $id = (string) Str::uuid();
        DB::table('capacity_calendar_slots')->insert([
            'id' => $id, 'tenant_id' => $tenantId,
            'work_centre_id' => $centre->id, 'shift_id' => $shift->id,
            'work_date' => $data['work_date'],
            'available_minutes' => $data['available_minutes'],
            'planned_maintenance_minutes' => $data['planned_maintenance_minutes'] ?? 0,
            'reserved_minutes' => 0, 'status' => 'OPEN',
            'capacity_basis_reference' => $data['capacity_basis_reference'] ?? null,
            'created_by' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('capacity_calendar_slots')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function createStandardVersion(string $tenantId, string $actorId, array $data): object
    {
        $routeStep = DB::table('process_route_steps')->where('tenant_id', $tenantId)
            ->where('route_version_id', $data['route_version_id'])->where('id', $data['route_step_id'])->first();
        $route = DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $data['route_version_id'])->first();
        if ($route === null || $routeStep === null || $route->style_id !== $data['style_id'] ||
            (int) $route->style_revision_no !== (int) $data['style_revision_no']) {
            throw ValidationException::withMessages(['route_step_id' => 'The industrial engineering standard must reference a step on the selected style revision route.']);
        }

        $id = (string) Str::uuid();
        DB::table('operation_standard_versions')->insert([
            'id' => $id, 'tenant_id' => $tenantId, 'style_id' => $data['style_id'],
            'style_revision_no' => $data['style_revision_no'],
            'route_version_id' => $data['route_version_id'], 'route_step_id' => $data['route_step_id'],
            'version_no' => $data['version_no'], 'standard_minutes' => $data['standard_minutes'],
            'observed_cycle_count' => $data['observed_cycle_count'], 'rating_basis' => $data['rating_basis'],
            'allowance_basis' => $data['allowance_basis'],
            'study_evidence' => json_encode($data['study_evidence'], JSON_THROW_ON_ERROR),
            'method_revision_reference' => $data['method_revision_reference'],
            'ergonomic_risk_review' => $data['ergonomic_risk_review'] ?? 'PENDING',
            'status' => 'DRAFT', 'effective_from' => $data['effective_from'] ?? null,
            'created_by' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('operation_standard_versions')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function approveStandardVersion(string $tenantId, string $approverId, string $standardId, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $approverId, $standardId, $reason): object {
            $standard = DB::table('operation_standard_versions')->where('tenant_id', $tenantId)->where('id', $standardId)->lockForUpdate()->first();
            if ($standard === null || $standard->status !== 'DRAFT' || $standard->created_by === $approverId) {
                throw ValidationException::withMessages(['standard' => 'A draft standard requires independent IE approval.']);
            }
            if ($standard->ergonomic_risk_review !== 'ACCEPTED' || trim($reason) === '') {
                throw ValidationException::withMessages(['standard' => 'Accepted ergonomic review and an approval reason are required.']);
            }

            DB::table('operation_standard_versions')->where('tenant_id', $tenantId)->where('id', $standardId)->update([
                'status' => 'APPROVED', 'approved_by' => $approverId, 'approved_at' => now(),
                'decision_reason' => $reason, 'updated_at' => now(),
            ]);

            return DB::table('operation_standard_versions')->where('tenant_id', $tenantId)->where('id', $standardId)->first();
        }, 3);
    }

    public function createPlanVersion(string $tenantId, string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('production_plan_versions')->insert([
            'id' => $id, 'tenant_id' => $tenantId,
            'plan_code' => $data['plan_code'], 'version_no' => $data['version_no'],
            'horizon_start' => $data['horizon_start'], 'horizon_end' => $data['horizon_end'],
            'status' => 'DRAFT',
            'planning_basis' => json_encode($data['planning_basis'] ?? [], JSON_THROW_ON_ERROR),
            'assumptions' => $data['assumptions'] ?? null,
            'created_by' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('production_plan_versions')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function addPlanLine(string $tenantId, string $actorId, string $planId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $planId, $data): object {
            $plan = DB::table('production_plan_versions')->where('tenant_id', $tenantId)->where('id', $planId)->lockForUpdate()->first();
            if ($plan === null || $plan->status !== 'DRAFT' || $plan->created_by !== $actorId) {
                throw ValidationException::withMessages(['plan' => 'Only the plan creator can edit a draft plan.']);
            }

            $slot = DB::table('capacity_calendar_slots')->where('tenant_id', $tenantId)
                ->where('id', $data['capacity_slot_id'])->lockForUpdate()->first();
            if ($slot === null || $slot->status !== 'OPEN' ||
                $slot->work_centre_id !== $data['work_centre_id'] ||
                $slot->work_date !== $data['planned_start_date']) {
                throw ValidationException::withMessages(['capacity_slot_id' => 'The selected capacity slot must be open and match the work centre and planned date.']);
            }

            $operation = DB::table('production_order_operations')->where('tenant_id', $tenantId)
                ->where('id', $data['operation_id'])->where('production_order_id', $data['production_order_id'])->first();
            $productionOrder = DB::table('production_orders')->where('tenant_id', $tenantId)
                ->where('id', $data['production_order_id'])->first();
            if ($operation === null || $productionOrder === null ||
                ! in_array($operation->status, ['QUEUED', 'READY'], true)) {
                throw ValidationException::withMessages(['operation_id' => 'A queued operation belonging to the selected production order is required.']);
            }

            $routeStep = DB::table('process_route_steps')->where('tenant_id', $tenantId)
                ->where('route_version_id', $operation->route_version_id)->where('id', $operation->route_step_id)->first();
            if ($routeStep === null) {
                throw ValidationException::withMessages(['operation_id' => 'The route step for this production operation is missing.']);
            }

            $standard = DB::table('operation_standard_versions')->where('tenant_id', $tenantId)
                ->where('style_id', $productionOrder->style_id)
                ->where('style_revision_no', $productionOrder->style_revision_no)
                ->where('route_version_id', $operation->route_version_id)
                ->where('route_step_id', $operation->route_step_id)
                ->where('status', 'APPROVED')
                ->where(function ($query) use ($plan): void {
                    $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $plan->horizon_start);
                })
                ->orderByDesc('version_no')->first();

            if ($standard !== null) {
                $expectedMinutesUnits = intdiv(
                    DecimalQuantity::units((string) $standard->standard_minutes)
                        * DecimalQuantity::units((string) $data['planned_quantity']),
                    1_000_000
                );
                $actualMinutesUnits = DecimalQuantity::units((string) $data['planned_minutes']);
                if ($actualMinutesUnits < $expectedMinutesUnits) {
                    throw ValidationException::withMessages(['planned_minutes' => 'Planned minutes cannot be lower than the approved SMV multiplied by planned quantity.']);
                }
            }

            $shift = DB::table('work_shift_definitions')->where('tenant_id', $tenantId)->where('id', $slot->shift_id)->first();
            $start = new \DateTimeImmutable($data['planned_start_at']);
            $end = new \DateTimeImmutable($data['planned_end_at']);
            $shiftStart = new \DateTimeImmutable($slot->work_date.' '.$shift->starts_at);
            $shiftEndsNextDay = $shift->ends_at < $shift->starts_at;
            $shiftEndDate = $shiftEndsNextDay
                ? (new \DateTimeImmutable($slot->work_date))->modify('+1 day')->format('Y-m-d')
                : $slot->work_date;
            $shiftEnd = new \DateTimeImmutable($shiftEndDate.' '.$shift->ends_at);
            if ($start < $shiftStart || $end > $shiftEnd || $end <= $start ||
                $start->format('Y-m-d') !== $slot->work_date) {
                throw ValidationException::withMessages(['planned_start_at' => 'The scheduled interval must fit inside the selected work-centre shift slot.']);
            }

            $netCapacityUnits = ($slot->available_minutes - $slot->planned_maintenance_minutes) * 1_000_000;
            $reservedUnits = (int) $slot->reserved_minutes * 1_000_000;
            $plannedUnits = DecimalQuantity::units((string) $data['planned_minutes']);
            if ($plannedUnits <= 0 || $reservedUnits + $plannedUnits > $netCapacityUnits) {
                throw ValidationException::withMessages(['planned_minutes' => 'The plan would exceed net available capacity for this slot.']);
            }

            $id = (string) Str::uuid();
            DB::table('production_plan_lines')->insert([
                'id' => $id, 'tenant_id' => $tenantId,
                'plan_version_id' => $planId, 'production_order_id' => $data['production_order_id'],
                'operation_id' => $operation->id, 'work_centre_id' => $data['work_centre_id'],
                'capacity_slot_id' => $slot->id, 'sequence_no' => $data['sequence_no'],
                'planned_quantity' => $data['planned_quantity'], 'planned_minutes' => $data['planned_minutes'],
                'planned_start_at' => $data['planned_start_at'], 'planned_end_at' => $data['planned_end_at'],
                'bottleneck_flag' => (bool) ($data['bottleneck_flag'] ?? false),
                'schedule_reason' => $data['schedule_reason'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('capacity_calendar_slots')->where('tenant_id', $tenantId)->where('id', $slot->id)->update([
                'reserved_minutes' => (int) $slot->reserved_minutes + (int) ceil(DecimalQuantity::units((string) $data['planned_minutes']) / 1_000_000),
                'updated_at' => now(),
            ]);

            return DB::table('production_plan_lines')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function approvePlan(string $tenantId, string $approverId, string $planId): object
    {
        return DB::transaction(function () use ($tenantId, $approverId, $planId): object {
            $plan = DB::table('production_plan_versions')->where('tenant_id', $tenantId)->where('id', $planId)->lockForUpdate()->first();
            if ($plan === null || ! in_array($plan->status, ['DRAFT', 'SIMULATED'], true) || $plan->created_by === $approverId) {
                throw ValidationException::withMessages(['plan' => 'A draft or simulated plan requires independent approval.']);
            }
            if (! DB::table('production_plan_lines')->where('tenant_id', $tenantId)->where('plan_version_id', $planId)->exists()) {
                throw ValidationException::withMessages(['plan_lines' => 'A production plan requires at least one scheduled operation.']);
            }

            DB::table('production_plan_versions')->where('tenant_id', $tenantId)->where('id', $planId)->update([
                'status' => 'APPROVED', 'approved_by' => $approverId, 'approved_at' => now(), 'updated_at' => now(),
            ]);

            return DB::table('production_plan_versions')->where('tenant_id', $tenantId)->where('id', $planId)->first();
        }, 3);
    }

    public function recordWorkerSkill(string $tenantId, string $assessorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('workforce_skill_records')->insert([
            'id' => $id, 'tenant_id' => $tenantId,
            'worker_reference' => $data['worker_reference'], 'skill_code' => $data['skill_code'],
            'skill_grade' => $data['skill_grade'], 'assessment_result' => strtoupper($data['assessment_result']),
            'assessment_evidence' => json_encode($data['assessment_evidence'], JSON_THROW_ON_ERROR),
            'assessed_by' => $assessorId, 'assessed_at' => now(),
            'valid_from' => $data['valid_from'], 'valid_until' => $data['valid_until'] ?? null,
            'authorized_operation_scope' => json_encode($data['authorized_operation_scope'] ?? [], JSON_THROW_ON_ERROR),
            'status' => $data['assessment_result'] === 'COMPETENT' ? 'ACTIVE' : 'SUSPENDED',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('workforce_skill_records')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function assignWorkerToOperation(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $skill = DB::table('workforce_skill_records')->where('tenant_id', $tenantId)
                ->where('id', $data['skill_record_id'])->lockForUpdate()->first();
            if ($skill === null || $skill->worker_reference !== $data['worker_reference'] ||
                $skill->status !== 'ACTIVE' || $skill->assessment_result !== 'COMPETENT' ||
                $skill->valid_from > substr($data['planned_start_at'], 0, 10) ||
                ($skill->valid_until !== null && $skill->valid_until < substr($data['planned_end_at'], 0, 10))) {
                throw ValidationException::withMessages(['skill_record_id' => 'A current competent worker skill record is required for the full assignment period.']);
            }

            $operation = DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $data['operation_id'])->first();
            if ($operation === null) {
                throw ValidationException::withMessages(['operation_id' => 'The production operation is not available in this tenant.']);
            }
            $routeStep = DB::table('process_route_steps')->where('tenant_id', $tenantId)
                ->where('route_version_id', $operation->route_version_id)->where('id', $operation->route_step_id)->first();
            if ($routeStep?->required_skill_code !== null && $routeStep->required_skill_code !== $skill->skill_code) {
                throw ValidationException::withMessages(['skill_record_id' => 'The worker does not hold the exact skill required by this operation.']);
            }

            // Serialize assignments for a tenant worker, independent of which skill row is used.
            DB::selectOne('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', [$tenantId.':'.$data['worker_reference']]);

            $centre = DB::table('work_centres')->where('tenant_id', $tenantId)
                ->where('id', $data['work_centre_id'])->where('status', 'ACTIVE')->first();
            $shift = DB::table('work_shift_definitions')->where('tenant_id', $tenantId)
                ->where('id', $data['shift_id'])->where('active', true)->first();
            if ($centre === null || $shift === null || $centre->site_id !== $shift->site_id) {
                throw ValidationException::withMessages(['work_centre_id' => 'An active work centre and a shift at the same site are required.']);
            }
            $shiftStartClock = new \DateTimeImmutable('1970-01-01 '.$shift->starts_at);
            $shiftEndClock = new \DateTimeImmutable('1970-01-01 '.$shift->ends_at);
            if ($shiftEndClock <= $shiftStartClock) {
                $shiftEndClock = $shiftEndClock->modify('+1 day');
            }
            $shiftDuration = (int) (($shiftEndClock->getTimestamp() - $shiftStartClock->getTimestamp()) / 60);
            if (DecimalQuantity::units((string) $data['planned_minutes']) >
                DecimalQuantity::units((string) max(0, $shiftDuration - (int) $shift->unpaid_break_minutes))) {
                throw ValidationException::withMessages(['planned_minutes' => 'Worker assignment exceeds net shift minutes.']);
            }

            $overlap = DB::table('workforce_assignments')->where('tenant_id', $tenantId)
                ->where('worker_reference', $data['worker_reference'])
                ->whereIn('assignment_status', ['CONFIRMED', 'STARTED'])
                ->where('planned_start_at', '<', $data['planned_end_at'])
                ->where('planned_end_at', '>', $data['planned_start_at'])
                ->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['worker_reference' => 'The worker already has an overlapping confirmed operation assignment.']);
            }

            $id = (string) Str::uuid();
            DB::table('workforce_assignments')->insert([
                'id' => $id, 'tenant_id' => $tenantId,
                'operation_id' => $operation->id, 'work_centre_id' => $data['work_centre_id'],
                'shift_id' => $data['shift_id'], 'skill_record_id' => $skill->id,
                'worker_reference' => $data['worker_reference'],
                'assignment_status' => 'PLANNED',
                'planned_start_at' => $data['planned_start_at'], 'planned_end_at' => $data['planned_end_at'],
                'planned_minutes' => $data['planned_minutes'],
                'created_by' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);

            return DB::table('workforce_assignments')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function confirmWorkerAssignment(string $tenantId, string $actorId, string $assignmentId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $assignmentId): object {
            $assignment = DB::table('workforce_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->lockForUpdate()->first();
            if ($assignment === null || $assignment->assignment_status !== 'PLANNED' || $assignment->created_by === $actorId) {
                throw ValidationException::withMessages(['assignment' => 'A planned workforce assignment requires independent supervisor confirmation.']);
            }
            DB::table('workforce_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->update([
                'assignment_status' => 'CONFIRMED', 'approved_by' => $actorId, 'updated_at' => now(),
            ]);

            return DB::table('workforce_assignments')->where('tenant_id', $tenantId)->where('id', $assignmentId)->first();
        }, 3);
    }
}
