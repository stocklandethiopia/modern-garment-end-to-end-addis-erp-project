<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Services\Planning\IndustrialEngineeringService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IndustrialEngineeringController extends Controller
{
    public function __construct(
        private readonly IndustrialEngineeringService $ie,
        private readonly TenantContextResolver $tenant
    ) {}

    public function storeWorkCentre(Request $request): JsonResponse
    {
        Gate::authorize('planning.manage-capacity');
        $data = $request->validate([
            'site_id' => ['required', 'uuid'],
            'work_centre_code' => ['required', 'string', 'max:80'],
            'department_code' => ['required', 'in:CUTTING,PRINTING,EMBROIDERY,SEWING,WASHING,DYEING,SPECIAL_PROCESS,FINISHING,PRESSING,PACKING,QUALITY,SAMPLE_ROOM,OTHER'],
            'work_centre_type' => ['required', 'in:LINE,CELL,MACHINE_GROUP,PROCESS_AREA,SAMPLE_ROOM,SUBCONTRACT'],
            'name' => ['required', 'string', 'max:180'],
            'machine_family' => ['nullable', 'string', 'max:100'],
            'capabilities' => ['nullable', 'array'],
            'capacity_unit' => ['nullable', 'string', 'max:24'],
        ]);

        return response()->json(['data' => $this->ie->createWorkCentre($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function storeShift(Request $request): JsonResponse
    {
        Gate::authorize('planning.manage-capacity');
        $data = $request->validate([
            'site_id' => ['required', 'uuid'],
            'shift_code' => ['required', 'string', 'max:40'],
            'shift_name' => ['required', 'string', 'max:120'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'different:starts_at'],
            'unpaid_break_minutes' => ['nullable', 'integer', 'min:0', 'max:720'],
        ]);

        return response()->json(['data' => $this->ie->createShift($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function storeCapacitySlot(Request $request): JsonResponse
    {
        Gate::authorize('planning.manage-capacity');
        $data = $request->validate([
            'work_centre_id' => ['required', 'uuid'],
            'shift_id' => ['required', 'uuid'],
            'work_date' => ['required', 'date'],
            'available_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'planned_maintenance_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'capacity_basis_reference' => ['nullable', 'string', 'max:180'],
        ]);

        return response()->json(['data' => $this->ie->createCapacitySlot($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function storeOperationStandard(Request $request): JsonResponse
    {
        Gate::authorize('planning.manage-ie-standard');
        $data = $request->validate([
            'style_id' => ['required', 'uuid'],
            'style_revision_no' => ['required', 'integer', 'min:1'],
            'route_version_id' => ['required', 'uuid'],
            'route_step_id' => ['required', 'uuid'],
            'version_no' => ['required', 'integer', 'min:1'],
            'standard_minutes' => ['required', 'numeric', 'gt:0'],
            'observed_cycle_count' => ['required', 'integer', 'min:1'],
            'rating_basis' => ['required', 'string', 'max:80'],
            'allowance_basis' => ['required', 'string', 'max:80'],
            'study_evidence' => ['required', 'array'],
            'method_revision_reference' => ['required', 'string', 'max:120'],
            'ergonomic_risk_review' => ['required', 'in:PENDING,ACCEPTED,MITIGATION_REQUIRED'],
            'effective_from' => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->ie->createStandardVersion($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function approveOperationStandard(Request $request, string $standardId): JsonResponse
    {
        Gate::authorize('planning.approve-ie-standard');
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        return response()->json(['data' => $this->ie->approveStandardVersion($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $standardId, $data['reason'])]);
    }

    public function storePlan(Request $request): JsonResponse
    {
        Gate::authorize('planning.create-production-plan');
        $data = $request->validate([
            'plan_code' => ['required', 'string', 'max:80'],
            'version_no' => ['required', 'integer', 'min:1'],
            'horizon_start' => ['required', 'date'],
            'horizon_end' => ['required', 'date', 'after_or_equal:horizon_start'],
            'planning_basis' => ['nullable', 'array'],
            'assumptions' => ['nullable', 'string', 'max:4000'],
        ]);

        return response()->json(['data' => $this->ie->createPlanVersion($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function addPlanLine(Request $request, string $planId): JsonResponse
    {
        Gate::authorize('planning.create-production-plan');
        $data = $request->validate([
            'production_order_id' => ['required', 'uuid'],
            'operation_id' => ['required', 'uuid'],
            'work_centre_id' => ['required', 'uuid'],
            'capacity_slot_id' => ['required', 'uuid'],
            'sequence_no' => ['required', 'integer', 'min:1'],
            'planned_quantity' => ['required', 'numeric', 'gt:0'],
            'planned_minutes' => ['required', 'numeric', 'gt:0'],
            'planned_start_at' => ['required', 'date'],
            'planned_end_at' => ['required', 'date', 'after:planned_start_at'],
            'planned_start_date' => ['required', 'date'],
            'bottleneck_flag' => ['sometimes', 'boolean'],
            'schedule_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->ie->addPlanLine($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $planId, $data)], 201);
    }

    public function approvePlan(string $planId): JsonResponse
    {
        Gate::authorize('planning.approve-production-plan');

        return response()->json(['data' => $this->ie->approvePlan($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $planId)]);
    }

    public function recordWorkerSkill(Request $request): JsonResponse
    {
        Gate::authorize('workforce.assess-skills');
        $data = $request->validate([
            'worker_reference' => ['required', 'string', 'max:120'],
            'skill_code' => ['required', 'string', 'max:100'],
            'skill_grade' => ['required', 'string', 'max:40'],
            'assessment_result' => ['required', 'in:COMPETENT,DEVELOPING,NOT_COMPETENT,EXPIRED'],
            'assessment_evidence' => ['required', 'array'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'authorized_operation_scope' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->ie->recordWorkerSkill($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function assignWorker(Request $request): JsonResponse
    {
        Gate::authorize('workforce.assign-operations');
        $data = $request->validate([
            'operation_id' => ['required', 'uuid'],
            'work_centre_id' => ['required', 'uuid'],
            'shift_id' => ['required', 'uuid'],
            'skill_record_id' => ['required', 'uuid'],
            'worker_reference' => ['required', 'string', 'max:120'],
            'planned_start_at' => ['required', 'date'],
            'planned_end_at' => ['required', 'date', 'after:planned_start_at'],
            'planned_minutes' => ['required', 'numeric', 'gt:0'],
        ]);

        return response()->json(['data' => $this->ie->assignWorkerToOperation($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function confirmAssignment(string $assignmentId): JsonResponse
    {
        Gate::authorize('workforce.confirm-assignments');

        return response()->json(['data' => $this->ie->confirmWorkerAssignment($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $assignmentId)]);
    }
}
