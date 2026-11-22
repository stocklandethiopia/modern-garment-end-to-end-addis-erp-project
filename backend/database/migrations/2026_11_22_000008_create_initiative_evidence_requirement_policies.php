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
            throw new RuntimeException('Evidence requirement policies require PostgreSQL Row-Level Security.');
        }

        Schema::create('initiative_evidence_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('code', 80);
            $table->string('name', 180);
            $table->string('initiative_type', 40);
            $table->string('stage', 32);
            $table->string('target_market', 160)->nullable();
            $table->unsignedInteger('version_no');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->text('change_reason');
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'evidence_policies_tenant_id_uq');
            $table->unique(['tenant_id', 'code', 'version_no'], 'evidence_policies_code_ver_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(
                ['tenant_id', 'initiative_type', 'stage', 'status', 'effective_from'],
                'evidence_policies_scope_status_idx',
            );
        });

        Schema::create('initiative_evidence_policy_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('policy_id');
            $table->string('requirement_code', 80);
            $table->string('insight_type', 40);
            $table->unsignedSmallInteger('minimum_records')->default(1);
            $table->unsignedSmallInteger('minimum_distinct_sources')->default(1);
            $table->decimal('minimum_confidence', 5, 4)->nullable();
            $table->decimal('minimum_source_reliability', 5, 4)->nullable();
            $table->unsignedSmallInteger('max_age_days')->nullable();
            $table->boolean('require_geography')->default(false);
            $table->boolean('require_target_segment')->default(false);
            $table->boolean('require_sample_denominator')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id'], 'evidence_policy_rules_tenant_id_uq');
            $table->unique(
                ['tenant_id', 'policy_id', 'requirement_code'],
                'evidence_policy_rules_policy_code_uq',
            );
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'policy_id'], 'evidence_policy_rules_policy_fk')
                ->references(['tenant_id', 'id'])->on('initiative_evidence_policies')->restrictOnDelete();
            $table->index(['tenant_id', 'policy_id', 'insight_type'], 'evidence_policy_rules_type_idx');
        });

        DB::statement("ALTER TABLE initiative_evidence_policies
            ADD CONSTRAINT evidence_policies_dates_chk
                CHECK (effective_to IS NULL OR effective_to >= effective_from),
            ADD CONSTRAINT evidence_policies_stage_chk
                CHECK (stage IN ('RESEARCH', 'ASSESSMENT')),
            ADD CONSTRAINT evidence_policies_status_chk
                CHECK (status IN ('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED')),
            ADD CONSTRAINT evidence_policies_market_chk
                CHECK (target_market IS NULL OR LENGTH(TRIM(target_market)) > 0),
            ADD CONSTRAINT evidence_policies_approval_chk
                CHECK ((status = 'APPROVED') = (approved_by IS NOT NULL AND approved_at IS NOT NULL))");

        DB::statement("ALTER TABLE initiative_evidence_policy_rules
            ADD CONSTRAINT evidence_policy_rules_counts_chk
                CHECK (minimum_records > 0 AND minimum_distinct_sources > 0
                    AND minimum_distinct_sources <= minimum_records),
            ADD CONSTRAINT evidence_policy_rules_confidence_chk
                CHECK (minimum_confidence IS NULL OR minimum_confidence BETWEEN 0 AND 1),
            ADD CONSTRAINT evidence_policy_rules_reliability_chk
                CHECK (minimum_source_reliability IS NULL OR minimum_source_reliability BETWEEN 0 AND 1),
            ADD CONSTRAINT evidence_policy_rules_age_chk
                CHECK (max_age_days IS NULL OR max_age_days > 0)");

        DB::statement("CREATE OR REPLACE FUNCTION guard_initiative_evidence_policy_state()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.status = 'DRAFT' AND NEW.status IN ('DRAFT', 'PENDING_APPROVAL') THEN
                    RETURN NEW;
                END IF;

                IF OLD.status = 'PENDING_APPROVAL'
                    AND NEW.status IN ('APPROVED', 'REJECTED')
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.code IS NOT DISTINCT FROM OLD.code
                    AND NEW.name IS NOT DISTINCT FROM OLD.name
                    AND NEW.initiative_type IS NOT DISTINCT FROM OLD.initiative_type
                    AND NEW.stage IS NOT DISTINCT FROM OLD.stage
                    AND NEW.target_market IS NOT DISTINCT FROM OLD.target_market
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.effective_from IS NOT DISTINCT FROM OLD.effective_from
                    AND NEW.effective_to IS NOT DISTINCT FROM OLD.effective_to
                    AND NEW.change_reason IS NOT DISTINCT FROM OLD.change_reason
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'evidence policy content is immutable after submission; create a new version';
            END;
            $$");

        DB::statement("CREATE TRIGGER initiative_evidence_policy_state_guard
            BEFORE UPDATE ON initiative_evidence_policies
            FOR EACH ROW EXECUTE FUNCTION guard_initiative_evidence_policy_state()");

        DB::statement("CREATE OR REPLACE FUNCTION guard_initiative_evidence_policy_rules()
            RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE
                policy_status text;
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN
                    SELECT status INTO policy_status
                    FROM initiative_evidence_policies
                    WHERE tenant_id = OLD.tenant_id AND id = OLD.policy_id;

                    IF policy_status IS DISTINCT FROM 'DRAFT' THEN
                        RAISE EXCEPTION 'evidence policy rules can only change while the policy is a draft';
                    END IF;
                END IF;

                IF TG_OP IN ('INSERT', 'UPDATE') THEN
                    SELECT status INTO policy_status
                    FROM initiative_evidence_policies
                    WHERE tenant_id = NEW.tenant_id AND id = NEW.policy_id;

                    IF policy_status IS DISTINCT FROM 'DRAFT' THEN
                        RAISE EXCEPTION 'evidence policy rules can only change while the policy is a draft';
                    END IF;
                END IF;

                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;

                RETURN NEW;
            END;
            $$");

        DB::statement("CREATE TRIGGER initiative_evidence_policy_rules_guard
            BEFORE INSERT OR UPDATE OR DELETE ON initiative_evidence_policy_rules
            FOR EACH ROW EXECUTE FUNCTION guard_initiative_evidence_policy_rules()");

        foreach (['initiative_evidence_policies', 'initiative_evidence_policy_rules'] as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::statement('GRANT SELECT, INSERT, UPDATE ON initiative_evidence_policies TO app_runtime');
        DB::statement('REVOKE DELETE ON initiative_evidence_policies FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON initiative_evidence_policy_rules TO app_runtime');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS initiative_evidence_policy_rules_guard ON initiative_evidence_policy_rules');
        DB::statement('DROP TRIGGER IF EXISTS initiative_evidence_policy_state_guard ON initiative_evidence_policies');
        DB::statement('DROP FUNCTION IF EXISTS guard_initiative_evidence_policy_rules()');
        DB::statement('DROP FUNCTION IF EXISTS guard_initiative_evidence_policy_state()');
        Schema::dropIfExists('initiative_evidence_policy_rules');
        Schema::dropIfExists('initiative_evidence_policies');
    }
};
