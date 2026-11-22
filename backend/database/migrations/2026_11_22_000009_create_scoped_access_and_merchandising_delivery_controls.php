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
            throw new RuntimeException('Scoped access and merchandising delivery controls require PostgreSQL RLS.');
        }

        Schema::create('tenant_access_scope_modes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->string('scope_mode', 16)->default('TENANT_WIDE');
            $table->uuid('changed_by');
            $table->text('change_reason');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'user_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('tenant_access_scopes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->string('scope_type', 16);
            $table->uuid('scope_id')->nullable();
            $table->string('scope_code', 160)->nullable();
            $table->string('permission_code', 16);
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->string('status', 16)->default('ACTIVE');
            $table->text('grant_reason');
            $table->uuid('granted_by');
            $table->uuid('revoked_by')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('granted_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('revoked_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(
                ['tenant_id', 'user_id', 'scope_type', 'scope_id', 'scope_code', 'status'],
                'tenant_access_scopes_lookup_idx',
            );
        });

        DB::statement("ALTER TABLE tenant_access_scope_modes
            ADD CONSTRAINT tenant_access_scope_modes_value_chk
                CHECK (scope_mode IN ('TENANT_WIDE', 'SCOPED'))");

        DB::statement("ALTER TABLE tenant_access_scopes
            ADD CONSTRAINT tenant_access_scopes_type_chk
                CHECK (scope_type IN ('BRAND', 'SITE', 'MARKET')),
            ADD CONSTRAINT tenant_access_scopes_permission_chk
                CHECK (permission_code IN ('READ', 'CREATE', 'APPROVE', 'MANAGE')),
            ADD CONSTRAINT tenant_access_scopes_status_chk
                CHECK (status IN ('ACTIVE', 'REVOKED')),
            ADD CONSTRAINT tenant_access_scopes_identity_chk
                CHECK (
                    (scope_type IN ('BRAND', 'SITE') AND scope_id IS NOT NULL AND scope_code IS NULL)
                    OR (scope_type = 'MARKET' AND scope_id IS NULL AND scope_code IS NOT NULL AND LENGTH(TRIM(scope_code)) > 0)
                ),
            ADD CONSTRAINT tenant_access_scopes_dates_chk
                CHECK (valid_to IS NULL OR valid_to >= valid_from),
            ADD CONSTRAINT tenant_access_scopes_revocation_chk
                CHECK (
                    (status = 'ACTIVE' AND revoked_by IS NULL AND revoked_at IS NULL AND revocation_reason IS NULL)
                    OR (status = 'REVOKED' AND revoked_by IS NOT NULL AND revoked_at IS NOT NULL AND revocation_reason IS NOT NULL)
                )");

        DB::statement("CREATE OR REPLACE FUNCTION guard_tenant_access_scope_mutation()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.status = 'ACTIVE'
                    AND NEW.status = 'REVOKED'
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.user_id IS NOT DISTINCT FROM OLD.user_id
                    AND NEW.scope_type IS NOT DISTINCT FROM OLD.scope_type
                    AND NEW.scope_id IS NOT DISTINCT FROM OLD.scope_id
                    AND NEW.scope_code IS NOT DISTINCT FROM OLD.scope_code
                    AND NEW.permission_code IS NOT DISTINCT FROM OLD.permission_code
                    AND NEW.valid_from IS NOT DISTINCT FROM OLD.valid_from
                    AND NEW.valid_to IS NOT DISTINCT FROM OLD.valid_to
                    AND NEW.grant_reason IS NOT DISTINCT FROM OLD.grant_reason
                    AND NEW.granted_by IS NOT DISTINCT FROM OLD.granted_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'access grants are immutable; only an explicit revocation is allowed';
            END;
            $$");

        DB::statement('CREATE TRIGGER tenant_access_scope_mutation_guard BEFORE UPDATE OR DELETE ON tenant_access_scopes FOR EACH ROW EXECUTE FUNCTION guard_tenant_access_scope_mutation()');

        Schema::table('assortment_plans', function (Blueprint $table): void {
            $table->uuid('business_site_id')->nullable();
            $table->string('commercial_eligibility_status', 32)->default('NOT_APPLICABLE');
            $table->text('commercial_block_reason')->nullable();
            $table->foreign(['tenant_id', 'business_site_id'], 'assortment_plans_business_site_fk')
                ->references(['tenant_id', 'id'])->on('business_sites')->restrictOnDelete();
        });

        DB::statement("ALTER TABLE assortment_plans
            ADD CONSTRAINT assortment_plans_commercial_eligibility_chk
                CHECK (commercial_eligibility_status IN ('NOT_APPLICABLE', 'SIMULATION_ONLY', 'ELIGIBLE_PENDING_APPROVAL'))");

        Schema::table('market_insights', function (Blueprint $table): void {
            $table->string('source_system', 80)->nullable();
            $table->string('source_event_id', 160)->nullable();
        });

        DB::statement('ALTER TABLE market_insights
            ADD CONSTRAINT market_insights_source_event_pair_chk
                CHECK ((source_system IS NULL) = (source_event_id IS NULL))');

        DB::statement('CREATE UNIQUE INDEX market_insights_source_event_uq
            ON market_insights (tenant_id, source_system, source_event_id)
            WHERE source_event_id IS NOT NULL');

        Schema::create('market_insight_event_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('source_system', 80);
            $table->string('source_event_id', 160);
            $table->uuid('market_insight_id');
            $table->char('payload_sha256', 64);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->timestampTz('first_received_at');
            $table->timestampTz('last_received_at');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'source_system', 'source_event_id'], 'market_insight_event_receipts_event_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'market_insight_id'])
                ->references(['tenant_id', 'id'])->on('market_insights')->restrictOnDelete();
            $table->index(['tenant_id', 'market_insight_id']);
        });

        foreach (['tenant_access_scope_modes', 'tenant_access_scopes', 'market_insight_event_receipts'] as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::statement('GRANT SELECT, INSERT, UPDATE ON tenant_access_scope_modes TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON tenant_access_scopes TO app_runtime');
        DB::statement('GRANT UPDATE (status, revoked_by, revoked_at, revocation_reason, updated_at) ON tenant_access_scopes TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON market_insight_event_receipts TO app_runtime');
        DB::statement('GRANT UPDATE (duplicate_count, last_received_at, updated_at) ON market_insight_event_receipts TO app_runtime');
        DB::statement('GRANT UPDATE (business_site_id, commercial_eligibility_status, commercial_block_reason) ON assortment_plans TO app_runtime');
        DB::statement('GRANT INSERT ON market_insights TO app_runtime');
        DB::statement('GRANT SELECT ON market_insights TO app_runtime');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS tenant_access_scope_mutation_guard ON tenant_access_scopes');
        DB::statement('DROP FUNCTION IF EXISTS guard_tenant_access_scope_mutation()');
        Schema::dropIfExists('market_insight_event_receipts');
        DB::statement('DROP INDEX IF EXISTS market_insights_source_event_uq');
        DB::statement('ALTER TABLE market_insights DROP CONSTRAINT IF EXISTS market_insights_source_event_pair_chk');
        Schema::table('market_insights', function (Blueprint $table): void {
            $table->dropColumn(['source_system', 'source_event_id']);
        });
        DB::statement('ALTER TABLE assortment_plans DROP CONSTRAINT IF EXISTS assortment_plans_commercial_eligibility_chk');
        Schema::table('assortment_plans', function (Blueprint $table): void {
            $table->dropForeign('assortment_plans_business_site_fk');
            $table->dropColumn(['business_site_id', 'commercial_eligibility_status', 'commercial_block_reason']);
        });
        Schema::dropIfExists('tenant_access_scopes');
        Schema::dropIfExists('tenant_access_scope_modes');
    }
};
