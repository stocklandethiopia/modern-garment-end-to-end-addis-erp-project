<?php

declare(strict_types=1);

namespace Tests\\Feature;

use Illuminate\\Support\\Facades\\DB;
use Tests\\TestCase;

class PayrollRunLineIntegrityTest extends TestCase
{
    public function test_runtime_role_cannot_update_or_delete_calculated_payroll_lines(): void
    {
        $runtime = DB::connection('pgsql');

        $this->assertTrue((bool) $runtime->scalar(
            'SELECT has_table_privilege(?, ?, ?)',
            ['app_runtime', 'payroll_run_lines', 'SELECT'],
        ));
        $this->assertTrue((bool) $runtime->scalar(
            'SELECT has_table_privilege(?, ?, ?)',
            ['app_runtime', 'payroll_run_lines', 'INSERT'],
        ));
        $this->assertFalse((bool) $runtime->scalar(
            'SELECT has_table_privilege(?, ?, ?)',
            ['app_runtime', 'payroll_run_lines', 'UPDATE'],
        ));
        $this->assertFalse((bool) $runtime->scalar(
            'SELECT has_table_privilege(?, ?, ?)',
            ['app_runtime', 'payroll_run_lines', 'DELETE'],
        ));
    }

    public function test_payroll_line_insert_and_immutability_triggers_are_installed(): void
    {
        $runtime = DB::connection('pgsql');

        $triggers = $runtime->table('pg_trigger')
            ->where('tgrelid', 'payroll_run_lines' . '::regclass')
            ->where('NOT tgisinternal')
            ->pluck('tgname')
            ->all();

        $this->assertContains('payroll_run_lines_insert_draft_only', $triggers);
        $this->assertContains('payroll_run_lines_immutable', $triggers);
    }
}
