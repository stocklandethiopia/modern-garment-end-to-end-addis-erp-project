<?php

namespace App\Http\Controllers\Sourcing;

use App\Http\Controllers\Controller;
use App\Services\Sourcing\SourcingService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SourcingController extends Controller
{
    public function __construct(
        private readonly SourcingService $sourcing,
        private readonly TenantContextResolver $tenant
    ) {}

    public function storeSupplier(Request $request): JsonResponse
    {
        Gate::authorize('sourcing.manage');
        $data = $request->validate([
            'supplier_code' => ['required', 'string', 'max:80'],
            'legal_name' => ['required', 'string', 'max:255'],
            'supplier_type' => ['required', 'in:FABRIC_MILL,TRIM_SUPPLIER,PACKAGING,CHEMICAL,SUBCONTRACTOR,CARRIER,SERVICE'],
            'country_code' => ['required', 'string', 'size:2'],
            'tin_reference' => ['nullable', 'string', 'max:100'],
            'licence_reference' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json([
            'data' => $this->sourcing->createSupplier($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data),
        ], 201);
    }

    public function approveSupplier(Request $request, string $supplierId): JsonResponse
    {
        Gate::authorize('sourcing.approve-supplier');
        $data = $request->validate([
            'due_diligence_reference' => ['required', 'string', 'max:180'],
            'quality_approval_reference' => ['required', 'string', 'max:180'],
        ]);

        return response()->json([
            'data' => $this->sourcing->approveSupplier($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $supplierId, $data),
        ]);
    }

    public function storePurchaseOrder(Request $request): JsonResponse
    {
        Gate::authorize('sourcing.create-purchase-order');
        $data = $request->validate([
            'legal_entity_id' => ['required', 'uuid'],
            'supplier_id' => ['required', 'uuid'],
            'sourcing_event_id' => ['nullable', 'uuid'],
            'order_code' => ['required', 'string', 'max:80'],
            'currency' => ['required', 'string', 'size:3'],
            'order_date' => ['required', 'date'],
            'requested_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'incoterm_code' => ['nullable', 'string', 'max:12'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_code' => ['required', 'string', 'max:100'],
            'lines.*.material_type' => ['required', 'in:FABRIC,TRIM,PACKAGING,CHEMICAL,SERVICE,SUBCONTRACT_PROCESS'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.specification_reference' => ['nullable', 'string', 'max:180'],
            'lines.*.unit_code' => ['required', 'string', 'max:16'],
            'lines.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'lines.*.required_delivery_date' => ['nullable', 'date'],
        ]);

        return response()->json([
            'data' => $this->sourcing->createPurchaseOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data),
        ], 201);
    }

    public function submitPurchaseOrder(string $orderId): JsonResponse
    {
        Gate::authorize('sourcing.submit-purchase-order');

        return response()->json(['data' => $this->sourcing->submitPurchaseOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $orderId)]);
    }

    public function decidePurchaseOrder(Request $request, string $orderId): JsonResponse
    {
        Gate::authorize('sourcing.approve-purchase-order');
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json([
            'data' => $this->sourcing->decidePurchaseOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $orderId, $data['approve'], $data['reason']),
        ]);
    }

    public function markPurchaseOrderSent(string $orderId): JsonResponse
    {
        Gate::authorize('sourcing.dispatch-purchase-order');

        return response()->json(['data' => $this->sourcing->markPurchaseOrderSent($this->tenant->requireTenantId(), $orderId)]);
    }

    public function recordGoodsReceipt(Request $request): JsonResponse
    {
        Gate::authorize('inventory.receive-materials');
        $data = $request->validate([
            'purchase_order_id' => ['required', 'uuid'],
            'destination_location_id' => ['required', 'uuid'],
            'receipt_code' => ['required', 'string', 'max:80'],
            'supplier_delivery_reference' => ['nullable', 'string', 'max:120'],
            'received_at' => ['nullable', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_order_line_id' => ['required', 'uuid'],
            'lines.*.quantity_received' => ['required', 'numeric', 'gt:0'],
            'lines.*.discrepancy_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        return response()->json([
            'data' => $this->sourcing->recordGoodsReceipt($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data),
        ], 201);
    }

    public function materializeReceiptLine(Request $request, string $receiptLineId): JsonResponse
    {
        Gate::authorize('inventory.materialize-receipt');
        $data = $request->validate([
            'quality_inspection_id' => ['required', 'uuid'],
            'materialized_quantity' => ['required', 'numeric', 'gt:0'],
            'supplier_lot_reference' => ['nullable', 'string', 'max:120'],
            'supplier_dye_lot_reference' => ['nullable', 'string', 'max:120'],
            'country_of_origin' => ['nullable', 'string', 'size:2'],
            'shade_code' => ['nullable', 'string', 'max:80'],
            'specification_snapshot' => ['nullable', 'array'],
            'roll_code' => ['required_without:trim_lot_code', 'string', 'max:120'],
            'measured_width' => ['nullable', 'numeric', 'gt:0'],
            'gsm' => ['nullable', 'numeric', 'gt:0'],
            'roll_attributes' => ['nullable', 'array'],
            'trim_lot_code' => ['required_without:roll_code', 'string', 'max:120'],
            'approved_artwork_reference' => ['nullable', 'string', 'max:180'],
            'lot_attributes' => ['nullable', 'array'],
        ]);

        return response()->json([
            'data' => $this->sourcing->materializeInspectedReceiptLine($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $receiptLineId, $data),
        ], 201);
    }
}
