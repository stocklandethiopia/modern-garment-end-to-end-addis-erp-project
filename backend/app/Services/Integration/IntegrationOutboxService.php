<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IntegrationOutboxService
{
    public function claimBatch(string $tenantId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        return DB::transaction(function () use ($tenantId, $limit): array {
            $events = DB::table('integration_outbox_events')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['PENDING', 'RETRY'])
                ->where('available_at', '<=', now())
                ->orderBy('created_at')
                ->limit($limit)
                ->lock('FOR UPDATE SKIP LOCKED')
                ->get();

            $claimed = [];
            foreach ($events as $event) {
                DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $event->id)->update([
                    'status' => 'PROCESSING',
                    'attempt_count' => $event->attempt_count + 1,
                    'locked_at' => now(),
                    'updated_at' => now(),
                ]);
                $event->status = 'PROCESSING';
                $event->attempt_count++;
                $claimed[] = $event;
            }

            return $claimed;
        }, 3);
    }

    public function markDelivered(string $tenantId, string $eventId): object
    {
        return DB::transaction(function () use ($tenantId, $eventId): object {
            $event = DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $eventId)->lockForUpdate()->first();
            if ($event === null || $event->status !== 'PROCESSING') {
                throw ValidationException::withMessages(['event' => 'Only a claimed integration event can be marked delivered.']);
            }
            DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $eventId)->update([
                'status' => 'DELIVERED',
                'delivered_at' => now(),
                'locked_at' => null,
                'last_error_code' => null,
                'last_error_summary' => null,
                'updated_at' => now(),
            ]);

            return DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $eventId)->first();
        }, 3);
    }

    public function markFailed(string $tenantId, string $eventId, string $errorCode, string $summary, int $maxAttempts = 8): object
    {
        return DB::transaction(function () use ($tenantId, $eventId, $errorCode, $summary, $maxAttempts): object {
            $event = DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $eventId)->lockForUpdate()->first();
            if ($event === null || $event->status !== 'PROCESSING') {
                throw ValidationException::withMessages(['event' => 'Only a claimed integration event can be marked failed.']);
            }
            $dead = $event->attempt_count >= $maxAttempts;
            $backoffSeconds = min(3600, 5 * (2 ** min(10, max(0, $event->attempt_count - 1))));

            DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $eventId)->update([
                'status' => $dead ? 'DEAD_LETTER' : 'RETRY',
                'available_at' => now()->addSeconds($backoffSeconds),
                'locked_at' => null,
                'last_error_code' => mb_substr($errorCode, 0, 100),
                'last_error_summary' => mb_substr($summary, 0, 500),
                'updated_at' => now(),
            ]);

            return DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $eventId)->first();
        }, 3);
    }
}
