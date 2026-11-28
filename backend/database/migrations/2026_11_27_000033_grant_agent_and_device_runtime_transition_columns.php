<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Agent and device runtime transition grants require PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
-- Agent run finalization updates only terminal evidence fields.
GRANT UPDATE (status, output_sha256, token_usage, tool_trace, completed_at)
    ON agent_run_audits TO app_runtime;

-- Proposal workflow updates only its state; approval and action events remain append-only.
GRANT UPDATE (status)
    ON agent_action_proposals TO app_runtime;

-- Scan resolution updates only resolution outcome and resolver metadata.
GRANT UPDATE (resolution_status, resolution_reference_type, resolution_reference_id, raw_metadata)
    ON device_scan_events TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
REVOKE UPDATE (status, output_sha256, token_usage, tool_trace, completed_at)
    ON agent_run_audits FROM app_runtime;

REVOKE UPDATE (status)
    ON agent_action_proposals FROM app_runtime;

REVOKE UPDATE (resolution_status, resolution_reference_type, resolution_reference_id, raw_metadata)
    ON device_scan_events FROM app_runtime;
SQL);
    }
};
