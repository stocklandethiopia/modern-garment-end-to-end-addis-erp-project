<?php

namespace App\Services\Manufacturing;

use App\Services\Support\DecimalQuantity;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManufacturingExecutionService
{
    public function createRouteVersion(string $tenantId, string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('process_route_versions')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'style_id' => $data['style_id'],
            'style_revision_no' => $data['style_revision_no'],
            'version_no' => $data['version_no'],
            'route_code' => $data['route_code'],
            'route_reason' => $data['route_reason'],
            'status' => 'DRAFT',
            'effective_from' => $data['effective_from'] ?? null,
            'created_by' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function addRouteSteps(string $tenantId, string $routeId, array $steps): array
    {
        return DB::transaction(function () use ($tenantId, $routeId, $steps): array {
            $route = DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->lockForUpdate()->first();
            if ($route === null || $route->status !== 'DRAFT') {
                throw ValidationException::withMessages(['route' => 'Route steps can only be changed on a draft route version.']);
            }
            if (DB::table('process_route_steps')->where('tenant_id', $tenantId)->where('route_version_id', $routeId)->exists()) {
                throw ValidationException::withMessages(['steps' => 'This route version already has steps. Create a new draft version instead of replacing released route history.']);
            }

            $created = [];
            foreach ($steps as $index => $step) {
                $stepNo = $index + 1;
                $predecessor = $step['predecessor_step_no'] ?? ($stepNo > 1 ? $stepNo - 1 : null);
                if ($predecessor !== null && ($predecessor < 1 || $predecessor >= $stepNo)) {
                    throw ValidationException::withMessages(['predecessor_step_no' => 'A route dependency must reference an earlier step in this version.']);
                }
                if (($step['is_optional'] ?? false) && empty($step['condition_expression'])) {
                    throw ValidationException::withMessages(['condition_expression' => 'Optional route steps require an explicit condition.']);
                }
                if (($step['department_code'] ?? null) === 'SUBCONTRACT' && ! ($step['subcontract_allowed'] ?? false)) {
                    throw ValidationException::withMessages(['subcontract_allowed' => 'A subcontract step must explicitly enable subcontract execution.']);
                }

                $id = (string) Str::uuid();
                DB::table('process_route_steps')->insert([
                    'id' => $id,
                    'tenant_id' => $tenantId,
                    'route_version_id' => $routeId,
                    'step_no' => $stepNo,
                    'step_code' => $step['step_code'],
                    'department_code' => $step['department_code'],
                    'operation_name' => $step['operation_name'],
                    'process_type' => $step['process_type'],
                    'is_optional' => (bool) ($step['is_optional'] ?? false),
                    'condition_expression' => json_encode($step['condition_expression'] ?? []),
                    'predecessor_step_no' => $predecessor,
                    'subcontract_allowed' => (bool) ($step['subcontract_allowed'] ?? false),
                    'quality_gate' => (bool) ($step['quality_gate'] ?? false),
                    'standard_minutes' => $step['standard_minutes'] ?? null,
                    'required_skill_code' => $step['required_skill_code'] ?? null,
                    'work_instruction_reference' => $step['work_instruction_reference'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created[] = $id;
            }

            return DB::table('process_route_steps')->where('tenant_id', $tenantId)->where('route_version_id', $routeId)->orderBy('step_no')->get()->all();
        }, 3);
    }

    public function submitRoute(string $tenantId, string $actorId, string $routeId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $routeId): object {
            $route = DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->lockForUpdate()->first();
            if ($route === null || $route->status !== 'DRAFT') {
                throw ValidationException::withMessages(['route' => 'Only a draft route can be submitted.']);
            }
            $steps = DB::table('process_route_steps')->where('tenant_id', $tenantId)->where('route_version_id', $routeId)->orderBy('step_no')->get();
            if ($steps->isEmpty() || $steps->first()->step_no !== 1) {
                throw ValidationException::withMessages(['steps' => 'A route requires a complete ordered operation sequence beginning at step 1.']);
            }
            foreach ($steps as $index => $step) {
                if ($step->step_no !== $index + 1) {
                    throw ValidationException::withMessages(['steps' => 'Route step numbers must be contiguous.']);
                }
            }

            DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->update([
                'status' => 'SUBMITTED',
                'submitted_by' => $actorId,
                'submitted_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->first();
        }, 3);
    }

    public function decideRoute(string $tenantId, string $actorId, string $routeId, bool $approve, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $routeId, $approve, $reason): object {
            $route = DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->lockForUpdate()->first();
            if ($route === null || $route->status !== 'SUBMITTED') {
                throw ValidationException::withMessages(['route' => 'Only a submitted route version can be decided.']);
            }
            if ($route->created_by === $actorId || $route->submitted_by === $actorId) {
                throw ValidationException::withMessages(['approver' => 'The route author or submitter cannot approve their own route.']);
            }
            if (trim($reason) === '') {
                throw ValidationException::withMessages(['decision_reason' => 'A route decision requires a reason.']);
            }

            DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->update([
                'status' => $approve ? 'APPROVED' : 'REJECTED',
                'decided_by' => $actorId,
                'decided_at' => now(),
                'decision_reason' => $reason,
                'updated_at' => now(),
            ]);

            return DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $routeId)->first();
        }, 3);
    }

    public function createProductionOrder(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $techPack = DB::table('tech_pack_versions')->where('tenant_id', $tenantId)->where('id', $data['tech_pack_version_id'])->first();
            if ($techPack === null || ! in_array($techPack->status, ['APPROVED', 'RELEASED'], true)) {
                throw ValidationException::withMessages(['tech_pack_version_id' => 'Production requires an approved, released technical pack version.']);
            }

            $route = DB::table('process_route_versions')->where('tenant_id', $tenantId)
                ->where('id', $data['route_version_id'])
                ->where('style_id', $techPack->style_id)
                ->where('style_revision_no', $techPack->style_revision_no)
                ->where('status', 'APPROVED')
                ->first();

            if ($route === null) {
                throw ValidationException::withMessages(['route_version_id' => 'Production requires an approved route for the same style revision as the technical pack.']);
            }

            $steps = DB::table('process_route_steps')->where('tenant_id', $tenantId)
                ->where('route_version_id', $route->id)->orderBy('step_no')->get();

            if ($steps->isEmpty()) {
                throw ValidationException::withMessages(['route_version_id' => 'The approved route contains no released process steps.']);
            }

            $id = (string) Str::uuid();
            DB::table('production_orders')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'],
                'site_id' => $data['site_id'],
                'order_code' => $data['order_code'],
                'style_id' => $techPack->style_id,
                'style_revision_no' => $techPack->style_revision_no,
                'tech_pack_version_id' => $techPack->id,
                'route_version_id' => $route->id,
                'planned_quantity' => $data['planned_quantity'],
                'unit_code' => $data['unit_code'] ?? 'PCS',
                'planned_start_date' => $data['planned_start_date'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($steps as $step) {
                DB::table('production_order_operations')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'production_order_id' => $id,
                    'route_version_id' => $route->id,
                    'route_step_id' => $step->id,
                    'step_no' => $step->step_no,
                    'step_code' => $step->step_code,
                    'department_code' => $step->department_code,
                    'operation_name' => $step->operation_name,
                    'planned_quantity' => $data['planned_quantity'],
                    'good_quantity' => 0,
                    'defective_quantity' => 0,
                    'status' => 'QUEUED',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function releaseProductionOrder(string $tenantId, string $actorId, string $orderId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderId): object {
            $order = DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'DRAFT') {
                throw ValidationException::withMessages(['production_order' => 'Only a draft production order can be released.']);
            }
            $route = DB::table('process_route_versions')->where('tenant_id', $tenantId)->where('id', $order->route_version_id)->first();
            $techPack = DB::table('tech_pack_versions')->where('tenant_id', $tenantId)->where('id', $order->tech_pack_version_id)->first();
            if ($route === null || $route->status !== 'APPROVED' || $techPack === null || ! in_array($techPack->status, ['APPROVED','RELEASED'], true)) {
                throw ValidationException::withMessages(['readiness' => 'Approved route and technical pack versions are required for production release.']);
            }
            $operationCount = DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('production_order_id', $orderId)->count();
            if ($operationCount === 0) {
                throw ValidationException::withMessages(['operations' => 'Production order contains no route operations.']);
            }

            DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->update([
                'status' => 'RELEASED',
                'released_by' => $actorId,
                'released_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function startProductionOrder(string $tenantId, string $orderId): object
    {
        return DB::transaction(function () use ($tenantId, $orderId): object {
            $order = DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'RELEASED') {
                throw ValidationException::withMessages(['production_order' => 'Only a released production order can start.']);
            }
            DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->update([
                'status' => 'IN_PROGRESS', 'started_at' => now(), 'updated_at' => now(),
            ]);
            return DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function recordOperationEvent(string $tenantId, string $actorId, string $orderId, string $operationId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderId, $operationId, $data): object {
            $existing = DB::table('production_execution_events')->where('tenant_id', $tenantId)->where('event_key', $data['event_key'])->first();
            if ($existing !== null) {
                return $existing;
            }

            $order = DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            $operation = DB::table('production_order_operations')->where('tenant_id', $tenantId)
                ->where('production_order_id', $orderId)->where('id', $operationId)->lockForUpdate()->first();

            if ($order === null || $order->status !== 'IN_PROGRESS' || $operation === null) {
                throw ValidationException::withMessages(['production_order' => 'The production order must be IN_PROGRESS and the operation must belong to it.']);
            }

            $routeStep = DB::table('process_route_steps')->where('tenant_id', $tenantId)
                ->where('route_version_id', $operation->route_version_id)->where('id', $operation->route_step_id)->first();

            $eventType = $data['event_type'];
            if ($eventType === 'START') {
                if (! in_array($operation->status, ['QUEUED','READY'], true)) {
                    throw ValidationException::withMessages(['operation' => 'Only a queued or ready operation can start.']);
                }
                if ($routeStep->predecessor_step_no !== null) {
                    $predecessor = DB::table('production_order_operations')->where('tenant_id', $tenantId)
                        ->where('production_order_id', $orderId)->where('step_no', $routeStep->predecessor_step_no)->first();
                    if ($predecessor === null || ! in_array($predecessor->status, ['COMPLETED','SKIPPED'], true)) {
                        throw ValidationException::withMessages(['predecessor' => 'The predecessor operation must be completed or validly skipped before this operation starts.']);
                    }
                    $predecessorStep = DB::table('process_route_steps')->where('tenant_id', $tenantId)
                        ->where('route_version_id', $operation->route_version_id)->where('id', $predecessor->route_step_id)->first();
                    if ($predecessorStep?->quality_gate) {
                        $released = DB::table('quality_release_decisions')->where('tenant_id', $tenantId)
                            ->where('subject_type', 'PRODUCTION_ORDER_OPERATION')->where('subject_id', $predecessor->id)
                            ->whereIn('decision', ['RELEASE','RELEASE_WITH_DEVIATION'])->exists();
                        if (! $released) {
                            throw ValidationException::withMessages(['quality_gate' => 'A predecessor quality gate has not been released.']);
                        }
                    }
                }

                $workerReference = $data['operator_reference'] ?? null;
                if ($workerReference === null || trim($workerReference) === '') {
                    throw ValidationException::withMessages(['operator_reference' => 'A confirmed skilled-worker assignment is required to start a production operation.']);
                }
                $assignmentQuery = DB::table('workforce_assignments as wa')
                    ->join('workforce_skill_records as ws', function ($join): void {
                        $join->on('ws.tenant_id', '=', 'wa.tenant_id')->on('ws.id', '=', 'wa.skill_record_id');
                    })
                    ->where('wa.tenant_id', $tenantId)->where('wa.operation_id', $operationId)
                    ->where('wa.worker_reference', $workerReference)
                    ->where('wa.assignment_status', 'CONFIRMED')
                    ->where('wa.planned_start_at', '<=', now())
                    ->where('wa.planned_end_at', '>=', now())
                    ->where('ws.status', 'ACTIVE')->where('ws.assessment_result', 'COMPETENT')
                    ->whereDate('ws.valid_from', '<=', now()->toDateString())
                    ->where(function ($q): void {
                        $q->whereNull('ws.valid_until')->orWhereDate('ws.valid_until', '>=', now()->toDateString());
                    });
                if ($routeStep->required_skill_code !== null) {
                    $assignmentQuery->where('ws.skill_code', $routeStep->required_skill_code);
                }
                $assignment = $assignmentQuery->exists();
                if (! $assignment) {
                    throw ValidationException::withMessages(['operator_reference' => 'The worker requires a current competent skill record and a confirmed assignment for this operation.']);
                }

                DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $operationId)
                    ->update(['status' => 'IN_PROGRESS', 'started_at' => now(), 'updated_at' => now()]);
            } elseif (in_array($eventType, ['OUTPUT','DEFECT','DOWNTIME'], true)) {
                if ($operation->status !== 'IN_PROGRESS') {
                    throw ValidationException::withMessages(['operation' => 'Output, defect and downtime events require an IN_PROGRESS operation.']);
                }
                if (in_array($eventType, ['OUTPUT','DEFECT'], true)) {
                    $quantity = (string) ($data['quantity'] ?? '0');
                    $quantityUnits = DecimalQuantity::units($quantity);
                    if ($quantityUnits <= 0) {
                        throw ValidationException::withMessages(['quantity' => 'Production quantity must be greater than zero.']);
                    }
                    $goodUnits = DecimalQuantity::units((string) $operation->good_quantity)
                        + ($eventType === 'OUTPUT' ? $quantityUnits : 0);
                    $defectiveUnits = DecimalQuantity::units((string) $operation->defective_quantity)
                        + ($eventType === 'DEFECT' ? $quantityUnits : 0);
                    if ($goodUnits + $defectiveUnits > DecimalQuantity::units((string) $operation->planned_quantity)) {
                        throw ValidationException::withMessages(['quantity' => 'Recorded output cannot exceed planned operation quantity.']);
                    }
                    DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $operationId)->update([
                        'good_quantity' => DecimalQuantity::format($goodUnits),
                        'defective_quantity' => DecimalQuantity::format($defectiveUnits),
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($eventType === 'BLOCK') {
                DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $operationId)->update(['status' => 'BLOCKED', 'updated_at' => now()]);
            } elseif ($eventType === 'UNBLOCK') {
                if ($operation->status !== 'BLOCKED') {
                    throw ValidationException::withMessages(['operation' => 'Only a blocked operation can be unblocked.']);
                }
                DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $operationId)->update(['status' => 'IN_PROGRESS', 'updated_at' => now()]);
            } elseif ($eventType === 'COMPLETE') {
                if ($operation->status !== 'IN_PROGRESS' ||
                    DecimalQuantity::units((string) $operation->good_quantity) + DecimalQuantity::units((string) $operation->defective_quantity)
                        !== DecimalQuantity::units((string) $operation->planned_quantity)) {
                    throw ValidationException::withMessages(['operation' => 'An operation can complete only when good plus defective output equals its planned quantity.']);
                }
                DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $operationId)->update([
                    'status' => 'COMPLETED', 'completed_at' => now(), 'updated_at' => now(),
                ]);
            } elseif ($eventType === 'SKIP') {
                if (! $routeStep->is_optional || ! in_array($operation->status, ['QUEUED','READY'], true)) {
                    throw ValidationException::withMessages(['operation' => 'Only an optional queued operation can be skipped.']);
                }
                if (trim((string) ($data['reason_code'] ?? '')) === '') {
                    throw ValidationException::withMessages(['reason_code' => 'Optional route skipping requires a reason.']);
                }
                DB::table('production_order_operations')->where('tenant_id', $tenantId)->where('id', $operationId)->update(['status' => 'SKIPPED', 'updated_at' => now()]);
            } else {
                throw ValidationException::withMessages(['event_type' => 'Unsupported production event type.']);
            }

            $eventId = (string) Str::uuid();
            DB::table('production_execution_events')->insert([
                'id' => $eventId, 'tenant_id' => $tenantId, 'production_order_id' => $orderId,
                'operation_id' => $operationId, 'event_type' => $eventType, 'event_key' => $data['event_key'],
                'good_quantity' => $eventType === 'OUTPUT' ? DecimalQuantity::format(DecimalQuantity::units((string) $data['quantity'])) : '0',
                'defective_quantity' => $eventType === 'DEFECT' ? DecimalQuantity::format(DecimalQuantity::units((string) $data['quantity'])) : '0',
                'downtime_minutes' => $eventType === 'DOWNTIME' ? ($data['downtime_minutes'] ?? 0) : 0,
                'reason_code' => $data['reason_code'] ?? null, 'reason_detail' => $data['reason_detail'] ?? null,
                'operator_reference' => $data['operator_reference'] ?? null,
                'workstation_reference' => $data['workstation_reference'] ?? null,
                'source_device_reference' => $data['source_device_reference'] ?? null,
                'recorded_by' => $actorId, 'occurred_at' => $data['occurred_at'] ?? now(), 'created_at' => now(),
            ]);

            $remaining = DB::table('production_order_operations')->where('tenant_id', $tenantId)
                ->where('production_order_id', $orderId)->whereNotIn('status', ['COMPLETED','SKIPPED'])->exists();
            if (! $remaining) {
                DB::table('production_orders')->where('tenant_id', $tenantId)->where('id', $orderId)
                    ->update(['status' => 'COMPLETED', 'completed_at' => now(), 'updated_at' => now()]);
            }

            return DB::table('production_execution_events')->where('tenant_id', $tenantId)->where('id', $eventId)->first();
        }, 3);
    }
}
