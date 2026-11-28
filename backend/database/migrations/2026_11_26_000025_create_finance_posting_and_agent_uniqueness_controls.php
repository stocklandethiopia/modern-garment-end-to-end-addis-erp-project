<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE finance_journal_postings (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    journal_id uuid NOT NULL,
    posting_number varchar(100) NOT NULL,
    posted_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    posted_at timestamptz NOT NULL,
    posting_reference varchar(180),
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, journal_id),
    UNIQUE (tenant_id, posting_number),
    FOREIGN KEY (tenant_id, journal_id) REFERENCES finance_journals(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX finance_journal_postings_date_idx ON finance_journal_postings (tenant_id, posted_at);

CREATE TABLE finance_journal_reversals (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    original_posting_id uuid NOT NULL,
    reversal_journal_id uuid NOT NULL,
    reversal_reason text NOT NULL,
    requested_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, original_posting_id),
    FOREIGN KEY (tenant_id, original_posting_id) REFERENCES finance_journal_postings(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, reversal_journal_id) REFERENCES finance_journals(tenant_id, id) ON DELETE RESTRICT,
    CHECK (requested_by <> approved_by)
);
CREATE INDEX finance_journal_reversals_date_idx ON finance_journal_reversals (tenant_id, approved_at);

CREATE UNIQUE INDEX agent_approval_decisions_one_per_proposal
    ON agent_approval_decisions (tenant_id, proposal_id);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY['finance_journal_postings','finance_journal_reversals'] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT ON finance_journal_postings, finance_journal_reversals TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS agent_approval_decisions_one_per_proposal');
        DB::statement('DROP TABLE IF EXISTS finance_journal_reversals CASCADE');
        DB::statement('DROP TABLE IF EXISTS finance_journal_postings CASCADE');
    }
};
