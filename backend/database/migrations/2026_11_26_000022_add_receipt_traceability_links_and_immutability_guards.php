<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE receipt_line_material_lots (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    goods_receipt_line_id uuid NOT NULL,
    material_lot_id uuid NOT NULL,
    quality_inspection_id uuid,
    materialized_quantity numeric(18,6) NOT NULL CHECK (materialized_quantity > 0),
    unit_code varchar(16) NOT NULL,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, goods_receipt_line_id, material_lot_id),
    FOREIGN KEY (tenant_id, goods_receipt_line_id) REFERENCES goods_receipt_lines(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, material_lot_id) REFERENCES material_lots(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, quality_inspection_id) REFERENCES quality_inspections(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX receipt_line_material_lots_idx ON receipt_line_material_lots (tenant_id, goods_receipt_line_id);

ALTER TABLE statutory_rule_versions ALTER COLUMN effective_from SET NOT NULL;

CREATE OR REPLACE FUNCTION guard_finance_journal_line_mutation() RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    journal_status varchar(24);
    current_tenant uuid;
    journal_uuid uuid;
BEGIN
    current_tenant := COALESCE(NEW.tenant_id, OLD.tenant_id);
    journal_uuid := COALESCE(NEW.journal_id, OLD.journal_id);
    SELECT status INTO journal_status
      FROM finance_journals
     WHERE tenant_id = current_tenant AND id = journal_uuid
     FOR UPDATE;
    IF journal_status IS DISTINCT FROM 'DRAFT' THEN
        RAISE EXCEPTION 'Journal lines can only be changed while the parent journal is DRAFT.'
            USING ERRCODE = '55000';
    END IF;
    RETURN COALESCE(NEW, OLD);
END;
$$;
CREATE TRIGGER finance_journal_lines_draft_only
BEFORE INSERT OR UPDATE OR DELETE ON finance_journal_lines
FOR EACH ROW EXECUTE FUNCTION guard_finance_journal_line_mutation();

CREATE OR REPLACE FUNCTION guard_inventory_transaction_immutable() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    RAISE EXCEPTION 'Inventory transactions are append-only; corrections require a compensating movement.'
        USING ERRCODE = '55000';
END;
$$;
CREATE TRIGGER inventory_transactions_immutable
BEFORE UPDATE OR DELETE ON inventory_transactions
FOR EACH ROW EXECUTE FUNCTION guard_inventory_transaction_immutable();

CREATE OR REPLACE FUNCTION guard_route_step_draft_only() RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    route_status varchar(24);
    current_tenant uuid;
    route_uuid uuid;
BEGIN
    current_tenant := COALESCE(NEW.tenant_id, OLD.tenant_id);
    route_uuid := COALESCE(NEW.route_version_id, OLD.route_version_id);
    SELECT status INTO route_status
      FROM process_route_versions
     WHERE tenant_id = current_tenant AND id = route_uuid
     FOR UPDATE;
    IF route_status IS DISTINCT FROM 'DRAFT' THEN
        RAISE EXCEPTION 'Route steps can only be changed while the route version is DRAFT.'
            USING ERRCODE = '55000';
    END IF;
    RETURN COALESCE(NEW, OLD);
END;
$$;
CREATE TRIGGER process_route_steps_draft_only
BEFORE INSERT OR UPDATE OR DELETE ON process_route_steps
FOR EACH ROW EXECUTE FUNCTION guard_route_step_draft_only();

DO $$
BEGIN
    ALTER TABLE receipt_line_material_lots ENABLE ROW LEVEL SECURITY;
    ALTER TABLE receipt_line_material_lots FORCE ROW LEVEL SECURITY;
    CREATE POLICY tenant_isolation ON receipt_line_material_lots
        USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
        WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid);
END $$;
REVOKE ALL ON TABLE receipt_line_material_lots FROM PUBLIC, app_runtime;
GRANT SELECT, INSERT ON receipt_line_material_lots TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS receipt_line_material_lots CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS guard_finance_journal_line_mutation() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS guard_inventory_transaction_immutable() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS guard_route_step_draft_only() CASCADE');
    }
};
