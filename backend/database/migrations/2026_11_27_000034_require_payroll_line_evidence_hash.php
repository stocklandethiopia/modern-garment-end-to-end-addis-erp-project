<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Payroll evidence integrity requires PostgreSQL.');
        }

        DB::statement(<<<'SQL'
ALTER TABLE payroll_run_lines
ADD COLUMN calculation_evidence_sha256 char(64)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE payroll_run_lines
ADD CONSTRAINT payroll_validated_line_evidence_hash_required
CHECK (
    calculation_status <> 'VALIDATED'
    OR (
        calculation_evidence_sha256 IS NOT NULL
        AND calculation_evidence_sha256 ~ '^[0-9a-f]{64}$'
    )
) NOT VALID
SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payroll_run_lines DROP CONSTRAINT IF EXISTS payroll_validated_line_evidence_hash_required');
        DB::statement('ALTER TABLE payroll_run_lines DROP COLUMN IF EXISTS calculation_evidence_sha256');
    }
};
