<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Services\Quality\QualityControlService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QualityController extends Controller
{
    public function __construct(
        private readonly QualityControlService $quality,
        private readonly TenantContextResolver $tenant
    ) {
    }

    public function storeInspection(Request $request): JsonResponse
    {
        Gate::authorize('quality.inspect');
        $data = $request->validate([
            'inspection_code' => ['required','string','max:100'],
            'inspection_type' => ['required','in:INCOMING_MATERIAL,FIRST_PIECE,INLINE,END_LINE,FINAL,PACKING,PRE_SHIPMENT,CUSTOMER_RETURN,PROCESS_AUDIT'],
            'subject_type' => ['required','in:MATERIAL_LOT,GOODS_RECEIPT_LINE,PRODUCTION_ORDER_OPERATION,PRODUCTION_ORDER,CUTTING_BUNDLE,SHIPMENT,CUSTOMER_ORDER'],
            'subject_id' => ['required','uuid'],
            'sample_quantity' => ['required','numeric','gt:0'],
            'inspection_standard_reference' => ['nullable','string','max:180'],
            'sampling_plan_reference' => ['nullable','string','max:180'],
            'measurements' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->quality->createInspection($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function submitInspectionResult(Request $request, string $inspectionId): JsonResponse
    {
        Gate::authorize('quality.record-inspection-result');
        $data = $request->validate([
            'accepted_quantity' => ['required','numeric','gte:0'],
            'rejected_quantity' => ['required','numeric','gte:0'],
            'inspected_at' => ['nullable','date'],
            'measurements' => ['nullable','array'],
            'observations' => ['nullable','array'],
            'containment_confirmed' => ['sometimes','boolean'],
            'containment_notes' => ['nullable','string','max:2000'],
            'deviation_approved' => ['sometimes','boolean'],
            'defects' => ['nullable','array'],
            'defects.*.defect_catalog_id' => ['nullable','uuid'],
            'defects.*.nonconformance_code' => ['required_with:defects','string','max:100'],
            'defects.*.defect_description' => ['required_with:defects','string','max:2000'],
            'defects.*.severity' => ['nullable','in:LOW,MEDIUM,HIGH,CRITICAL'],
            'defects.*.affected_quantity' => ['nullable','numeric','gt:0'],
            'defects.*.affected_reference' => ['nullable','string','max:180'],
        ]);

        return response()->json(['data' => $this->quality->submitInspectionResult($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $inspectionId, $data)]);
    }

    public function reviewInspection(Request $request, string $inspectionId): JsonResponse
    {
        Gate::authorize('quality.review-inspection');
        $data = $request->validate([
            'decision' => ['required','in:ACCEPT,REJECT,RETURN_FOR_REINSPECTION'],
            'review_reason' => ['required','string','max:2000'],
        ]);

        return response()->json(['data' => $this->quality->reviewInspection($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $inspectionId, $data)]);
    }

    public function createCorrectiveAction(Request $request, string $nonconformanceId): JsonResponse
    {
        Gate::authorize('quality.manage-capa');
        $data = $request->validate([
            'action_code' => ['required','string','max:100'],
            'action_type' => ['required','in:CORRECTIVE,PREVENTIVE,CONTAINMENT,SYSTEMIC'],
            'action_description' => ['required','string','max:4000'],
            'assigned_to_user_id' => ['nullable','uuid'],
            'due_date' => ['nullable','date'],
            'effectiveness_criteria' => ['nullable','string','max:2000'],
        ]);

        return response()->json(['data' => $this->quality->createCorrectiveAction($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $nonconformanceId, $data)], 201);
    }

    public function releaseMaterial(Request $request, string $lotId): JsonResponse
    {
        Gate::authorize('quality.release-material');
        $data = $request->validate([
            'inspection_id' => ['required','uuid'],
            'source_quarantine_location_id' => ['required','uuid'],
            'destination_location_id' => ['required','uuid'],
            'release_quantity' => ['required','numeric','gt:0'],
            'unit_code' => ['required','string','max:16'],
            'reason' => ['required','string','max:2000'],
            'conditions' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->quality->decideMaterialRelease($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $lotId, $data)]);
    }
}
