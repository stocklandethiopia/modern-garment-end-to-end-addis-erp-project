<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Account assortment terms require PostgreSQL row-level security.');
        }

        Schema::create('assortment_account_terms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('assortment_plan_id');
            $table->uuid('price_list_id');
            $table->unsignedInteger('version_no');
            $table->string('channel_code', 60);
            $table->string('market_scope', 160);
            $table->string('account_reference', 120);
            $table->string('account_tier', 80)->nullable();
            $table->string('commercial_terms_reference', 160);
            $table->string('payment_terms_reference', 120);
            $table->string('delivery_terms_reference', 120);
            $table->string('returns_policy_reference', 120)->nullable();
            $table->char('currency', 3);
            $table->unsignedInteger('minimum_order_units')->default(0);
            $table->unsignedInteger('order_multiple_units')->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('change_reason');
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('created_by');
            $table->uuid('submitted_by')->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->uuid('decided_by')->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'assortment_account_terms_tenant_id_uq');
            $table->unique(
                ['tenant_id', 'assortment_plan_id', 'version_no'],
                'assortment_account_terms_version_uq',
            );
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'assortment_plan_id'], 'assortment_account_terms_plan_fk')
                ->references(['tenant_id', 'id'])->on('assortment_plans')->restrictOnDelete();
            $table->foreign(['tenant_id', 'price_list_id'], 'assortment_account_terms_price_list_fk')
                ->references(['tenant_id', 'id'])->on('price_lists')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(
                ['tenant_id', 'assortment_plan_id', 'status', 'effective_from'],
                'assortment_account_terms_scope_idx',
            );
        });

        DB::statement("ALTER TABLE assortment_account_terms
            ADD CONSTRAINT assortment_account_terms_channel_chk
                CHECK (channel_code IN ('WHOLESALE', 'RESELLER', 'CUSTOMER_PROGRAMME', 'EXPORT')),
            ADD CONSTRAINT assortment_account_terms_status_chk
                CHECK (status IN ('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED')),
            ADD CONSTRAINT assortment_account_terms_currency_chk
                CHECK (currency ~ '^[A-Z]{3}$'),
            ADD CONSTRAINT assortment_account_terms_minimum_chk
                CHECK (order_multiple_units > 0),
            ADD CONSTRAINT assortment_account_terms_dates_chk
                CHECK (effective_to IS NULL OR effective_to >= effective_from),
            ADD CONSTRAINT assortment_account_terms_submission_chk
                CHECK (
                    (status = 'DRAFT' AND submitted_by IS NULL AND submitted_at IS NULL
                        AND decided_by IS NULL AND decided_at IS NULL AND decision_reason IS NULL)
                    OR
                    (status = 'PENDING_APPROVAL' AND submitted_by IS NOT NULL AND submitted_at IS NOT NULL
                        AND decided_by IS NULL AND decided_at IS NULL AND decision_reason IS NULL)
                    OR
                    (status IN ('APPROVED', 'REJECTED') AND submitted_by IS NOT NULL AND submitted_at IS NOT NULL
                        AND decided_by IS NOT NULL AND decided_at IS NOT NULL AND decision_reason IS NOT NULL)
                )");

        DB::statement("CREATE OR REPLACE FUNCTION guard_assortment_account_terms_state()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.status = 'DRAFT' AND NEW.status = 'PENDING_APPROVAL'
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.assortment_plan_id IS NOT DISTINCT FROM OLD.assortment_plan_id
                    AND NEW.price_list_id IS NOT DISTINCT FROM OLD.price_list_id
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.channel_code IS NOT DISTINCT FROM OLD.channel_code
                    AND NEW.market_scope IS NOT DISTINCT FROM OLD.market_scope
                    AND NEW.account_reference IS NOT DISTINCT FROM OLD.account_reference
                    AND NEW.account_tier IS NOT DISTINCT FROM OLD.account_tier
                    AND NEW.commercial_terms_reference IS NOT DISTINCT FROM OLD.commercial_terms_reference
                    AND NEW.payment_terms_reference IS NOT DISTINCT FROM OLD.payment_terms_reference
                    AND NEW.delivery_terms_reference IS NOT DISTINCT FROM OLD.delivery_terms_reference
                    AND NEW.returns_policy_reference IS NOT DISTINCT FROM OLD.returns_policy_reference
                    AND NEW.currency IS NOT DISTINCT FROM OLD.currency
                    AND NEW.minimum_order_units IS NOT DISTINCT FROM OLD.minimum_order_units
                    AND NEW.order_multiple_units IS NOT DISTINCT FROM OLD.order_multiple_units
                    AND NEW.effective_from IS NOT DISTINCT FROM OLD.effective_from
                    AND NEW.effective_to IS NOT DISTINCT FROM OLD.effective_to
                    AND NEW.change_reason IS NOT DISTINCT FROM OLD.change_reason
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at THEN
                    RETURN NEW;
                END IF;

                IF OLD.status = 'PENDING_APPROVAL'
                    AND NEW.status IN ('APPROVED', 'REJECTED')
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.assortment_plan_id IS NOT DISTINCT FROM OLD.assortment_plan_id
                    AND NEW.price_list_id IS NOT DISTINCT FROM OLD.price_list_id
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.channel_code IS NOT DISTINCT FROM OLD.channel_code
                    AND NEW.market_scope IS NOT DISTINCT FROM OLD.market_scope
                    AND NEW.account_reference IS NOT DISTINCT FROM OLD.account_reference
                    AND NEW.account_tier IS NOT DISTINCT FROM OLD.account_tier
                    AND NEW.commercial_terms_reference IS NOT DISTINCT FROM OLD.commercial_terms_reference
                    AND NEW.payment_terms_reference IS NOT DISTINCT FROM OLD.payment_terms_reference
                    AND NEW.delivery_terms_reference IS NOT DISTINCT FROM OLD.delivery_terms_reference
                    AND NEW.returns_policy_reference IS NOT DISTINCT FROM OLD.returns_policy_reference
                    AND NEW.currency IS NOT DISTINCT FROM OLD.currency
                    AND NEW.minimum_order_units IS NOT DISTINCT FROM OLD.minimum_order_units
                    AND NEW.order_multiple_units IS NOT DISTINCT FROM OLD.order_multiple_units
                    AND NEW.effective_from IS NOT DISTINCT FROM OLD.effective_from
                    AND NEW.effective_to IS NOT DISTINCT FROM OLD.effective_to
                    AND NEW.change_reason IS NOT DISTINCT FROM OLD.change_reason
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at
                    AND NEW.submitted_by IS NOT DISTINCT FROM OLD.submitted_by
                    AND NEW.submitted_at IS NOT DISTINCT FROM OLD.submitted_at THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'assortment account terms are immutable; create a new terms version';
            END;
            $$");

        DB.statement("CREATE TRIGGER assortment_account_terms_state_guard
            BEFORE UPDATE ON assortment_account_terms
            FOR EACH ROW EXECUTE FUNCTION guard_assortment_account_terms_state()");

        DB.statement("ALTER TABLE assortment_account_terms ENABLE ROW LEVEL SECURITY");
        DB.statement("ALTER TABLE assortment_account_terms FORCE ROW LEVEL SECURITY");
        DB.statement(
            "CREATE POLICY tenant_isolation_assortment_account_terms ON assortment_account_terms
             USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
             WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
        );
        DB.statement('GRANT SELECT, INSERT ON assortment_account_terms TO app_runtime');
        DB.statement('GRANT UPDATE (status, submitted_by, submitted_at, decided_by, decided_at, decision_reason, updated_at) ON assortment_account_terms TO app_runtime');
        DB.statement('REVOKE DELETE ON assortment_account_terms FROM app_runtime');

        DB.statement('GRANT EXECUTE ON FUNCTION prevent_merchandising_history_mutation() TO app_runtime');
        DB.statement('CREATE TRIGGER assortment_account_terms_append_only BEFORE DELETE ON assortment_account_terms FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
    }

    public function down(): void
    {
        DB.statement('DROP TRIGGER IF EXISTS assortment_account_terms_append_only ON assortment_account_terms');
        DB.statement('DROP TRIGGER IF EXISTS assortment_account_terms_state_guard ON assortment_account_terms');
        DB.statement('DROP FUNCTION IF EXISTS guard_assortment_account_terms_state()');
        Schema::dropIfExists('assortment_account_terms');
    }
};
