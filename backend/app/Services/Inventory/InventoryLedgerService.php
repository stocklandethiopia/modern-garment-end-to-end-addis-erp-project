<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class InventoryLedgerService
{
    /**
     * Append one stock movement. The material-lot lock serializes balance checks
     * for every roll/trim item inside the lot; inventory_transactions is append-only.
     */
    public function postMovement(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $existing = DB::table('inventory_transactions')
                ->where('tenant_id', $tenantId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $lot = DB::table('material_lots')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['material_lot_id'])
                ->lockForUpdate()
                ->first();

            if ($lot === null) {
                throw ValidationException::withMessages(['material_lot_id' => 'The material lot is not available in this tenant.']);
            }

            $rollId = $data['fabric_roll_id'] ?? null;
            $trimId = $data['trim_lot_id'] ?? null;
            if ($rollId !== null && $trimId !== null) {
                throw ValidationException::withMessages(['traceability' => 'A movement must reference a fabric roll or trim lot, never both.']);
            }

            if ($rollId !== null) {
                $roll = DB::table('fabric_rolls')->where('tenant_id', $tenantId)->where('material_lot_id', $lot->id)->where('id', $rollId)->lockForUpdate()->first();
                if ($roll === null) {
                    throw ValidationException::withMessages(['fabric_roll_id' => 'The fabric roll does not belong to the material lot.']);
                }
            }

            if ($trimId !== null) {
                $trim = DB::table('trim_lots')->where('tenant_id', $tenantId)->where('material_lot_id', $lot->id)->where('id', $trimId)->lockForUpdate()->first();
                if ($trim === null) {
                    throw ValidationException::withMessages(['trim_lot_id' => 'The trim lot does not belong to the material lot.']);
                }
            }

            $type = $data['movement_type'];
            $from = $data['from_location_id'] ?? null;
            $to = $data['to_location_id'] ?? null;
            $this->validateMovementShape($type, $from, $to);

            if ($type === 'RECEIPT') {
                if ($lot->status !== 'QUARANTINE') {
                    throw ValidationException::withMessages(['material_lot_id' => 'New receipts must remain in QUARANTINE until a quality release decision.']);
                }
                $destination = DB::table('inventory_locations')->where('tenant_id', $tenantId)->where('id', $to)->first();
                if ($destination === null || strtoupper($destination->location_type) !== 'QUARANTINE') {
                    throw ValidationException::withMessages(['to_location_id' => 'Receipt stock must first be placed into a QUARANTINE location.']);
                }
            }

            $outboundTypes = ['TRANSFER', 'ISSUE', 'SCRAP', 'CONSUMPTION', 'DISPATCH'];
            if (in_array($type, $outboundTypes, true)) {
                if ($lot->status !== 'AVAILABLE' && $type !== 'SCRAP') {
                    throw ValidationException::withMessages(['material_lot_id' => 'Only AVAILABLE material can be issued, consumed, transferred or dispatched.']);
                }
                $balance = $this->balanceAt($tenantId, $lot->id, $rollId, $trimId, $from);
                if (bccomp((string) $balance, (string) $data['quantity'], 6) < 0) {
                    throw ValidationException::withMessages(['quantity' => 'The movement exceeds available quantity at the source location.']);
                }
            }

            if (in_array($type, ['TRANSFER', 'ISSUE', 'CONSUMPTION', 'DISPATCH'], true) && $lot->status !== 'AVAILABLE') {
                throw ValidationException::withMessages(['material_lot_id' => 'Quarantined or blocked stock cannot be issued or dispatched.']);
            }

            $id = (string) Str::uuid();
            DB::table('inventory_transactions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'material_lot_id' => $lot->id,
                'fabric_roll_id' => $rollId,
                'trim_lot_id' => $trimId,
                'from_location_id' => $from,
                'to_location_id' => $to,
                'movement_type' => $type,
                'quantity' => $data['quantity'],
                'unit_code' => $data['unit_code'],
                'idempotency_key' => $data['idempotency_key'],
                'source_document_type' => $data['source_document_type'],
                'source_document_id' => $data['source_document_id'],
                'source_line_id' => $data['source_line_id'] ?? null,
                'reason' => $data['reason'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'recorded_by' => $actorId,
                'created_at' => now(),
            ]);

            return DB::table('inventory_transactions')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    public function balanceAt(string $tenantId, string $lotId, ?string $rollId, ?string $trimId, string $locationId): string
    {
        $query = DB::table('inventory_transactions')
            ->where('tenant_id', $tenantId)
            ->where('material_lot_id', $lotId)
            ->where(function ($q) use ($locationId): void {
                $q->where('from_location_id', $locationId)->orWhere('to_location_id', $locationId);
            });

        if ($rollId !== null) {
            $query->where('fabric_roll_id', $rollId);
        } elseif ($trimId !== null) {
            $query->where('trim_lot_id', $trimId);
        } else {
            $query->whereNull('fabric_roll_id')->whereNull('trim_lot_id');
        }

        $rows = $query->get(['from_location_id', 'to_location_id', 'quantity']);
        $balance = '0.000000';
        foreach ($rows as $row) {
            if ($row->to_location_id === $locationId) {
                $balance = bcadd($balance, (string) $row->quantity, 6);
            }
            if ($row->from_location_id === $locationId) {
                $balance = bcsub($balance, (string) $row->quantity, 6);
            }
        }

        return $balance;
    }

    private function validateMovementShape(string $type, ?string $from, ?string $to): void
    {
        $valid = match ($type) {
            'RECEIPT', 'RETURN', 'CUSTOMER_RETURN' => $from === null && $to !== null,
            'TRANSFER' => $from !== null && $to !== null && $from !== $to,
            'ISSUE', 'SCRAP', 'CONSUMPTION', 'DISPATCH' => $from !== null && $to === null,
            'ADJUSTMENT' => ($from === null) xor ($to === null),
            default => false,
        };

        if (! $valid) {
            throw ValidationException::withMessages(['movement_type' => 'The source and destination locations do not match the movement type.']);
        }
    }
}
