<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Quality inspection review transitions require PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
DO $$
DECLARE
    review_constraint text;
BEGIN
    SELECT conname
      INTO review_constraint
      FROM pg_constraint
     WHERE conrelid = 'public.quality_inspections'::regclass
       AND contype = 'c'
       AND pg_get_constraintdef(oid) ILIKE '%reviewed_by IS NOT NULL%'
       AND pg_get_constraintdef(oid) ILIKE '%reviewed_at IS NOT NULL%';

    IF review_constraint IS NULL THEN
        RAISE EXCEPTION 'Expected quality inspection PASS review constraint was not found; refusing an unverified schema change.';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM public.quality_inspections
         WHERE (reviewed_by IS NULL) <> (reviewed_at IS NULL)
    ) THEN
        RAISE EXCEPTION 'Existing quality inspections have inconsistent reviewer metadata; reconcile these rows before applying the review-transition migration.';
    END IF;

    EXECUTE format('ALTER TABLE public.quality_inspections DROP CONSTRAINT %I', review_constraint);
END $$;

ALTER TABLE public.quality_inspections
    ADD CONSTRAINT quality_inspections_review_metadata_pair_ck
    CHECK ((reviewed_by IS NULL) = (reviewed_at IS NULL));
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
          FROM public.quality_inspections
         WHERE result_status IN ('PASS', 'PASS_WITH_DEVIATION')
           AND (reviewed_by IS NULL OR reviewed_at IS NULL)
    ) THEN
        RAISE EXCEPTION 'Cannot restore the strict PASS review constraint while submitted inspections are still awaiting review; complete or reconcile them first.';
    END IF;
END $$;

ALTER TABLE public.quality_inspections
    DROP CONSTRAINT IF EXISTS quality_inspections_review_metadata_pair_ck;

ALTER TABLE public.quality_inspections
    ADD CONSTRAINT quality_inspections_pass_requires_review_ck
    CHECK (
        (result_status NOT IN ('PASS', 'PASS_WITH_DEVIATION'))
        OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL)
    );
SQL);
    }
};
