<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'product_briefs',
        'product_brief_versions',
        'product_brief_decisions',
        'styles',
        'style_revisions',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Product development requires PostgreSQL with Row-Level Security.');
        }

        Schema::create('product_briefs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('initiative_id')->nullable();
            $table->uuid('collection_id');
            $table->uuid('range_plan_id');
            $table->uuid('range_line_id');
            $table->string('target_consumer', 200);
            $table->text('product_need');
            $table->string('intended_use', 500);
            $table->decimal('target_price', 18, 4)->nullable();
            $table->decimal('target_cost', 18, 4)->nullable();
            $table->char('target_currency', 3);
            $table->string('quality_tier', 80)->nullable();
            $table->date('delivery_need_by')->nullable();
            $table->jsonb('sustainability_intent')->default(DB::raw("'[]'::jsonb"));
            $table->string('status', 32)->default('DRAFT');
            $table->unsignedInteger('current_version')->default(1);
            $table->unsignedInteger('accepted_version')->nullable();
            $table->uuid('owner_user_id');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'range_line_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'initiative_id'])
                ->references(['tenant_id', 'id'])->on('initiatives')->restrictOnDelete();
            $table->foreign(['tenant_id', 'collection_id'])
                ->references(['tenant_id', 'id'])->on('collections')->restrictOnDelete();
            $table->foreign(['tenant_id', 'range_plan_id'])
                ->references(['tenant_id', 'id'])->on('range_plans')->restrictOnDelete();
            $table->foreign(['tenant_id', 'range_line_id'])
                ->references(['tenant_id', 'id'])->on('range_lines')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'status', 'created_at']);
        });

        Schema::create('product_brief_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('product_brief_id');
            $table->unsignedInteger('version_no');
            $table->jsonb('snapshot');
            $table->string('change_reason', 500);
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'product_brief_id', 'version_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'product_brief_id'])
                ->references(['tenant_id', 'id'])->on('product_briefs')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('product_brief_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('product_brief_id');
            $table->unsignedInteger('version_no');
            $table->string('decision', 24);
            $table->text('reason');
            $table->jsonb('actions')->default(DB::raw("'[]'::jsonb"));
            $table->uuid('decided_by');
            $table->timestampTz('decided_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'product_brief_id', 'version_no'])
                ->references(['tenant_id', 'product_brief_id', 'version_no'])->on('product_brief_versions')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'product_brief_id', 'decided_at']);
        });

        Schema::create('styles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('product_brief_id');
            $table->string('style_code', 80);
            $table->string('name', 180);
            $table->string('category_code', 80);
            $table->string('status', 32)->default('DESIGN_IN_PROGRESS');
            $table->unsignedInteger('current_revision')->default(1);
            $table->uuid('owner_user_id');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'style_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'product_brief_id'])
                ->references(['tenant_id', 'id'])->on('product_briefs')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'status', 'created_at']);
        });

        Schema::create('style_revisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('style_id');
            $table->unsignedInteger('revision_no');
            $table->text('design_direction');
            $table->string('silhouette', 180)->nullable();
            $table->text('fit_intent');
            $table->text('construction_intent');
            $table->jsonb('material_direction')->default(DB::raw("'{}'::jsonb"));
            $table->jsonb('colour_direction')->default(DB::raw("'{}'::jsonb"));
            $table->jsonb('target_size_range')->default(DB::raw("'{}'::jsonb"));
            $table->string('status', 32)->default('DESIGN_IN_PROGRESS');
            $table->string('change_summary', 500);
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'style_id', 'revision_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id'])
                ->references(['tenant_id', 'id'])->on('styles')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'style_id', 'revision_no']);
        });

        DB::statement('ALTER TABLE product_briefs ADD CONSTRAINT product_brief_prices_nonnegative_check CHECK ((target_price IS NULL OR target_price >= 0) AND (target_cost IS NULL OR target_cost >= 0))');
        DB::statement('ALTER TABLE product_briefs ADD CONSTRAINT product_brief_accepted_version_check CHECK (accepted_version IS NULL OR accepted_version <= current_version)');
        DB::statement('ALTER TABLE product_brief_versions ADD CONSTRAINT product_brief_version_number_check CHECK (version_no > 0)');
        DB::statement('ALTER TABLE style_revisions ADD CONSTRAINT style_revision_number_check CHECK (revision_no > 0)');

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::statement('GRANT SELECT, INSERT ON product_briefs TO app_runtime');
        DB::statement('GRANT UPDATE (target_consumer, product_need, intended_use, target_price, target_cost, target_currency, quality_tier, delivery_need_by, sustainability_intent, status, current_version, accepted_version, updated_at) ON product_briefs TO app_runtime');
        DB::statement('REVOKE DELETE ON product_briefs FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON product_brief_versions, product_brief_decisions, style_revisions TO app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON product_brief_versions, product_brief_decisions, style_revisions FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON styles TO app_runtime');
        DB::statement('GRANT UPDATE (status, current_revision, updated_at) ON styles TO app_runtime');
        DB::statement('REVOKE DELETE ON styles FROM app_runtime');

        DB::statement("CREATE OR REPLACE FUNCTION prevent_product_development_history_mutation() RETURNS trigger LANGUAGE plpgsql AS 'BEGIN RAISE EXCEPTION ''product-development revisions and decisions are append-only''; END;'");

        foreach (['product_brief_versions', 'product_brief_decisions', 'style_revisions'] as $table) {
            DB::statement("CREATE TRIGGER {$table}_append_only BEFORE UPDATE OR DELETE ON {$table} FOR EACH ROW EXECUTE FUNCTION prevent_product_development_history_mutation()");
        }
    }

    public function down(): void
    {
        foreach (['product_brief_versions', 'product_brief_decisions', 'style_revisions'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_append_only ON {$table}");
        }

        DB::statement('DROP FUNCTION IF EXISTS prevent_product_development_history_mutation()');

        foreach (['style_revisions', 'styles', 'product_brief_decisions', 'product_brief_versions', 'product_briefs'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
