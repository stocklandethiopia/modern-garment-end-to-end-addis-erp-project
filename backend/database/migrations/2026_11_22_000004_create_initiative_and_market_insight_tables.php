<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tenantTables = [
        'initiatives',
        'initiative_versions',
        'initiative_decisions',
        'insight_sources',
        'market_insights',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('The initiative domain requires PostgreSQL with Row-Level Security.');
        }

        Schema::create('initiatives', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('sponsor_entity_id')->nullable();
            $table->uuid('owner_user_id');
            $table->string('code', 80);
            $table->string('initiative_type', 40);
            $table->string('title', 200);
            $table->text('problem_statement');
            $table->text('objective');
            $table->string('target_market', 160);
            $table->string('target_consumer', 160)->nullable();
            $table->text('expected_benefit')->nullable();
            $table->string('status', 32)->default('DRAFT');
            $table->unsignedInteger('current_version')->default(1);
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'sponsor_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'initiative_type', 'status']);
        });

        Schema::create('initiative_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('initiative_id');
            $table->unsignedInteger('version_no');
            $table->jsonb('snapshot');
            $table->string('change_reason', 500);
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'initiative_id', 'version_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'initiative_id'])
                ->references(['tenant_id', 'id'])->on('initiatives')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('initiative_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('initiative_id');
            $table->unsignedInteger('initiative_version');
            $table->string('stage', 32);
            $table->string('decision', 40);
            $table->text('reason');
            $table->jsonb('actions')->default(DB::raw("'[]'::jsonb"));
            $table->date('review_date')->nullable();
            $table->uuid('decided_by');
            $table->timestampTz('decided_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'initiative_id'])
                ->references(['tenant_id', 'id'])->on('initiatives')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'initiative_id', 'decided_at']);
        });

        Schema::create('insight_sources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('source_type', 40);
            $table->string('source_name', 200);
            $table->string('source_reference', 1000)->nullable();
            $table->string('capture_method', 80);
            $table->string('geography', 160)->nullable();
            $table->decimal('reliability_score', 5, 4)->nullable();
            $table->string('permitted_use_basis', 200);
            $table->text('limitations')->nullable();
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'source_type', 'created_at']);
        });

        Schema::create('market_insights', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('source_id');
            $table->uuid('initiative_id')->nullable();
            $table->string('insight_type', 40);
            $table->string('title', 200);
            $table->text('summary');
            $table->timestampTz('observed_at');
            $table->timestampTz('captured_at');
            $table->string('geography', 160)->nullable();
            $table->string('target_segment', 160)->nullable();
            $table->string('channel_code', 60)->nullable();
            $table->string('category_code', 80)->nullable();
            $table->string('metric_name', 120)->nullable();
            $table->decimal('metric_value_numeric', 20, 6)->nullable();
            $table->string('metric_value_text', 300)->nullable();
            $table->string('metric_unit', 40)->nullable();
            $table->unsignedInteger('sample_size')->nullable();
            $table->unsignedInteger('denominator')->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->text('limitations')->nullable();
            $table->string('privacy_basis', 200);
            $table->uuid('analyst_user_id');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'source_id'])
                ->references(['tenant_id', 'id'])->on('insight_sources')->restrictOnDelete();
            $table->foreign(['tenant_id', 'initiative_id'])
                ->references(['tenant_id', 'id'])->on('initiatives')->restrictOnDelete();
            $table->foreign('analyst_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'initiative_id', 'observed_at']);
            $table->index(['tenant_id', 'insight_type', 'captured_at']);
        });

        // Explicit minimum grants; the runtime role never inherits table-owner powers.
        foreach ($this->tenantTables as $table) {
            DB::statement("REVOKE ALL ON {$table} FROM app_runtime");
        }

        DB::statement('GRANT SELECT, INSERT ON initiatives TO app_runtime');
        DB::statement('GRANT UPDATE (status, updated_at) ON initiatives TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON initiative_versions, initiative_decisions, insight_sources, market_insights TO app_runtime');

        foreach ($this->tenantTables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION prevent_initiative_history_mutation()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'initiative history and captured market evidence are append-only';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER initiative_versions_append_only
            BEFORE UPDATE OR DELETE ON initiative_versions
            FOR EACH ROW EXECUTE FUNCTION prevent_initiative_history_mutation();

            CREATE TRIGGER initiative_decisions_append_only
            BEFORE UPDATE OR DELETE ON initiative_decisions
            FOR EACH ROW EXECUTE FUNCTION prevent_initiative_history_mutation();

            CREATE TRIGGER market_insights_append_only
            BEFORE UPDATE OR DELETE ON market_insights
            FOR EACH ROW EXECUTE FUNCTION prevent_initiative_history_mutation();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS market_insights_append_only ON market_insights');
        DB::unprepared('DROP TRIGGER IF EXISTS initiative_decisions_append_only ON initiative_decisions');
        DB::unprepared('DROP TRIGGER IF EXISTS initiative_versions_append_only ON initiative_versions');
        DB::unprepared('DROP FUNCTION IF EXISTS prevent_initiative_history_mutation()');

        Schema::dropIfExists('market_insights');
        Schema::dropIfExists('insight_sources');
        Schema::dropIfExists('initiative_decisions');
        Schema::dropIfExists('initiative_versions');
        Schema::dropIfExists('initiatives');
    }
};
