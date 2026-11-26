<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'device_scan_events', 'agent_action_events', 'agent_approval_decisions',
        'agent_action_proposals', 'agent_run_audits', 'integration_outbox_events',
        'integration_endpoint_registry', 'document_extraction_runs', 'document_assets',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Document, integration and agent controls require PostgreSQL Row-Level Security.');
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE document_assets (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    document_code varchar(100) NOT NULL,
    document_type varchar(60) NOT NULL,
    original_filename varchar(255) NOT NULL,
    media_type varchar(160) NOT NULL,
    byte_size bigint NOT NULL CHECK (byte_size > 0),
    sha256 char(64) NOT NULL CHECK (sha256 ~ '^[0-9a-f]{64}$'),
    object_storage_reference varchar(1000) NOT NULL,
    source_channel varchar(24) NOT NULL CHECK (source_channel IN ('UPLOAD','CAMERA','EMAIL','INTEGRATION','SCANNER','GENERATED')),
    source_record_type varchar(80),
    source_record_id uuid,
    classification_status varchar(20) NOT NULL DEFAULT 'PENDING' CHECK (classification_status IN ('PENDING','CLASSIFIED','RESTRICTED','REJECTED')),
    access_classification varchar(24) NOT NULL DEFAULT 'BUSINESS' CHECK (access_classification IN ('PUBLIC','BUSINESS','CONFIDENTIAL','RESTRICTED')),
    uploaded_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    uploaded_at timestamptz NOT NULL,
    retention_until date,
    checksum_verified_at timestamptz,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, document_code),
    UNIQUE (tenant_id, sha256, byte_size),
    CHECK (retention_until IS NULL OR retention_until >= uploaded_at::date)
);
CREATE INDEX document_assets_source_idx ON document_assets (tenant_id, source_record_type, source_record_id);
CREATE INDEX document_assets_type_idx ON document_assets (tenant_id, document_type, uploaded_at DESC);

CREATE TABLE document_extraction_runs (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    document_asset_id uuid NOT NULL,
    extraction_profile varchar(100) NOT NULL,
    extractor_provider varchar(80) NOT NULL,
    extractor_model varchar(120),
    extractor_version varchar(80),
    status varchar(24) NOT NULL DEFAULT 'QUEUED' CHECK (status IN ('QUEUED','PROCESSING','EXTRACTED','VALIDATION_FAILED','REVIEW_REQUIRED','APPROVED_FOR_IMPORT','REJECTED','FAILED')),
    extracted_values jsonb NOT NULL DEFAULT '{}'::jsonb,
    field_confidence jsonb NOT NULL DEFAULT '{}'::jsonb,
    source_locations jsonb NOT NULL DEFAULT '{}'::jsonb,
    validation_issues jsonb NOT NULL DEFAULT '[]'::jsonb,
    proposed_domain varchar(80),
    proposed_record_type varchar(100),
    approved_values jsonb,
    reviewed_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at timestamptz,
    review_reason text,
    extraction_started_at timestamptz,
    extraction_completed_at timestamptz,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, document_asset_id) REFERENCES document_assets(tenant_id, id) ON DELETE RESTRICT,
    CHECK ((status <> 'APPROVED_FOR_IMPORT') OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL AND approved_values IS NOT NULL))
);
CREATE INDEX document_extraction_status_idx ON document_extraction_runs (tenant_id, status, created_at);

CREATE TABLE integration_endpoint_registry (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    endpoint_code varchar(100) NOT NULL,
    connector_type varchar(40) NOT NULL CHECK (connector_type IN ('ERP_API','E_COMMERCE','MARKETPLACE','POS','BANK','PAYMENT_GATEWAY','LOGISTICS','SUPPLIER','DEVICE','MESSAGING','MCP_TOOL')),
    direction varchar(16) NOT NULL CHECK (direction IN ('INBOUND','OUTBOUND','BIDIRECTIONAL')),
    base_url_reference varchar(500),
    credential_secret_reference varchar(500),
    granted_scopes jsonb NOT NULL DEFAULT '[]'::jsonb,
    allowed_event_types jsonb NOT NULL DEFAULT '[]'::jsonb,
    status varchar(20) NOT NULL DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','UNDER_REVIEW','ACTIVE','SUSPENDED','RETIRED')),
    data_classification varchar(24) NOT NULL DEFAULT 'BUSINESS' CHECK (data_classification IN ('PUBLIC','BUSINESS','CONFIDENTIAL','RESTRICTED')),
    approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    approved_at timestamptz,
    created_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, endpoint_code),
    CHECK (status <> 'ACTIVE' OR (approved_by IS NOT NULL AND approved_at IS NOT NULL))
);
CREATE INDEX integration_endpoint_status_idx ON integration_endpoint_registry (tenant_id, connector_type, status);

CREATE TABLE integration_outbox_events (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    endpoint_id uuid,
    event_key varchar(180) NOT NULL,
    event_type varchar(120) NOT NULL,
    aggregate_type varchar(100) NOT NULL,
    aggregate_id uuid NOT NULL,
    payload jsonb NOT NULL,
    payload_sha256 char(64) NOT NULL CHECK (payload_sha256 ~ '^[0-9a-f]{64}$'),
    status varchar(20) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING','PROCESSING','DELIVERED','RETRY','DEAD_LETTER','CANCELLED')),
    attempt_count integer NOT NULL DEFAULT 0 CHECK (attempt_count >= 0),
    available_at timestamptz NOT NULL,
    locked_at timestamptz,
    delivered_at timestamptz,
    last_error_code varchar(100),
    last_error_summary varchar(500),
    created_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, event_key),
    FOREIGN KEY (tenant_id, endpoint_id) REFERENCES integration_endpoint_registry(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX integration_outbox_dispatch_idx ON integration_outbox_events (status, available_at, created_at);
CREATE INDEX integration_outbox_aggregate_idx ON integration_outbox_events (tenant_id, aggregate_type, aggregate_id, created_at);

CREATE TABLE agent_run_audits (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    run_key varchar(180) NOT NULL,
    agent_code varchar(100) NOT NULL,
    workflow_code varchar(120) NOT NULL,
    provider_code varchar(60) NOT NULL,
    model_code varchar(120),
    model_version varchar(100),
    execution_mode varchar(24) NOT NULL CHECK (execution_mode IN ('READ_ONLY','DRAFT','TOOL_ASSISTED','APPROVAL_GATED')),
    risk_tier varchar(8) NOT NULL CHECK (risk_tier IN ('A0','A1','A2','A3','A4')),
    input_sha256 char(64) NOT NULL CHECK (input_sha256 ~ '^[0-9a-f]{64}$'),
    output_sha256 char(64),
    context_references jsonb NOT NULL DEFAULT '[]'::jsonb,
    tool_trace jsonb NOT NULL DEFAULT '[]'::jsonb,
    policy_snapshot jsonb NOT NULL DEFAULT '{}'::jsonb,
    token_usage jsonb NOT NULL DEFAULT '{}'::jsonb,
    status varchar(20) NOT NULL DEFAULT 'STARTED' CHECK (status IN ('STARTED','SUCCEEDED','FAILED','CANCELLED','DENIED')),
    started_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    started_at timestamptz NOT NULL,
    completed_at timestamptz,
    error_class varchar(120),
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, run_key)
);
CREATE INDEX agent_run_audits_agent_idx ON agent_run_audits (tenant_id, agent_code, started_at DESC);
CREATE INDEX agent_run_audits_status_idx ON agent_run_audits (tenant_id, status, started_at DESC);

CREATE TABLE agent_action_proposals (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    agent_run_id uuid NOT NULL,
    proposal_key varchar(180) NOT NULL,
    capability_code varchar(120) NOT NULL,
    target_record_type varchar(100) NOT NULL,
    target_record_id uuid,
    action_type varchar(80) NOT NULL,
    risk_tier varchar(8) NOT NULL CHECK (risk_tier IN ('A0','A1','A2','A3','A4')),
    proposed_payload jsonb NOT NULL,
    expected_preconditions jsonb NOT NULL DEFAULT '{}'::jsonb,
    reason text NOT NULL,
    status varchar(24) NOT NULL DEFAULT 'PROPOSED' CHECK (status IN ('PROPOSED','AWAITING_APPROVAL','APPROVED','REJECTED','EXPIRED','EXECUTED','FAILED','CANCELLED')),
    expires_at timestamptz,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, proposal_key),
    FOREIGN KEY (tenant_id, agent_run_id) REFERENCES agent_run_audits(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX agent_action_proposals_status_idx ON agent_action_proposals (tenant_id, status, risk_tier, created_at);

CREATE TABLE agent_approval_decisions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    proposal_id uuid NOT NULL,
    decision varchar(16) NOT NULL CHECK (decision IN ('APPROVE','REJECT','REQUEST_CHANGES','EXPIRE')),
    decision_reason text NOT NULL,
    approved_payload jsonb,
    actor_user_id uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    decided_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, proposal_id) REFERENCES agent_action_proposals(tenant_id, id) ON DELETE RESTRICT
);
CREATE INDEX agent_approval_decisions_proposal_idx ON agent_approval_decisions (tenant_id, proposal_id, decided_at);

CREATE TABLE agent_action_events (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    proposal_id uuid NOT NULL,
    event_key varchar(180) NOT NULL,
    event_type varchar(24) NOT NULL CHECK (event_type IN ('APPROVED','REJECTED','EXECUTION_STARTED','EXECUTED','FAILED','ROLLED_BACK','EXPIRED')),
    domain_service varchar(120),
    result_reference_type varchar(100),
    result_reference_id uuid,
    result_sha256 char(64),
    event_payload jsonb NOT NULL DEFAULT '{}'::jsonb,
    actor_user_id uuid REFERENCES users(id) ON DELETE RESTRICT,
    occurred_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, event_key),
    FOREIGN KEY (tenant_id, proposal_id) REFERENCES agent_action_proposals(tenant_id, id) ON DELETE RESTRICT,
    CHECK (result_sha256 IS NULL OR result_sha256 ~ '^[0-9a-f]{64}$')
);
CREATE INDEX agent_action_events_proposal_idx ON agent_action_events (tenant_id, proposal_id, occurred_at);

CREATE TABLE device_scan_events (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    event_key varchar(180) NOT NULL,
    device_reference varchar(160) NOT NULL,
    device_type varchar(24) NOT NULL CHECK (device_type IN ('CAMERA','BARCODE_SCANNER','QR_SCANNER','RFID_READER','MACHINE_TERMINAL','MOBILE_APP','OTHER')),
    symbology varchar(32),
    scanned_value varchar(500) NOT NULL,
    event_context_type varchar(100),
    event_context_id uuid,
    device_timestamp timestamptz,
    received_at timestamptz NOT NULL,
    actor_user_id uuid REFERENCES users(id) ON DELETE RESTRICT,
    resolution_status varchar(24) NOT NULL DEFAULT 'PENDING' CHECK (resolution_status IN ('PENDING','RESOLVED','AMBIGUOUS','REJECTED')),
    resolution_reference_type varchar(100),
    resolution_reference_id uuid,
    raw_metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    UNIQUE (tenant_id, id),
    UNIQUE (tenant_id, event_key)
);
CREATE INDEX device_scan_events_value_idx ON device_scan_events (tenant_id, scanned_value, received_at DESC);
CREATE INDEX device_scan_events_context_idx ON device_scan_events (tenant_id, event_context_type, event_context_id);

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'document_assets','document_extraction_runs','integration_endpoint_registry',
        'integration_outbox_events','agent_run_audits','agent_action_proposals',
        'agent_approval_decisions','agent_action_events','device_scan_events'
    ] LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('ALTER TABLE %I FORCE ROW LEVEL SECURITY', t);
        EXECUTE format('CREATE POLICY tenant_isolation ON %I USING (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid) WITH CHECK (tenant_id = NULLIF(current_setting(''app.current_tenant_id'', true), '''')::uuid)', t);
        EXECUTE format('REVOKE ALL ON TABLE %I FROM PUBLIC, app_runtime', t);
    END LOOP;
END $$;

GRANT SELECT, INSERT, UPDATE ON document_assets, document_extraction_runs,
    integration_endpoint_registry, integration_outbox_events TO app_runtime;
GRANT SELECT, INSERT ON agent_run_audits, agent_action_proposals,
    agent_approval_decisions, agent_action_events, device_scan_events TO app_runtime;
SQL);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
        }
    }
};
