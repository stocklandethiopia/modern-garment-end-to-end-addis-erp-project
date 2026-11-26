<?php

namespace App\Services\Distribution;

use App\Services\Support\DecimalQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FinishedGoodsService
{
    public function receiveCompletedProduction(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $order = DB::table('production_orders')->where('tenant_id', $tenantId)
                ->where('id', $data['production_order_id'])->lockForUpdate()->first();

            if ($order === null || $order->status !== 'COMPLETED') {
                throw ValidationException::withMessages(['production_order_id' => 'Only a completed production order can transfer finished output to finished-goods quarantine.']);
            }

            $inspection = DB::table('quality_inspections')->where('tenant_id', $tenantId)
                ->where('id', $data['final_quality_inspection_id'])
                ->where('subject_type', 'PRODUCTION_ORDER')
                ->where('subject_id', $order->id)
                ->first();

            if ($inspection === null || ! in_array($inspection->result_status, ['PASS','PASS_WITH_DEVIATION'], true) ||
                $inspection->reviewed_by === null || $inspection->reviewed_at === null) {
                throw ValidationException::withMessages(['final_quality_inspection_id' => 'A reviewed passing final inspection for this production order is required.']);
            }

            $openNonconformances = DB::table('quality_nonconformances')->where('tenant_id', $tenantId)
                ->where('inspection_id', $inspection->id)->whereNotIn('status', ['RESOLVED','CLOSED'])->exists();
            if ($openNonconformances) {
                throw ValidationException::withMessages(['quality' => 'Open final nonconformances block finished-goods receipt.']);
            }

            $finalGood = DB::table('production_order_operations')->where('tenant_id', $tenantId)
                ->where('production_order_id', $order->id)->where('status', 'COMPLETED')->orderByDesc('step_no')->value('good_quantity');
            if ($finalGood === null) {
                throw ValidationException::withMessages(['production_order_id' => 'The completed order has no completed operation output to receive as finished goods.']);
            }

            $quantity = (string) $data['finished_quantity'];
            if (DecimalQuantity::units($quantity) <= 0 || DecimalQuantity::exceeds($quantity, (string) $finalGood)) {
                throw ValidationException::withMessages(['finished_quantity' => 'Finished quantity must be positive and cannot exceed the last operation good output.']);
            }

            $location = DB::table('inventory_locations')->where('tenant_id', $tenantId)
                ->where('id', $data['quarantine_location_id'])->first();
            if ($location === null || strtoupper((string) $location->location_type) !== 'QUARANTINE') {
                throw ValidationException::withMessages(['quarantine_location_id' => 'Finished goods must enter a QUARANTINE location first.']);
            }

            $id = (string) Str::uuid();
            DB::table('finished_goods_lots')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'production_order_id' => $order->id,
                'final_quality_inspection_id' => $inspection->id,
                'lot_code' => $data['lot_code'],
                'sku_code' => $data['sku_code'],
                'style_id' => $order->style_id,
                'style_revision_no' => $order->style_revision_no,
                'colour_code' => $data['colour_code'] ?? null,
                'size_code' => $data['size_code'] ?? null,
                'finished_quantity' => $quantity,
                'unit_code' => $data['unit_code'] ?? 'PCS',
                'status' => 'QUARANTINE',
                'created_by' => $actorId,
                'received_at' => $data['received_at'] ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('finished_goods_transactions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'finished_goods_lot_id' => $id,
                'from_location_id' => null,
                'to_location_id' => $location->id,
                'transaction_type' => 'RECEIPT',
                'quantity' => $quantity,
                'unit_code' => $data['unit_code'] ?? 'PCS',
                'idempotency_key' => 'production-fg-receipt:'.$order->id.':'.$id,
                'source_document_type' => 'PRODUCTION_ORDER',
                'source_document_id' => $order->id,
                'reason' => 'Completed production output is held in finished-goods quarantine pending final release.',
                'occurred_at' => $data['received_at'] ?? now(),
                'recorded_by' => $actorId,
                'created_at' => now(),
            ]);

            return DB::table('finished_goods_lots')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function releaseLot(string $tenantId, string $actorId, string $lotId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $lotId, $data): object {
            $lot = DB::table('finished_goods_lots')->where('tenant_id', $tenantId)->where('id', $lotId)->lockForUpdate()->first();
            if ($lot === null || $lot->status !== 'QUARANTINE') {
                throw ValidationException::withMessages(['lot' => 'Only a quarantined finished-goods lot can be released.']);
            }

            $inspection = DB::table('quality_inspections')->where('tenant_id', $tenantId)
                ->where('id', $data['inspection_id'])->where('subject_type', 'PRODUCTION_ORDER')
                ->where('subject_id', $lot->production_order_id)
                ->whereIn('result_status', ['PASS','PASS_WITH_DEVIATION'])
                ->whereNotNull('reviewed_by')->whereNotNull('reviewed_at')->first();

            if ($inspection === null || DB::table('quality_nonconformances')->where('tenant_id', $tenantId)
                ->where('inspection_id', $inspection->id)->whereNotIn('status', ['RESOLVED','CLOSED'])->exists()) {
                throw ValidationException::withMessages(['inspection_id' => 'A reviewed passing final inspection with no open nonconformance is required.']);
            }

            $source = DB::table('inventory_locations')->where('tenant_id', $tenantId)->where('id', $data['source_location_id'])->first();
            $destination = DB::table('inventory_locations')->where('tenant_id', $tenantId)->where('id', $data['destination_location_id'])->first();
            if ($source === null || strtoupper((string) $source->location_type) !== 'QUARANTINE' ||
                $destination === null || strtoupper((string) $destination->location_type) === 'QUARANTINE' || ! $destination->allow_stock) {
                throw ValidationException::withMessages(['location' => 'Finished-goods release must move stock from quarantine to a stock-enabled location.']);
            }

            $balance = $this->balanceAt($tenantId, $lotId, $source->id);
            $quantity = (string) $data['release_quantity'];
            if (DecimalQuantity::units($balance) <= 0 || ! DecimalQuantity::equal($balance, $quantity)) {
                throw ValidationException::withMessages(['release_quantity' => 'Release quantity must equal the full available quarantined balance.']);
            }

            $decisionId = (string) Str::uuid();
            DB::table('quality_release_decisions')->insert([
                'id' => $decisionId,
                'tenant_id' => $tenantId,
                'subject_type' => 'FINISHED_GOODS_LOT',
                'subject_id' => $lotId,
                'release_stage' => 'FINISHED_GOODS',
                'decision' => 'RELEASE',
                'reason' => $data['reason'],
                'linked_inspection_id' => $inspection->id,
                'conditions' => json_encode($data['conditions'] ?? [], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
                'created_at' => now(),
            ]);

            DB::table('finished_goods_transactions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'finished_goods_lot_id' => $lotId,
                'from_location_id' => $source->id,
                'to_location_id' => $destination->id,
                'transaction_type' => 'QUALITY_RELEASE',
                'quantity' => $quantity,
                'unit_code' => $lot->unit_code,
                'idempotency_key' => 'finished-goods-quality-release:'.$decisionId,
                'source_document_type' => 'QUALITY_RELEASE',
                'source_document_id' => $decisionId,
                'reason' => $data['reason'],
                'occurred_at' => now(),
                'recorded_by' => $actorId,
                'created_at' => now(),
            ]);

            DB::table('finished_goods_lots')->where('tenant_id', $tenantId)->where('id', $lotId)
                ->update(['status' => 'AVAILABLE', 'updated_at' => now()]);

            return DB::table('finished_goods_lots')->where('tenant_id', $tenantId)->where('id', $lotId)->first();
        }, 3);
    }

    public function createCustomerOrder(string $tenantId, string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('customer_orders')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'legal_entity_id' => $data['legal_entity_id'],
            'order_number' => $data['order_number'],
            'channel_code' => $data['channel_code'],
            'customer_reference' => $data['customer_reference'] ?? null,
            'customer_name_snapshot' => $data['customer_name_snapshot'],
            'currency' => strtoupper($data['currency']),
            'ordered_at' => $data['ordered_at'] ?? now(),
            'requested_delivery_date' => $data['requested_delivery_date'] ?? null,
            'status' => 'DRAFT',
            'credit_control_status' => 'NOT_CHECKED',
            'created_by' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($data['lines'] as $i => $line) {
            DB::table('customer_order_lines')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'customer_order_id' => $id,
                'line_no' => $i + 1,
                'sku_code' => $line['sku_code'],
                'style_reference' => $line['style_reference'] ?? null,
                'colour_code' => $line['colour_code'] ?? null,
                'size_code' => $line['size_code'] ?? null,
                'description' => $line['description'],
                'ordered_quantity' => $line['ordered_quantity'],
                'reserved_quantity' => 0,
                'fulfilled_quantity' => 0,
                'unit_code' => $line['unit_code'] ?? 'PCS',
                'unit_price' => $line['unit_price'],
                'tax_code_reference' => $line['tax_code_reference'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function recordCustomerCreditDecision(string $tenantId, string $actorId, string $orderId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderId, $data): object {
            $order = DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'DRAFT') {
                throw ValidationException::withMessages(['order' => 'Credit decisions can only be recorded for a draft customer order.']);
            }
            if (trim((string) $data['decision_reference']) === '' || trim((string) $data['reason']) === '') {
                throw ValidationException::withMessages(['decision_reference' => 'A credit decision reference and reason are required.']);
            }
            if ($order->created_by === $actorId) {
                throw ValidationException::withMessages(['reviewer' => 'The order creator cannot perform the independent customer credit review.']);
            }
            if ($data['decision'] === 'APPROVED_EXCEPTION' &&
                (empty($data['exception_approval_reference']) || trim((string) $data['exception_approval_reference']) === '')) {
                throw ValidationException::withMessages(['exception_approval_reference' => 'A separately authorized credit exception reference is required.']);
            }

            $id = (string) Str::uuid();
            DB::table('customer_credit_decisions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'customer_order_id' => $orderId,
                'decision' => $data['decision'],
                'decision_reference' => $data['decision_reference'],
                'reason' => $data['reason'],
                'reviewed_by' => $actorId,
                'reviewed_at' => now(),
                'exception_approved_by' => $data['decision'] === 'APPROVED_EXCEPTION' ? $data['exception_approved_by'] : null,
                'exception_approved_at' => $data['decision'] === 'APPROVED_EXCEPTION' ? ($data['exception_approved_at'] ?? now()) : null,
                'exception_approval_reference' => $data['decision'] === 'APPROVED_EXCEPTION' ? $data['exception_approval_reference'] : null,
                'created_at' => now(),
            ]);

            return DB::table('customer_credit_decisions')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function confirmCustomerOrder(string $tenantId, string $actorId, string $orderId, string $creditDecisionId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderId, $creditDecisionId): object {
            $order = DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'DRAFT') {
                throw ValidationException::withMessages(['order' => 'Only a draft customer order can be confirmed.']);
            }
            $decision = DB::table('customer_credit_decisions')->where('tenant_id', $tenantId)
                ->where('id', $creditDecisionId)->where('customer_order_id', $orderId)->first();
            if ($decision === null || ! in_array($decision->decision, ['CLEAR','APPROVED_EXCEPTION'], true)) {
                throw ValidationException::withMessages(['credit_control_decision_id' => 'An independent, linked credit decision of CLEAR or APPROVED_EXCEPTION is required.']);
            }
            if ($decision->reviewed_by === $actorId) {
                throw ValidationException::withMessages(['conflict_of_duties' => 'The credit reviewer cannot also confirm the customer order.']);
            }
            if ($decision->decision === 'APPROVED_EXCEPTION' &&
                ($decision->exception_approved_by === null ||
                 $decision->exception_approved_by === $decision->reviewed_by ||
                 $decision->exception_approval_reference === null)) {
                throw ValidationException::withMessages(['credit_control_decision_id' => 'The credit exception approval evidence is incomplete.']);
            }
            if (! DB::table('customer_order_lines')->where('tenant_id', $tenantId)->where('customer_order_id', $orderId)->exists()) {
                throw ValidationException::withMessages(['lines' => 'A customer order must have at least one line.']);
            }

            DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->update([
                'status' => 'CONFIRMED',
                'credit_control_status' => $decision->decision,
                'credit_control_decision_id' => $decision->id,
                'confirmed_by' => $actorId,
                'confirmed_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function reserveCustomerOrderLine(string $tenantId, string $actorId, string $orderLineId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderLineId, $data): object {
            $line = DB::table('customer_order_lines')->where('tenant_id', $tenantId)->where('id', $orderLineId)->lockForUpdate()->first();
            $order = $line ? DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $line->customer_order_id)->lockForUpdate()->first() : null;
            if ($line === null || $order === null || ! in_array($order->status, ['CONFIRMED','ALLOCATED','PART_FULFILLED'], true)) {
                throw ValidationException::withMessages(['order_line' => 'Reservations require a confirmed customer order line.']);
            }

            $lot = DB::table('finished_goods_lots')->where('tenant_id', $tenantId)->where('id', $data['finished_goods_lot_id'])->lockForUpdate()->first();
            if ($lot === null || $lot->status !== 'AVAILABLE' || $lot->sku_code !== $line->sku_code ||
                ($line->colour_code !== null && $lot->colour_code !== $line->colour_code) ||
                ($line->size_code !== null && $lot->size_code !== $line->size_code)) {
                throw ValidationException::withMessages(['finished_goods_lot_id' => 'The lot must be available and match the order SKU, colour and size.']);
            }

            $quantity = (string) $data['quantity'];
            $reservedElsewhere = (string) DB::table('finished_goods_reservations')->where('tenant_id', $tenantId)
                ->where('finished_goods_lot_id', $lot->id)->where('source_location_id', $data['location_id'])
                ->where('status', 'ACTIVE')->sum('quantity');
            $lotBalance = $this->balanceAt($tenantId, $lot->id, $data['location_id']);
            $available = DecimalQuantity::subtract($lotBalance, $reservedElsewhere);

            if (DecimalQuantity::units($quantity) <= 0 || DecimalQuantity::exceeds($quantity, $available)) {
                throw ValidationException::withMessages(['quantity' => 'Requested reservation exceeds unreserved finished-goods quantity at the selected location.']);
            }

            $remainingDemand = DecimalQuantity::subtract((string) $line->ordered_quantity, (string) $line->fulfilled_quantity);
            if (DecimalQuantity::exceeds(DecimalQuantity::add((string) $line->reserved_quantity, $quantity), $remainingDemand)) {
                throw ValidationException::withMessages(['quantity' => 'Reservations cannot exceed the remaining unfulfilled order demand.']);
            }

            $id = (string) Str::uuid();
            DB::table('finished_goods_reservations')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'customer_order_line_id' => $line->id,
                'finished_goods_lot_id' => $lot->id,
                'source_location_id' => $data['location_id'],
                'reservation_key' => $data['reservation_key'],
                'quantity' => $quantity,
                'unit_code' => $lot->unit_code,
                'status' => 'ACTIVE',
                'reserved_by' => $actorId,
                'reserved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('customer_order_lines')->where('tenant_id', $tenantId)->where('id', $line->id)->update([
                'reserved_quantity' => DecimalQuantity::add((string) $line->reserved_quantity, $quantity),
                'updated_at' => now(),
            ]);
            DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $order->id)
                ->update(['status' => 'ALLOCATED', 'updated_at' => now()]);

            return DB::table('finished_goods_reservations')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function dispatchReservedLine(string $tenantId, string $actorId, string $reservationId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $reservationId, $data): object {
            $reservation = DB::table('finished_goods_reservations')->where('tenant_id', $tenantId)->where('id', $reservationId)->lockForUpdate()->first();
            if ($reservation === null || $reservation->status !== 'ACTIVE') {
                throw ValidationException::withMessages(['reservation' => 'Only an active reservation can be dispatched.']);
            }
            $lot = DB::table('finished_goods_lots')->where('tenant_id', $tenantId)->where('id', $reservation->finished_goods_lot_id)->lockForUpdate()->first();
            $line = DB::table('customer_order_lines')->where('tenant_id', $tenantId)->where('id', $reservation->customer_order_line_id)->lockForUpdate()->first();
            $order = $line ? DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $line->customer_order_id)->lockForUpdate()->first() : null;
            if ($lot === null || $line === null || $order === null || $order->credit_control_status !== 'CLEAR' && $order->credit_control_status !== 'APPROVED_EXCEPTION') {
                throw ValidationException::withMessages(['order' => 'The reservation needs an order with cleared credit status.']);
            }

            $source = DB::table('inventory_locations')->where('tenant_id', $tenantId)->where('id', $data['source_location_id'])->first();
            if ($source === null || strtoupper((string) $source->location_type) === 'QUARANTINE' || ! $source->allow_stock ||
                $reservation->source_location_id !== $source->id) {
                throw ValidationException::withMessages(['source_location_id' => 'Dispatch must originate from the exact stock-enabled location held by this reservation.']);
            }
            $quantity = (string) $reservation->quantity;
            if (DecimalQuantity::exceeds($quantity, $this->balanceAt($tenantId, $lot->id, $source->id))) {
                throw ValidationException::withMessages(['quantity' => 'The reserved lot balance is not sufficient at the dispatch location.']);
            }

            $shipmentId = (string) Str::uuid();
            DB::table('shipments')->insert([
                'id' => $shipmentId,
                'tenant_id' => $tenantId,
                'customer_order_id' => $order->id,
                'source_location_id' => $source->id,
                'shipment_number' => $data['shipment_number'],
                'shipment_mode' => $data['shipment_mode'],
                'carrier_reference' => $data['carrier_reference'] ?? null,
                'tracking_reference' => $data['tracking_reference'] ?? null,
                'destination_snapshot' => json_encode($data['destination_snapshot'], JSON_THROW_ON_ERROR),
                'status' => 'PACKED',
                'packed_at' => now(),
                'created_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('shipment_lines')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'shipment_id' => $shipmentId,
                'customer_order_line_id' => $line->id,
                'finished_goods_lot_id' => $lot->id,
                'sku_code' => $line->sku_code,
                'quantity' => $quantity,
                'unit_code' => $lot->unit_code,
                'pack_reference' => $data['pack_reference'] ?? null,
                'created_at' => now(),
            ]);

            $dispatchEvent = DB::table('finished_goods_transactions')->where('tenant_id', $tenantId)
                ->where('idempotency_key', 'dispatch-reservation:'.$reservation->id)->first();
            if ($dispatchEvent === null) {
                DB::table('finished_goods_transactions')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'finished_goods_lot_id' => $lot->id,
                    'from_location_id' => $source->id,
                    'to_location_id' => null,
                    'transaction_type' => 'DISPATCH',
                    'quantity' => $quantity,
                    'unit_code' => $lot->unit_code,
                    'idempotency_key' => 'dispatch-reservation:'.$reservation->id,
                    'source_document_type' => 'SHIPMENT',
                    'source_document_id' => $shipmentId,
                    'source_line_id' => $line->id,
                    'reason' => $data['reason'] ?? 'Reserved finished goods dispatched to customer.',
                    'occurred_at' => now(),
                    'recorded_by' => $actorId,
                    'created_at' => now(),
                ]);
            }

            DB::table('shipments')->where('tenant_id', $tenantId)->where('id', $shipmentId)->update([
                'status' => 'DISPATCHED', 'released_by' => $actorId, 'dispatched_by' => $actorId,
                'dispatched_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('finished_goods_reservations')->where('tenant_id', $tenantId)->where('id', $reservation->id)->update([
                'status' => 'FULFILLED', 'fulfilled_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('customer_order_lines')->where('tenant_id', $tenantId)->where('id', $line->id)->update([
                'fulfilled_quantity' => DecimalQuantity::add((string) $line->fulfilled_quantity, $quantity),
                'reserved_quantity' => DecimalQuantity::subtract((string) $line->reserved_quantity, $quantity),
                'updated_at' => now(),
            ]);

            $outstanding = DB::table('customer_order_lines')->where('tenant_id', $tenantId)->where('customer_order_id', $order->id)->get();
            $allFulfilled = true;
            foreach ($outstanding as $orderLine) {
                if (DecimalQuantity::units((string) $orderLine->fulfilled_quantity) < DecimalQuantity::units((string) $orderLine->ordered_quantity)) {
                    $allFulfilled = false;
                    break;
                }
            }
            DB::table('customer_orders')->where('tenant_id', $tenantId)->where('id', $order->id)->update([
                'status' => $allFulfilled ? 'FULFILLED' : 'PART_FULFILLED',
                'updated_at' => now(),
            ]);

            return DB::table('shipments')->where('tenant_id', $tenantId)->where('id', $shipmentId)->first();
        }, 3);
    }

    public function balanceAt(string $tenantId, string $lotId, string $locationId): string
    {
        $rows = DB::table('finished_goods_transactions')->where('tenant_id', $tenantId)
            ->where('finished_goods_lot_id', $lotId)
            ->where(function ($query) use ($locationId): void {
                $query->where('from_location_id', $locationId)->orWhere('to_location_id', $locationId);
            })->get(['from_location_id','to_location_id','quantity']);

        $units = 0;
        foreach ($rows as $row) {
            if ($row->to_location_id === $locationId) {
                $units += DecimalQuantity::units((string) $row->quantity);
            }
            if ($row->from_location_id === $locationId) {
                $units -= DecimalQuantity::units((string) $row->quantity);
            }
        }

        return DecimalQuantity::format($units);
    }
}
