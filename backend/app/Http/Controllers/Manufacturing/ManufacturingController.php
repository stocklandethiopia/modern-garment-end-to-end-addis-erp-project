<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Services\Manufacturing\ManufacturingExecutionService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManufacturingController extends Controller
{
    public function __construct(
        private readonly ManufacturingExecutionService $manufacturing,
        private readonly TenantContextResolver $tenant
    ) {}

    public function storeRoute(Request $request): JsonResponse
    {
        Gate::authorize('manufacturing.manage-routes');
        $data = $request->validate([
            'style_id' => ['required', 'uuid'],
            'style_revision_no' => ['required', 'integer', 'min:1'],
            'version_no' => ['required', 'integer', 'min:1'],
            'route_code' => ['required', 'string', 'max:100'],
            'route_reason' => ['required', 'string', 'max:2000'],
            'effective_from' => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->manufacturing->createRouteVersion($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function addRouteSteps(Request $request, string $routeId): JsonResponse
    {
        Gate::authorize('manufacturing.manage-routes');
        $data = $request->validate([
            'steps' => ['required', 'array', 'min:1', 'max:120'],
            'steps.*.step_code' => ['required', 'string', 'max:80'],
            'steps.*.department_code' => ['required', 'in:PRODUCT_ENGINEERING,CUTTING,PRINTING,EMBROIDERY,SEWING,WASHING,DYEING,SPECIAL_PROCESS,FINISHING,PRESSING,PACKING,QUALITY,SUBCONTRACT'],
            'steps.*.operation_name' => ['required', 'string', 'max:180'],
            'steps.*.process_type' => ['required', 'string', 'max:40'],
            'steps.*.is_optional' => ['sometimes', 'boolean'],
            'steps.*.condition_expression' => ['nullable', 'array'],
            'steps.*.predecessor_step_no' => ['nullable', 'integer', 'min:1'],
            'steps.*.subcontract_allowed' => ['sometimes', 'boolean'],
            'steps.*.quality_gate' => ['sometimes', 'boolean'],
            'steps.*.standard_minutes' => ['nullable', 'numeric', 'gte:0'],
            'steps.*.required_skill_code' => ['nullable', 'string', 'max:80'],
            'steps.*.work_instruction_reference' => ['nullable', 'string', 'max:180'],
        ]);

        return response()->json(['data' => $this->manufacturing->addRouteSteps($this->tenant->requireTenantId(), $routeId, $data['steps'])]);
    }

    public function submitRoute(string $routeId): JsonResponse
    {
        Gate::authorize('manufacturing.submit-route');

        return response()->json(['data' => $this->manufacturing->submitRoute($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $routeId)]);
    }

    public function decideRoute(Request $request, string $routeId): JsonResponse
    {
        Gate::authorize('manufacturing.approve-route');
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->manufacturing->decideRoute($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $routeId, $data['approve'], $data['reason'])]);
    }

    public function storeProductionOrder(Request $request): JsonResponse
    {
        Gate::authorize('manufacturing.create-production-order');
        $data = $request->validate([
            'legal_entity_id' => ['required', 'uuid'],
            'site_id' => ['required', 'uuid'],
            'order_code' => ['required', 'string', 'max:100'],
            'tech_pack_version_id' => ['required', 'uuid'],
            'route_version_id' => ['required', 'uuid'],
            'planned_quantity' => ['required', 'numeric', 'gt:0'],
            'unit_code' => ['nullable', 'string', 'max:16'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
        ]);

        return response()->json(['data' => $this->manufacturing->createProductionOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function releaseProductionOrder(string $orderId): JsonResponse
    {
        Gate::authorize('manufacturing.release-production-order');

        return response()->json(['data' => $this->manufacturing->releaseProductionOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $orderId)]);
    }

    public function startProductionOrder(string $orderId): JsonResponse
    {
        Gate::authorize('manufacturing.start-production-order');

        return response()->json(['data' => $this->manufacturing->startProductionOrder($this->tenant->requireTenantId(), $orderId)]);
    }

    public function recordOperationEvent(Request $request, string $orderId, string $operationId): JsonResponse
    {
        Gate::authorize('manufacturing.record-execution');
        $data = $request->validate([
            'event_key' => ['required', 'string', 'max:160'],
            'event_type' => ['required', 'in:START,OUTPUT,DEFECT,DOWNTIME,BLOCK,UNBLOCK,COMPLETE,SKIP'],
            'quantity' => ['nullable', 'numeric', 'gt:0'],
            'downtime_minutes' => ['nullable', 'numeric', 'gt:0'],
            'reason_code' => ['nullable', 'string', 'max:80'],
            'reason_detail' => ['nullable', 'string', 'max:2000'],
            'operator_reference' => ['nullable', 'string', 'max:120'],
            'workstation_reference' => ['nullable', 'string', 'max:120'],
            'source_device_reference' => ['nullable', 'string', 'max:160'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->manufacturing->recordOperationEvent($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $orderId, $operationId, $data)], 201);
    }
}
