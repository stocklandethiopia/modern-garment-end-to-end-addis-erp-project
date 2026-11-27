<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Payroll line evidence protection requires PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.guard_payroll_run_line_insert_while_draft()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    parent_status varchar(24);
BEGIN
    SELECT status
      INTO parent_status
      FROM public.payroll_run_drafts
     WHERE tenant_id = NEW.tenant_id
       AND id = NEW.payroll_run_draft_id
     FOR UPDATE;

    IF parent_status IS DISTINCT FROM 'DRAFT' THEN
        RAISE EXCEPTION 'Payroll lines can only be added while the parent run is DRAFT.'
            USING ERRCODE = '55000';
    END IF;

    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.reject_payroll_run_line_mutation()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    RAISE EXCEPTION 'Payroll calculation lines are immutable; corrections require a new payroll run.'
        USING ERRCODE = '55000';
END;
$$;

CREATE TRIGGER payroll_run_lines_insert_draft_only
BEFORE INSERT ON public.payroll_run_lines
FOR EACH ROW
EXECUTE FUNCTION public.guard_payroll_run_line_insert_while_draft();

CREATE TRIGGER payroll_run_lines_immutable
BEFORE UPDATE OR DELETE ON public.payroll_run_lines
FOR EACH ROW
EXECUTE FUNCTION public.reject_payroll_run_line_mutation();

REVOKE UPDATE, DELETE ON TABLE public.payroll_run_lines FROM PUBLIC, app_runtime;
GRANT SELECT, INSERT ON TABLE public.payroll_run_lines TO app_runtime;
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS payroll_run_lines_insert_draft_only ON public.payroll_run_lines');
        DB::statement('DROP TRIGGER IF EXISTS payroll_run_lines_immutable ON public.payroll_run_lines');
        DB::statement('DROP FUNCTION IF EXISTS public.guard_payroll_run_line_insert_while_draft()');
        DB::statement('DROP FUNCTION IF EXISTS public.reject_payroll_run_line_mutation()');
        DB::statement('GRANT SELECT, INSERT, UPDATE ON TABLE public.payroll_run_lines TO app_runtime');
    }
};
