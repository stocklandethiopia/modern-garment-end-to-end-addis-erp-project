<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Domain-role effective periods require PostgreSQL.');
        }

        DB::unprepared(<<<'SQL'
-- The original partial unique index prohibited a second ACTIVE row even
-- when its effective period did not overlap an expired historical period.
-- Replace it with an ordinary lookup index and enforce real period overlap
-- under a per-tenant/member/role transaction advisory lock.
DROP INDEX IF EXISTS public.tenant_domain_role_assignments_open_uq;

CREATE INDEX IF NOT EXISTS tenant_domain_role_assignments_period_lookup_idx
    ON public.tenant_domain_role_assignments
       (tenant_id, user_id, role_code, status, valid_from, valid_to);

CREATE OR REPLACE FUNCTION public.prevent_overlapping_domain_role_period()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_overlap boolean;
BEGIN
    IF NEW.status NOT IN ('PENDING', 'ACTIVE') THEN
        RETURN NEW;
    END IF;

    PERFORM pg_advisory_xact_lock(
        hashtextextended(
            NEW.tenant_id::text || ':domain-role:' || NEW.user_id::text || ':' || NEW.role_code,
            0
        )
    );

    SELECT EXISTS (
        SELECT 1
        FROM public.tenant_domain_role_assignments existing
        WHERE existing.tenant_id = NEW.tenant_id
          AND existing.user_id = NEW.user_id
          AND existing.role_code = NEW.role_code
          AND existing.status IN ('PENDING', 'ACTIVE')
          AND existing.id <> NEW.id
          AND (NEW.valid_to IS NULL OR existing.valid_from <= NEW.valid_to)
          AND (existing.valid_to IS NULL OR existing.valid_to >= NEW.valid_from)
    ) INTO v_overlap;

    IF v_overlap THEN
        RAISE EXCEPTION 'A pending or active domain-role assignment overlaps the requested effective period.'
            USING ERRCODE = '23P01';
    END IF;

    RETURN NEW;
END;
$$;

REVOKE ALL ON FUNCTION public.prevent_overlapping_domain_role_period() FROM PUBLIC;

CREATE TRIGGER tenant_domain_role_period_overlap_guard
BEFORE INSERT OR UPDATE OF tenant_id, user_id, role_code, valid_from, valid_to, status
ON public.tenant_domain_role_assignments
FOR EACH ROW
EXECUTE FUNCTION public.prevent_overlapping_domain_role_period();
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS tenant_domain_role_period_overlap_guard
    ON public.tenant_domain_role_assignments;
DROP FUNCTION IF EXISTS public.prevent_overlapping_domain_role_period();

-- Restore the pre-000029 index only when the stored records still satisfy it.
-- If multiple active periods now exist, this rollback fails rather than
-- silently removing the effective-period integrity guarantee.
DROP INDEX IF EXISTS public.tenant_domain_role_assignments_period_lookup_idx;
CREATE UNIQUE INDEX tenant_domain_role_assignments_open_uq
    ON public.tenant_domain_role_assignments (tenant_id, user_id, role_code)
    WHERE status IN ('PENDING', 'ACTIVE');
SQL);
    }
};
