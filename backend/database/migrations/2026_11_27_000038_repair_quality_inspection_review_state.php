<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Quality inspection review state requires PostgreSQL.');
        }

        DB::statement('ALTER TABLE quality_inspections ADD COLUMN submitted_result_status varchar(24)');

        DB::unprepared(<<<'SQL'
DO $$
DECLARE
    constraint_record record;
BEGIN
    FOR constraint_record IN
        SELECT conname
          FROM pg_constraint
         WHERE conrelid = 'public.quality_inspections'::regclass
           AND contype = 'c'
           AND pg_get_constraintdef(oid) ILIKE '%result_status%'
    LOOP
        EXECUTE format(
            'ALTER TABLE public.quality_inspections DROP CONSTRAINT %I',
            constraint_record.conname
        );
    END LOOP;
END $$;
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE quality_inspections
ADD CONSTRAINT quality_inspections_result_state_check
CHECK (
    result_status IN (
        'DRAFT',
        'IN_PROGRESS',
        'SUBMITTED_PENDING_REVIEW',
        'PASS',
        'PASS_WITH_DEVIATION',
        'FAIL',
        'CONTAINED',
        'CANCELLED'
    )
    AND (
        result_status NOT IN ('PASS', 'PASS_WITH_DEVIATION')
        OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL)
    )
    AND (
        result_status <> 'SUBMITTED_PENDING_REVIEW'
        OR (
            submitted_result_status IN ('PASS', 'PASS_WITH_DEVIATION')
            AND reviewed_by IS NULL
            AND reviewed_at IS NULL
            AND inspected_at IS NOT NULL
        )
    )
)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE quality_inspections
ADD CONSTRAINT quality_inspections_submitted_result_status_check
CHECK (
    submitted_result_status IS NULL
    OR submitted_result_status IN ('PASS', 'PASS_WITH_DEVIATION')
)
SQL);
    }

    public function down(): void
    {
        if (DB::table('quality_inspections')
            ->where('result_status', 'SUBMITTED_PENDING_REVIEW')
            ->exists()) {
            throw new RuntimeException('Cannot reverse quality review-state migration while submitted inspections are awaiting review.');
        }

        DB::statement('ALTER TABLE quality_inspections DROP CONSTRAINT IF EXISTS quality_inspections_submitted_result_status_check');
        DB::statement('ALTER TABLE quality_inspections DROP CONSTRAINT IF EXISTS quality_inspections_result_state_check');
        DB::statement('ALTER TABLE quality_inspections DROP COLUMN IF EXISTS submitted_result_status');

        DB::statement(<<<'SQL'
ALTER TABLE quality_inspections
ADD CONSTRAINT quality_inspections_result_status_check
CHECK (
    result_status IN (
        'DRAFT',
        'IN_PROGRESS',
        'PASS',
        'PASS_WITH_DEVIATION',
        'FAIL',
        'CONTAINED',
        'CANCELLED'
    )
)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE quality_inspections
ADD CONSTRAINT quality_inspections_reviewed_result_check
CHECK (
    result_status NOT IN ('PASS', 'PASS_WITH_DEVIATION')
    OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL)
)
SQL);
    }
};
