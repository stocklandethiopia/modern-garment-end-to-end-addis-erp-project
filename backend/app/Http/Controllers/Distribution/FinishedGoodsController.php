<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Services\Distribution\FinishedGoodsService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FinishedGoodsController extends Controller
{
    public function __construct(
        private readonly FinishedGoodsService $finishedGoods,
        private readonly TenantContextResolver $tenant
    ) {}

    public function receiveProductionOutput(Request $request): JsonResponse
    {
        Gate::authorize('inventory.receive-finished-goods');
        $data = $request->validate([
            'production_order_id' => ['required', 'uuid'],
            'final_quality_inspection_id' => ['required', 'uuid'],
            'quarantine_location_id' => ['required', 'uuid'],
            'lot_code' => ['required', 'string', 'max:120'],
            'sku_code' => ['required', 'string', 'max:100'],
            'colour_code' => ['nullable', 'string', 'max:60'],
            'size_code' => ['nullable', 'string', 'max:40'],
            'finished_quantity' => ['required', 'numeric', 'gt:0'],
            'unit_code' => ['nullable', 'string', 'max:16'],
            'received_at' => ['nullable', 'date'],
        ]);

        return response()->json([
            'data' => $this->finishedGoods->receiveCompletedProduction(
                $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data
            ),
        ], 201);
    }

    public function releaseLot(Request $request, string $lotId): JsonResponse
    {
        Gate::authorize('quality.release-finished-goods');
        $data = $request->validate([
            'inspection_id' => ['required', 'uuid'],
            'source_location_id' => ['required', 'uuid'],
            'destination_location_id' => ['required', 'uuid'],
            'release_quantity' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:2000'],
            'conditions' => ['nullable', 'array'],
        ]);

        return response()->json([
            'data' => $this->finishedGoods->releaseLot($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $lotId, $data),
        ]);
    }

    public function storeCustomerOrder(Request $request): JsonResponse
    {
        Gate::authorize('commerce.create-customer-order');
        $data = $request->validate([
            'legal_entity_id' => ['required', 'uuid'],
            'order_number' => ['required', 'string', 'max:100'],
            'channel_code' => ['required', 'in:WHOLESALE,RETAIL,RESELLER,D2C,MARKETPLACE,EXPORT,INTERNAL_TRANSFER'],
            'customer_reference' => ['nullable', 'string', 'max:120'],
            'customer_name_snapshot' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'ordered_at' => ['nullable', 'date'],
            'requested_delivery_date' => ['nullable', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.sku_code' => ['required', 'string', 'max:100'],
            'lines.*.style_reference' => ['nullable', 'string', 'max:120'],
            'lines.*.colour_code' => ['nullable', 'string', 'max:60'],
            'lines.*.size_code' => ['nullable', 'string', 'max:40'],
            'lines.*.description' => ['required', 'string', 'max:400'],
            'lines.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_code' => ['nullable', 'string', 'max:16'],
            'lines.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'lines.*.tax_code_reference' => ['nullable', 'string', 'max:80'],
        ]);

        return response()->json([
            'data' => $this->finishedGoods->createCustomerOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data),
        ], 201);
    }

    public function recordCreditDecision(Request $request, string $orderId): JsonResponse
    {
        Gate::authorize('commerce.review-credit');
        $data = $request->validate([
            'decision' => ['required', 'in:CLEAR,HELD'],
            'decision_reference' => ['required', 'string', 'max:180'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->finishedGoods->recordCustomerCreditDecision(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $orderId, $data
        )], 201);
    }

    public function approveCreditException(Request $request, string $decisionId): JsonResponse
    {
        Gate::authorize('commerce.approve-credit-exception');
        $data = $request->validate([
            'approval_reference' => ['required', 'string', 'max:180'],
        ]);

        return response()->json(['data' => $this->finishedGoods->approveCustomerCreditException(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $decisionId, $data['approval_reference']
        )]);
    }

    public function confirmCustomerOrder(Request $request, string $orderId): JsonResponse
    {
        Gate::authorize('commerce.confirm-customer-order');
        $data = $request->validate([
            'credit_control_decision_id' => ['required', 'uuid'],
        ]);

        return response()->json([
            'data' => $this->finishedGoods->confirmCustomerOrder($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $orderId, $data['credit_control_decision_id']),
        ]);
    }

    public function reserveOrderLine(Request $request, string $lineId): JsonResponse
    {
        Gate::authorize('inventory.reserve-finished-goods');
        $data = $request->validate([
            'finished_goods_lot_id' => ['required', 'uuid'],
            'location_id' => ['required', 'uuid'],
            'reservation_key' => ['required', 'string', 'max:180'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        return response()->json([
            'data' => $this->finishedGoods->reserveCustomerOrderLine($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $lineId, $data),
        ], 201);
    }

    public function dispatchReservation(Request $request, string $reservationId): JsonResponse
    {
        Gate::authorize('distribution.dispatch-shipment');
        $data = $request->validate([
            'source_location_id' => ['required', 'uuid'],
            'shipment_number' => ['required', 'string', 'max:100'],
            'shipment_mode' => ['required', 'in:ROAD,AIR,SEA,COURIER,CUSTOMER_COLLECTION,INTERNAL_TRANSFER'],
            'carrier_reference' => ['nullable', 'string', 'max:180'],
            'tracking_reference' => ['nullable', 'string', 'max:180'],
            'destination_snapshot' => ['required', 'array'],
            'destination_snapshot.name' => ['required', 'string', 'max:255'],
            'destination_snapshot.address' => ['required', 'string', 'max:1000'],
            'pack_reference' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        return response()->json([
            'data' => $this->finishedGoods->dispatchReservedLine($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $reservationId, $data),
        ], 201);
    }
}
