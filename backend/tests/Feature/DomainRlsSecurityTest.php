<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('postgresql')]
class DomainRlsSecurityTest extends TestCase
{
    public function test_new_tenant_domain_tables_have_forced_rls(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL RLS verification requires PostgreSQL.');
        }

        $tables = [
            'supplier_accounts', 'sourcing_events', 'supplier_quotes', 'purchase_orders',
            'purchase_order_lines', 'goods_receipts', 'goods_receipt_lines', 'material_lots',
            'fabric_rolls', 'trim_lots', 'inventory_transactions', 'process_route_versions',
            'process_route_steps', 'production_orders', 'production_order_operations',
            'production_execution_events', 'cutting_bundles', 'bundle_operation_events',
            'quality_defect_catalog', 'quality_inspections', 'quality_nonconformances',
            'quality_corrective_actions', 'quality_release_decisions', 'customer_orders',
            'customer_order_lines', 'shipments', 'shipment_lines', 'operation_standard_versions',
            'work_centres', 'work_shift_definitions', 'capacity_calendar_slots',
            'production_plan_versions', 'production_plan_lines', 'workforce_skill_records',
            'workforce_assignments', 'incentive_policy_versions', 'incentive_calculation_drafts',
            'finance_periods', 'finance_accounts', 'finance_journals', 'finance_journal_lines',
            'statutory_rule_versions', 'payroll_run_drafts', 'payroll_run_lines',
            'document_assets', 'document_extraction_runs', 'integration_endpoint_registry',
            'integration_outbox_events', 'agent_run_audits', 'agent_action_proposals',
            'agent_approval_decisions', 'agent_action_events', 'device_scan_events',
            'receipt_line_material_lots', 'finished_goods_lots', 'finished_goods_transactions',
            'finished_goods_reservations', 'finance_journal_postings', 'finance_journal_reversals', 'customer_credit_decisions', 'tenant_domain_role_assignments',
        ];

        foreach ($tables as $table) {
            $row = DB::selectOne(
                "SELECT c.relrowsecurity AS enabled, c.relforcerowsecurity AS forced
                   FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
                  WHERE n.nspname = 'public' AND c.relname = ?",
                [$table]
            );

            self::assertNotNull($row, "Expected tenant-owned table {$table} to exist.");
            self::assertTrue((bool) $row->enabled, "RLS is not enabled on {$table}.");
            self::assertTrue((bool) $row->forced, "RLS is not forced on {$table}.");
        }
    }

    public function test_runtime_role_cannot_mutate_append_only_inventory_or_finished_goods_ledgers(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL privilege verification requires PostgreSQL.');
        }

        $roles = DB::select("SELECT 1 FROM pg_roles WHERE rolname = 'app_runtime'");
        if ($roles === []) {
            $this->markTestSkipped('The app_runtime role is provisioned by the PostgreSQL CI environment.');
        }

        $checks = DB::select(
            "SELECT
                has_table_privilege('app_runtime', 'public.inventory_transactions', 'UPDATE') AS raw_update,
                has_table_privilege('app_runtime', 'public.inventory_transactions', 'DELETE') AS raw_delete,
                has_table_privilege('app_runtime', 'public.finished_goods_transactions', 'UPDATE') AS fg_update,
                has_table_privilege('app_runtime', 'public.finished_goods_transactions', 'DELETE') AS fg_delete"
        );

        self::assertFalse((bool) $checks[0]->raw_update);
        self::assertFalse((bool) $checks[0]->raw_delete);
        self::assertFalse((bool) $checks[0]->fg_update);
        self::assertFalse((bool) $checks[0]->fg_delete);
    }

    public function test_runtime_role_sees_no_tenant_rows_without_a_tenant_context(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL RLS verification requires PostgreSQL.');
        }

        $roles = DB::select("SELECT 1 FROM pg_roles WHERE rolname = 'app_runtime'");
        if ($roles === []) {
            $this->markTestSkipped('The app_runtime role is provisioned by the PostgreSQL CI environment.');
        }

        DB::transaction(function (): void {
            DB::statement('SET LOCAL ROLE app_runtime');
            DB::selectOne("SELECT set_config('app.current_tenant_id', '', true)");
            $raw = DB::selectOne('SELECT COUNT(*) AS total FROM public.inventory_transactions');
            $finished = DB::selectOne('SELECT COUNT(*) AS total FROM public.finished_goods_transactions');
            self::assertSame(0, (int) $raw->total);
            self::assertSame(0, (int) $finished->total);
        });
    }
}


    public function test_agent_and_device_runtime_updates_are_limited_to_transition_columns(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL privilege verification requires PostgreSQL.');
        }

        $roles = DB::select("SELECT 1 FROM pg_roles WHERE rolname = 'app_runtime'");
        if ($roles === []) {
            $this->markTestSkipped('The app_runtime role is provisioned by the PostgreSQL CI environment.');
        }

        $privileges = DB::selectOne(<<<'SQL'
SELECT
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'status', 'UPDATE') AS run_status,
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'output_sha256', 'UPDATE') AS run_output_hash,
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'token_usage', 'UPDATE') AS run_token_usage,
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'tool_trace', 'UPDATE') AS run_tool_trace,
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'completed_at', 'UPDATE') AS run_completed_at,
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'input_sha256', 'UPDATE') AS run_input_hash,
    has_column_privilege('app_runtime', 'public.agent_run_audits', 'started_by', 'UPDATE') AS run_started_by,
    has_column_privilege('app_runtime', 'public.agent_action_proposals', 'status', 'UPDATE') AS proposal_status,
    has_column_privilege('app_runtime', 'public.agent_action_proposals', 'proposed_payload', 'UPDATE') AS proposal_payload,
    has_column_privilege('app_runtime', 'public.device_scan_events', 'resolution_status', 'UPDATE') AS scan_status,
    has_column_privilege('app_runtime', 'public.device_scan_events', 'resolution_reference_type', 'UPDATE') AS scan_reference_type,
    has_column_privilege('app_runtime', 'public.device_scan_events', 'resolution_reference_id', 'UPDATE') AS scan_reference_id,
    has_column_privilege('app_runtime', 'public.device_scan_events', 'raw_metadata', 'UPDATE') AS scan_raw_metadata,
    has_column_privilege('app_runtime', 'public.device_scan_events', 'event_key', 'UPDATE') AS scan_event_key,
    has_column_privilege('app_runtime', 'public.device_scan_events', 'scanned_value', 'UPDATE') AS scan_value,
    has_table_privilege('app_runtime', 'public.agent_approval_decisions', 'UPDATE') AS approval_updates,
    has_table_privilege('app_runtime', 'public.agent_action_events', 'UPDATE') AS action_event_updates,
    has_table_privilege('app_runtime', 'public.agent_approval_decisions', 'DELETE') AS approval_deletes,
    has_table_privilege('app_runtime', 'public.agent_action_events', 'DELETE') AS action_event_deletes
SQL);

        foreach ([
            'run_status', 'run_output_hash', 'run_token_usage', 'run_tool_trace',
            'run_completed_at', 'proposal_status', 'scan_status', 'scan_reference_type',
            'scan_reference_id', 'scan_raw_metadata',
        ] as $column) {
            self::assertTrue((bool) $privileges->{$column}, "app_runtime cannot update required transition column {$column}.");
        }

        foreach ([
            'run_input_hash', 'run_started_by', 'proposal_payload',
            'scan_event_key', 'scan_value', 'approval_updates', 'action_event_updates',
            'approval_deletes', 'action_event_deletes',
        ] as $column) {
            self::assertFalse((bool) $privileges->{$column}, "app_runtime has an unexpected update/delete privilege: {$column}.");
        }
    }
