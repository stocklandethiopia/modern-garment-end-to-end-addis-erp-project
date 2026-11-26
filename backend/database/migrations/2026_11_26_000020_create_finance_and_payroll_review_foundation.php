<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'payroll_run_lines','payroll_run_drafts','statutory_rule_versions',
        'finance_journal_lines','finance_journals','finance_accounts','finance_periods',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Finance and payroll controls require PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE finance_periods (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    period_code varchar(40) NOT NULL,
    starts_on date NOT NULL,
    ends_on date NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','OPEN','CLOSING','CLOSED','LOCKED')),
    opened_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    opened_at timestamptz,
    closed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    closed_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, legal_entity_id, period_code),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    CHECK (ends_on >= starts_on),
    CHECK ((status <> 'OPEN') OR (opened_by IS NOT NULL AND opened_at IS NOT NULL)),
    CHECK ((status <> 'CLOSED') OR (closed_by IS NOT NULL AND closed_at IS NOT NULL))
);
CREATE INDEX finance_periods_date_idx ON finance_periods (tenant_id, legal_entity_id, starts_on, ends_on, status);

CREATE TABLE finance_accounts (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    account_code varchar(80) NOT NULL,
    account_name varchar(180) NOT NULL,
    account_class varchar(24) NOT NULL CHECK (account_class IN ('ASSET','LIABILITY','EQUITY','INCOME','EXPENSE','CONTROL')),
    normal_balance varchar(8) NOT NULL CHECK (normal_balance IN ('DEBIT','CREDIT')),
    parent_account_id uuid,
    currency_policy varchar(20) NOT NULL DEFAULT 'MULTI' CHECK (currency_policy IN ('BASE_ONLY','MULTI')),
    control_account boolean NOT NULL DEFAULT false,
    reconciliation_required boolean NOT NULL DEFAULT false,
    status varchar(16) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','ACTIVE','BLOCKED','ARCHIVED')),
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, legal_entity_id, account_code),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, parent_account_id) REFERENCES finance_accounts(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status <> 'ACTIVE') OR (approved_by IS NOT NULL AND approved_at IS NOT NULL))
);
CREATE INDEX finance_accounts_class_idx ON finance_accounts (tenant_id, legal_entity_id, account_class, status);

CREATE TABLE finance_journals (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    period_id uuid NOT NULL,
    journal_number varchar(100) NOT NULL,
    journal_type varchar(32) NOT NULL CHECK (journal_type IN ('GENERAL','AP','AR','INVENTORY','COSTING','PAYROLL_DRAFT','TAX_DRAFT','ADJUSTMENT','REVERSAL')),
    entry_date date NOT NULL,
    currency char(3) NOT NULL,
    description text NOT NULL,
    source_document_type varchar(80),
    source_document_id uuid,
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','UNDER_REVIEW','APPROVED','REJECTED','ON_HOLD','VOID')),
    balance_verified_at timestamptz,
    submitted_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    submitted_at timestamptz,
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    approval_reason text,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, legal_entity_id, journal_number),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, period_id) REFERENCES finance_periods(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status <> 'APPROVED') OR (approved_by IS NOT NULL AND approved_at IS NOT NULL AND balance_verified_at IS NOT NULL))
);
CREATE INDEX finance_journal_review_idx ON finance_journals (tenant_id, legal_entity_id, entry_date, status);

CREATE TABLE finance_journal_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    journal_id uuid NOT NULL,
    line_no integer NOT NULL CHECK (line_no > 0),
    account_id uuid NOT NULL,
    description varchar(500),
    currency char(3) NOT NULL,
    debit_amount numeric(20,4) NOT NULL DEFAULT 0 CHECK (debit_amount >= 0),
    credit_amount numeric(20,4) NOT NULL DEFAULT 0 CHECK (credit_amount >= 0),
    base_debit_amount numeric(20,4) NOT NULL DEFAULT 0 CHECK (base_debit_amount >= 0),
    base_credit_amount numeric(20,4) NOT NULL DEFAULT 0 CHECK (base_credit_amount >= 0),
    exchange_rate_reference varchar(120),
    cost_centre_code varchar(80),
    source_reference jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, journal_id, line_no),
    FOREIGN KEY (tenant_id, journal_id) REFERENCES finance_journals(tenant_id, id) ON DELETE RESTRICT,
    FOREIGN KEY (tenant_id, account_id) REFERENCES finance_accounts(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((debit_amount > 0 AND credit_amount = 0) OR (credit_amount > 0 AND debit_amount = 0)),
    CHECK ((base_debit_amount > 0 AND base_credit_amount = 0) OR (base_credit_amount > 0 AND base_debit_amount = 0))
);
CREATE INDEX finance_journal_lines_account_idx ON finance_journal_lines (tenant_id, account_id, journal_id);

CREATE OR REPLACE FUNCTION enforce_balanced_finance_journal() RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    debit_total numeric(20,4);
    credit_total numeric(20,4);
    line_count integer;
BEGIN
    IF NEW.status IN ('UNDER_REVIEW','APPROVED') AND
       (TG_OP = 'INSERT' OR OLD.status IS DISTINCT FROM NEW.status OR NEW.balance_verified_at IS NULL) THEN
        SELECT COUNT(*), COALESCE(SUM(base_debit_amount),0), COALESCE(SUM(base_credit_amount),0)
          INTO line_count, debit_total, credit_total
          FROM finance_journal_lines
         WHERE tenant_id = NEW.tenant_id AND journal_id = NEW.id;
        IF line_count < 2 OR debit_total <> credit_total OR debit_total <= 0 THEN
            RAISE EXCEPTION 'Journal must contain at least two lines and balanced positive base-currency debit/credit totals before review or approval.'
                USING ERRCODE = '23514';
        END IF;
        NEW.balance_verified_at := COALESCE(NEW.balance_verified_at, now());
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER finance_journal_balance_gate
BEFORE INSERT OR UPDATE OF status, balance_verified_at ON finance_journals
FOR EACH ROW EXECUTE FUNCTION enforce_balanced_finance_journal();

CREATE TABLE statutory_rule_versions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    jurisdiction_code varchar(24) NOT NULL,
    rule_domain varchar(32) NOT NULL CHECK (rule_domain IN ('INCOME_TAX','VAT','WITHHOLDING','PENSION','CUSTOMS','FOREIGN_EXCHANGE','LABOUR','OTHER')),
    rule_code varchar(100) NOT NULL,
    source_authority varchar(180) NOT NULL,
    source_title varchar(300) NOT NULL,
    source_reference varchar(1000) NOT NULL,
    source_sha256 char(64),
    publication_date date,
    effective_from date,
    effective_to date,
    rule_payload jsonb NOT NULL DEFAULT '{}'::jsonb,
    verification_status varchar(24) NOT NULL DEFAULT 'UNVERIFIED' CHECK (verification_status IN ('UNVERIFIED','SOURCE_REVIEWED','COUNSEL_REVIEW','PROFESSIONAL_APPROVED','SUPERSEDED','REJECTED')),
    verified_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    verified_at timestamptz,
    professional_approval_reference varchar(180),
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, jurisdiction_code, rule_domain, rule_code, effective_from),
    CHECK (effective_to IS NULL OR effective_from IS NULL OR effective_to >= effective_from),
    CHECK (source_sha256 IS NULL OR source_sha256 ~ '^[0-9a-f]{64}$'),
    CHECK ((verification_status <> 'PROFESSIONAL_APPROVED') OR (verified_by IS NOT NULL AND verified_at IS NOT NULL AND professional_approval_reference IS NOT NULL))
);
CREATE INDEX statutory_rules_effective_idx ON statutory_rule_versions (tenant_id, jurisdiction_code, rule_domain, effective_from, effective_to, verification_status);

CREATE TABLE payroll_run_drafts (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    legal_entity_id uuid NOT NULL,
    payroll_run_code varchar(100) NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    currency char(3) NOT NULL,
    calculation_snapshot jsonb NOT NULL DEFAULT '{}'::jsonb,
    statutory_rule_set jsonb NOT NULL DEFAULT '[]'::jsonb,
    legal_review_status varchar(24) NOT NULL DEFAULT 'REQUIRED' CHECK (legal_review_status IN ('REQUIRED','IN_REVIEW','APPROVED','HOLD')),
    status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','CALCULATED','UNDER_REVIEW','LEGAL_HOLD','APPROVED_FOR_PAYROLL','REJECTED','VOID')),
    control_totals jsonb NOT NULL DEFAULT '{}'::jsonb,
    hold_reasons jsonb NOT NULL DEFAULT '[]'::jsonb,
    prepared_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at timestamptz,
    legal_approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    legal_approved_at timestamptz,
    legal_approval_reference varchar(180),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, legal_entity_id, payroll_run_code),
    FOREIGN KEY (tenant_id, legal_entity_id) REFERENCES legal_entities(tenant_id, id) ON DELETE RESTRICT,
    CHECK (period_end >= period_start),
    CHECK ((status <> 'APPROVED_FOR_PAYROLL') OR (legal_review_status = 'APPROVED' AND legal_approved_by IS NOT NULL AND legal_approved_at IS NOT NULL AND legal_approval_reference IS NOT NULL))
);
CREATE INDEX payroll_run_draft_status_idx ON payroll_run_drafts (tenant_id, legal_entity_id, period_start, status);

CREATE TABLE payroll_run_lines (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    payroll_run_draft_id uuid NOT NULL,
    worker_reference varchar(120) NOT NULL,
    earning_components jsonb NOT NULL DEFAULT '[]'::jsonb,
    deduction_components jsonb NOT NULL DEFAULT '[]'::jsonb,
    gross_amount numeric(18,4) NOT NULL CHECK (gross_amount >= 0),
    total_deductions numeric(18,4) NOT NULL CHECK (total_deductions >= 0),
    net_amount numeric(18,4) NOT NULL CHECK (net_amount >= 0),
    currency char(3) NOT NULL,
    calculation_evidence jsonb NOT NULL,
    calculation_status varchar(24) NOT NULL DEFAULT 'DRAFT' CHECK (calculation_status IN ('DRAFT','VALIDATED','HELD','REJECTED')),
    hold_reason text,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, payroll_run_draft_id, worker_reference),
    FOREIGN KEY (tenant_id, payroll_run_draft_id) REFERENCES payroll_run_drafts(tenant_id, id) ON DELETE RESTRICT,
    CHECK (gross_amount - total_deductions = net_amount)
);
CREATE INDEX payroll_run_lines_worker_idx ON payroll_run_lines (tenant_id, worker_reference, payroll_run_draft_id);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'finance_periods','finance_accounts','finance_journals','finance_journal_lines',
        'statutory_rule_versions','payroll_run_drafts','payroll_run_lines'
    ] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON finance_periods, finance_accounts, finance_journals, finance_journal_lines,
    statutory_rule_versions, payroll_run_drafts, payroll_run_lines TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS enforce_balanced_finance_journal() CASCADE');
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
