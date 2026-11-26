<?php

namespace App\Services\Sourcing;

use App\Services\Inventory\InventoryLedgerService;
use App\Services\Support\DecimalQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SourcingService
{
    public function __construct(private readonly InventoryLedgerService $inventoryLedger)
    {
    }

    public function createSupplier(string $tenantId, string $actorId, array $data): object
    {
        $id = (string) Str::uuid();
        DB::table('supplier_accounts')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'supplier_code' => $data['supplier_code'],
            'legal_name' => $data['legal_name'],
            'supplier_type' => $data['supplier_type'],
            'country_code' => strtoupper($data['country_code']),
            'tin_reference' => $data['tin_reference'] ?? null,
            'licence_reference' => $data['licence_reference'] ?? null,
            'due_diligence_status' => 'REVIEW_REQUIRED',
            'quality_status' => 'UNASSESSED',
            'created_by' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('supplier_accounts')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function approveSupplier(string $tenantId, string $actorId, string $supplierId, array $evidence): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $supplierId, $evidence): object {
            $supplier = DB::table('supplier_accounts')
                ->where('tenant_id', $tenantId)->where('id', $supplierId)->lockForUpdate()->first();

            if ($supplier === null) {
                throw ValidationException::withMessages(['supplier' => 'Supplier was not found in this tenant.']);
            }
            if (trim((string) ($evidence['due_diligence_reference'] ?? '')) === '' ||
                trim((string) ($evidence['quality_approval_reference'] ?? '')) === '') {
                throw ValidationException::withMessages(['evidence' => 'Due-diligence and quality approval evidence references are required.']);
            }

            DB::table('supplier_accounts')->where('tenant_id', $tenantId)->where('id', $supplierId)->update([
                'due_diligence_status' => 'APPROVED',
                'quality_status' => 'APPROVED',
                'due_diligence_reference' => $evidence['due_diligence_reference'],
                'quality_approval_reference' => $evidence['quality_approval_reference'],
                'approved_at' => now(),
                'approved_by' => $actorId,
                'updated_at' => now(),
            ]);

            return DB::table('supplier_accounts')->where('tenant_id', $tenantId)->where('id', $supplierId)->first();
        }, 3);
    }

    public function createPurchaseOrder(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $supplier = DB::table('supplier_accounts')->where('tenant_id', $tenantId)
                ->where('id', $data['supplier_id'])->lockForUpdate()->first();

            if ($supplier === null || $supplier->due_diligence_status !== 'APPROVED' || $supplier->quality_status !== 'APPROVED') {
                throw ValidationException::withMessages(['supplier_id' => 'An approved supplier with approved quality status is required before ordering.']);
            }

            $legalEntity = DB::table('legal_entities')->where('tenant_id', $tenantId)->where('id', $data['legal_entity_id'])->exists();
            if (! $legalEntity) {
                throw ValidationException::withMessages(['legal_entity_id' => 'The legal entity is not in this tenant.']);
            }

            $orderId = (string) Str::uuid();
            DB::table('purchase_orders')->insert([
                'id' => $orderId,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'],
                'supplier_id' => $supplier->id,
                'sourcing_event_id' => $data['sourcing_event_id'] ?? null,
                'order_code' => $data['order_code'],
                'currency' => strtoupper($data['currency']),
                'order_date' => $data['order_date'],
                'requested_delivery_date' => $data['requested_delivery_date'] ?? null,
                'incoterm_code' => $data['incoterm_code'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($data['lines'] as $index => $line) {
                DB::table('purchase_order_lines')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'purchase_order_id' => $orderId,
                    'line_no' => $index + 1,
                    'item_code' => $line['item_code'],
                    'material_type' => $line['material_type'],
                    'description' => $line['description'],
                    'specification_reference' => $line['specification_reference'] ?? null,
                    'unit_code' => strtoupper($line['unit_code']),
                    'ordered_quantity' => $line['ordered_quantity'],
                    'accepted_quantity' => 0,
                    'unit_price' => $line['unit_price'],
                    'required_delivery_date' => $line['required_delivery_date'] ?? $data['requested_delivery_date'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function submitPurchaseOrder(string $tenantId, string $actorId, string $orderId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderId): object {
            $order = DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'DRAFT') {
                throw ValidationException::withMessages(['purchase_order' => 'Only a draft purchase order can be submitted.']);
            }
            if (! DB::table('purchase_order_lines')->where('tenant_id', $tenantId)->where('purchase_order_id', $orderId)->exists()) {
                throw ValidationException::withMessages(['lines' => 'A purchase order requires at least one line.']);
            }

            DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->update([
                'status' => 'SUBMITTED',
                'submitted_by' => $actorId,
                'submitted_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function decidePurchaseOrder(string $tenantId, string $actorId, string $orderId, bool $approve, string $reason): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $orderId, $approve, $reason): object {
            $order = DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'SUBMITTED') {
                throw ValidationException::withMessages(['purchase_order' => 'Only a submitted purchase order can be decided.']);
            }
            if ($order->created_by === $actorId || $order->submitted_by === $actorId) {
                throw ValidationException::withMessages(['approver' => 'The requester or submitter cannot approve their own purchase order.']);
            }
            if (trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'An approval or rejection reason is required.']);
            }

            DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->update([
                'status' => $approve ? 'APPROVED' : 'CANCELLED',
                'approved_by' => $actorId,
                'approved_at' => now(),
                'change_reason' => $reason,
                'updated_at' => now(),
            ]);

            return DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function markPurchaseOrderSent(string $tenantId, string $orderId): object
    {
        return DB::transaction(function () use ($tenantId, $orderId): object {
            $order = DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->lockForUpdate()->first();
            if ($order === null || $order->status !== 'APPROVED') {
                throw ValidationException::withMessages(['purchase_order' => 'Only an approved purchase order can be marked as sent.']);
            }
            DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->update(['status' => 'SENT', 'updated_at' => now()]);
            return DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $orderId)->first();
        }, 3);
    }

    public function recordGoodsReceipt(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $order = DB::table('purchase_orders')->where('tenant_id', $tenantId)
                ->where('id', $data['purchase_order_id'])->lockForUpdate()->first();

            if ($order === null || ! in_array($order->status, ['SENT', 'PART_RECEIVED'], true)) {
                throw ValidationException::withMessages(['purchase_order_id' => 'The purchase order must be SENT or PART_RECEIVED before receipt.']);
            }

            $location = DB::table('inventory_locations')->where('tenant_id', $tenantId)->where('id', $data['destination_location_id'])->first();
            if ($location === null || strtoupper($location->location_type) !== 'QUARANTINE') {
                throw ValidationException::withMessages(['destination_location_id' => 'Goods must first be received into a QUARANTINE inventory location.']);
            }

            $receiptId = (string) Str::uuid();
            DB::table('goods_receipts')->insert([
                'id' => $receiptId,
                'tenant_id' => $tenantId,
                'purchase_order_id' => $order->id,
                'destination_location_id' => $location->id,
                'receipt_code' => $data['receipt_code'],
                'supplier_delivery_reference' => $data['supplier_delivery_reference'] ?? null,
                'received_at' => $data['received_at'] ?? now(),
                'status' => 'INSPECTION_PENDING',
                'received_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($data['lines'] as $index => $line) {
                $poLine = DB::table('purchase_order_lines')
                    ->where('tenant_id', $tenantId)
                    ->where('purchase_order_id', $order->id)
                    ->where('id', $line['purchase_order_line_id'])
                    ->lockForUpdate()
                    ->first();

                if ($poLine === null) {
                    throw ValidationException::withMessages(['purchase_order_line_id' => 'Each receipt line must belong to the selected purchase order.']);
                }

                $alreadyReceived = DB::table('goods_receipt_lines as grl')
                    ->join('goods_receipts as gr', function ($join): void {
                        $join->on('gr.tenant_id', '=', 'grl.tenant_id')->on('gr.id', '=', 'grl.goods_receipt_id');
                    })
                    ->where('grl.tenant_id', $tenantId)
                    ->where('grl.purchase_order_line_id', $poLine->id)
                    ->whereNotIn('gr.status', ['CANCELLED'])
                    ->sum('grl.quantity_received');

                $remaining = DecimalQuantity::subtract((string) $poLine->ordered_quantity, (string) $alreadyReceived);
                if (DecimalQuantity::exceeds((string) $line['quantity_received'], $remaining)) {
                    throw ValidationException::withMessages(['quantity_received' => 'Receipt quantity exceeds the outstanding purchase order quantity.']);
                }

                DB::table('goods_receipt_lines')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'goods_receipt_id' => $receiptId,
                    'purchase_order_line_id' => $poLine->id,
                    'line_no' => $index + 1,
                    'quantity_received' => $line['quantity_received'],
                    'quantity_accepted' => 0,
                    'quantity_rejected' => 0,
                    'unit_code' => $poLine->unit_code,
                    'inspection_required' => true,
                    'discrepancy_reason' => $line['discrepancy_reason'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('goods_receipts')->where('tenant_id', $tenantId)->where('id', $receiptId)->first();
        }, 3);
    }

    public function materializeInspectedReceiptLine(string $tenantId, string $actorId, string $receiptLineId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $receiptLineId, $data): object {
            $line = DB::table('goods_receipt_lines')->where('tenant_id', $tenantId)->where('id', $receiptLineId)->lockForUpdate()->first();
            if ($line === null) {
                throw ValidationException::withMessages(['goods_receipt_line' => 'Receipt line not found.']);
            }
            if (DB::table('receipt_line_material_lots')->where('tenant_id', $tenantId)->where('goods_receipt_line_id', $receiptLineId)->exists()) {
                throw ValidationException::withMessages(['goods_receipt_line' => 'This receipt line has already been materialized.']);
            }

            $inspection = DB::table('quality_inspections')->where('tenant_id', $tenantId)
                ->where('id', $data['quality_inspection_id'])->first();
            if ($inspection === null || $inspection->subject_type !== 'GOODS_RECEIPT_LINE' ||
                $inspection->subject_id !== $receiptLineId ||
                ! in_array($inspection->result_status, ['PASS', 'PASS_WITH_DEVIATION'], true) ||
                $inspection->reviewed_by === null || $inspection->reviewed_at === null) {
                throw ValidationException::withMessages(['quality_inspection_id' => 'A reviewed passing incoming-material inspection linked to this receipt line is required.']);
            }

            $receivedUnits = DecimalQuantity::units((string) $line->quantity_received);
            $acceptedUnits = DecimalQuantity::units((string) $data['materialized_quantity']);
            $receivedQty = DecimalQuantity::format($receivedUnits);
            $acceptedQty = DecimalQuantity::format($acceptedUnits);
            if ($acceptedUnits <= 0 || $acceptedUnits > $receivedUnits) {
                throw ValidationException::withMessages(['materialized_quantity' => 'Materialized quantity must be positive and cannot exceed received quantity.']);
            }

            $receipt = DB::table('goods_receipts')->where('tenant_id', $tenantId)->where('id', $line->goods_receipt_id)->first();
            $poLine = DB::table('purchase_order_lines')->where('tenant_id', $tenantId)->where('id', $line->purchase_order_line_id)->first();
            $order = DB::table('purchase_orders')->where('tenant_id', $tenantId)->where('id', $receipt->purchase_order_id)->first();

            if (! in_array($poLine->material_type, ['FABRIC','TRIM','PACKAGING','CHEMICAL'], true)) {
                throw ValidationException::withMessages(['material_type' => 'Service and subcontract-process receipt lines are not inventory material lots.']);
            }

            $lotId = (string) Str::uuid();
            DB::table('material_lots')->insert([
                'id' => $lotId,
                'tenant_id' => $tenantId,
                'goods_receipt_line_id' => $line->id,
                'supplier_id' => $order->supplier_id,
                'material_code' => $poLine->item_code,
                'material_type' => $poLine->material_type,
                'supplier_lot_reference' => $data['supplier_lot_reference'] ?? null,
                'supplier_dye_lot_reference' => $data['supplier_dye_lot_reference'] ?? null,
                'country_of_origin' => $data['country_of_origin'] ?? null,
                'shade_code' => $data['shade_code'] ?? null,
                'specification_snapshot' => json_encode($data['specification_snapshot'] ?? []),
                'status' => 'QUARANTINE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $rollId = null;
            $trimId = null;
            if ($poLine->material_type === 'FABRIC') {
                $rollId = (string) Str::uuid();
                DB::table('fabric_rolls')->insert([
                    'id' => $rollId, 'tenant_id' => $tenantId, 'material_lot_id' => $lotId,
                    'roll_code' => $data['roll_code'], 'shade_code' => $data['shade_code'] ?? null,
                    'measured_length' => $acceptedQty, 'unit_code' => $poLine->unit_code,
                    'measured_width' => $data['measured_width'] ?? null, 'gsm' => $data['gsm'] ?? null,
                    'roll_attributes' => json_encode($data['roll_attributes'] ?? []), 'status' => 'QUARANTINE',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            } elseif ($poLine->material_type === 'TRIM') {
                $trimId = (string) Str::uuid();
                DB::table('trim_lots')->insert([
                    'id' => $trimId, 'tenant_id' => $tenantId, 'material_lot_id' => $lotId,
                    'trim_lot_code' => $data['trim_lot_code'], 'approved_artwork_reference' => $data['approved_artwork_reference'] ?? null,
                    'lot_attributes' => json_encode($data['lot_attributes'] ?? []), 'status' => 'QUARANTINE',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('receipt_line_material_lots')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'goods_receipt_line_id' => $line->id,
                'material_lot_id' => $lotId,
                'quality_inspection_id' => $inspection->id,
                'materialized_quantity' => $acceptedQty,
                'unit_code' => $poLine->unit_code,
                'created_by' => $actorId,
                'created_at' => now(),
            ]);

            $this->inventoryLedger->postMovement($tenantId, $actorId, [
                'material_lot_id' => $lotId,
                'fabric_roll_id' => $rollId,
                'trim_lot_id' => $trimId,
                'from_location_id' => null,
                'to_location_id' => $receipt->destination_location_id,
                'movement_type' => 'RECEIPT',
                'quantity' => $acceptedQty,
                'unit_code' => $poLine->unit_code,
                'idempotency_key' => 'goods-receipt-line:'.$line->id.':material-lot:'.$lotId,
                'source_document_type' => 'GOODS_RECEIPT',
                'source_document_id' => $receipt->id,
                'source_line_id' => $line->id,
                'reason' => 'Incoming material accepted by a reviewed inspection; stock remains in quarantine until a separate release decision.',
            ]);

            DB::table('goods_receipt_lines')->where('tenant_id', $tenantId)->where('id', $line->id)->update([
                'quantity_accepted' => $acceptedQty,
                'quantity_rejected' => DecimalQuantity::format(max(0, $receivedUnits - $acceptedUnits)),
                'updated_at' => now(),
            ]);

            $acceptedOnLine = (string) DB::table('goods_receipt_lines')->where('tenant_id', $tenantId)->where('purchase_order_line_id', $poLine->id)->sum('quantity_accepted');
            DB::table('purchase_order_lines')->where('tenant_id', $tenantId)->where('id', $poLine->id)->update([
                'accepted_quantity' => $acceptedOnLine, 'updated_at' => now(),
            ]);

            return DB::table('material_lots')->where('tenant_id', $tenantId)->where('id', $lotId)->first();
        }, 3);
    }
}
