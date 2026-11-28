<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeviceScanResolutionService
{
    public function resolve(string $tenantId, string $scanEventId): object
    {
        return DB::transaction(function () use ($tenantId, $scanEventId): object {
            $event = DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('id', $scanEventId)->lockForUpdate()->first();
            if ($event === null) {
                throw ValidationException::withMessages(['scan_event' => 'The scan event is not available in this tenant.']);
            }
            if ($event->resolution_status !== 'PENDING') {
                return $event;
            }

            $value = trim((string) $event->scanned_value);
            $candidates = [];
            $queries = [
                ['fabric_rolls', 'roll_code', 'FABRIC_ROLL'],
                ['trim_lots', 'trim_lot_code', 'TRIM_LOT'],
                ['cutting_bundles', 'bundle_code', 'CUTTING_BUNDLE'],
                ['finished_goods_lots', 'lot_code', 'FINISHED_GOODS_LOT'],
                ['customer_orders', 'order_number', 'CUSTOMER_ORDER'],
                ['shipments', 'tracking_reference', 'SHIPMENT'],
            ];

            foreach ($queries as [$table, $column, $type]) {
                $row = DB::table($table)->where('tenant_id', $tenantId)->where($column, $value)->first();
                if ($row !== null) {
                    $candidates[] = ['type' => $type, 'id' => $row->id];
                }
            }

            if (count($candidates) === 1) {
                $target = $candidates[0];
                DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('id', $scanEventId)->update([
                    'resolution_status' => 'RESOLVED',
                    'resolution_reference_type' => $target['type'],
                    'resolution_reference_id' => $target['id'],
                ]);
            } elseif (count($candidates) > 1) {
                DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('id', $scanEventId)->update([
                    'resolution_status' => 'AMBIGUOUS',
                    'raw_metadata' => json_encode([
                        'original' => json_decode($event->raw_metadata, true, 512, JSON_THROW_ON_ERROR),
                        'resolution_candidates' => $candidates,
                    ], JSON_THROW_ON_ERROR),
                ]);
            } else {
                DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('id', $scanEventId)->update([
                    'resolution_status' => 'REJECTED',
                    'raw_metadata' => json_encode([
                        'original' => json_decode($event->raw_metadata, true, 512, JSON_THROW_ON_ERROR),
                        'resolution_note' => 'No tenant-scoped identifier match was found. No inventory or production action was performed.',
                    ], JSON_THROW_ON_ERROR),
                ]);
            }

            return DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('id', $scanEventId)->first();
        }, 3);
    }
}
