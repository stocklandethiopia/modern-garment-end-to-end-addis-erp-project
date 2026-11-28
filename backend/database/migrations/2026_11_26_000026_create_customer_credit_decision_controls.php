<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE customer_credit_decisions (
    id uuid PRIMARY KEY,
    tenant_id uuid NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    customer_order_id uuid NOT NULL,
    decision varchar(24) NOT NULL CHECK (decision IN ('CLEAR','APPROVED_EXCEPTION','HELD')),
    decision_reference varchar(180) NOT NULL,
    reason text NOT NULL,
    reviewed_by uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at timestamptz NOT NULL,
    exception_approved_by uuid REFERENCES users(id) ON DELETE RESTRICT,
    exception_approved_at timestamptz,
    exception_approval_reference varchar(180),
    created_at timestamptz NOT NULL,
    UNIQUE (tenant_id, id),
    FOREIGN KEY (tenant_id, customer_order_id) REFERENCES customer_orders(tenant_id, id) ON DELETE RESTRICT,
    CHECK (
        decision <> 'APPROVED_EXCEPTION'
        OR (
            exception_approved_by IS NOT NULL
            AND exception_approved_at IS NOT NULL
            AND exception_approval_reference IS NOT NULL
            AND reviewed_by <> exception_approved_by
        )
    )
);
CREATE INDEX customer_credit_decisions_order_idx ON customer_credit_decisions (tenant_id, customer_order_id, reviewed_at DESC);

ALTER TABLE customer_orders
    ADD COLUMN credit_control_decision_id uuid;
ALTER TABLE customer_orders
    ADD CONSTRAINT customer_orders_credit_decision_fk
    FOREIGN KEY (tenant_id, credit_control_decision_id)
    REFERENCES customer_credit_decisions(tenant_id, id) ON DELETE RESTRICT;

DO $$
BEGIN
    ALTER TABLE customer_credit_decisions ENABLE ROW LEVEL SECURITY;
    ALTER TABLE customer_credit_decisions FORCE ROW LEVEL SECURITY;
    CREATE POLICY tenant_isolation ON customer_credit_decisions
        USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
        WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid);
END $$;

REVOKE ALL ON TABLE customer_credit_decisions FROM PUBLIC, app_runtime;
GRANT SELECT, INSERT ON customer_credit_decisions TO app_runtime;
GRANT SELECT, INSERT, UPDATE ON customer_orders TO app_runtime;
SQL);
    }

    public function down(): void
    {
        DB.statement('ALTER TABLE customer_orders DROP CONSTRAINT IF EXISTS customer_orders_credit_decision_fk');
        DB.statement('ALTER TABLE customer_orders DROP COLUMN IF EXISTS credit_control_decision_id');
        DB.statement('DROP TABLE IF EXISTS customer_credit_decisions CASCADE');
    }
};
