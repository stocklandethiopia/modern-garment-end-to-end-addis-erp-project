<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tenantTables = [
        'brands',
        'brand_strategy_versions',
        'merchandising_decisions',
        'seasons',
        'collections',
        'collection_versions',
        'range_plans',
        'range_lines',
        'assortment_plans',
        'assortment_lines',
        'demand_forecast_runs',
        'demand_forecasts',
        'forecast_adjustments',
        'otb_policies',
        'otb_periods',
        'otb_scenarios',
        'price_lists',
        'price_list_lines',
        'promotions',
        'promotion_eligibility',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Merchandising planning requires PostgreSQL with Row-Level Security.');
        }

        Schema::create('brands', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('owner_entity_id')->nullable();
            $table->string('code', 80);
            $table->string('name', 180);
            $table->string('status', 24)->default('DRAFT');
            $table->unsignedInteger('current_strategy_version')->default(0);
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'owner_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
        });

        Schema::create('brand_strategy_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('brand_id');
            $table->unsignedInteger('version_no');
            $table->string('positioning', 500);
            $table->string('target_consumer', 200);
            $table->text('value_proposition');
            $table->string('quality_tier', 100)->nullable();
            $table->jsonb('approved_categories')->default(DB::raw("'[]'::jsonb"));
            $table->jsonb('price_architecture')->default(DB::raw("'{}'::jsonb"));
            $table->jsonb('approved_channels')->default(DB::raw("'[]'::jsonb"));
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->string('change_reason', 500);
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'brand_id', 'version_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'brand_id'])
                ->references(['tenant_id', 'id'])->on('brands')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('(effective_to IS NULL OR effective_from IS NULL OR effective_to >= effective_from)');
        });

        Schema::create('merchandising_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('object_type', 40);
            $table->uuid('object_id');
            $table->unsignedInteger('object_version')->default(1);
            $table->string('decision', 32);
            $table->text('reason');
            $table->jsonb('actions')->default(DB::raw("'[]'::jsonb"));
            $table->date('review_date')->nullable();
            $table->uuid('decided_by');
            $table->timestampTz('decided_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'object_type', 'object_id', 'decided_at']);
        });

        Schema::create('seasons', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('code', 80);
            $table->string('name', 180);
            $table->string('calendar_type', 32)->default('FASHION');
            $table->string('market_scope', 160)->nullable();
            $table->string('timezone', 64)->default('Africa/Addis_Ababa');
            $table->date('design_start')->nullable();
            $table->date('buy_start')->nullable();
            $table->date('production_start')->nullable();
            $table->date('production_end')->nullable();
            $table->date('delivery_start')->nullable();
            $table->date('delivery_end')->nullable();
            $table->date('selling_start')->nullable();
            $table->date('selling_end')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('(production_end IS NULL OR production_start IS NULL OR production_end >= production_start)');
            $table->check('(delivery_end IS NULL OR delivery_start IS NULL OR delivery_end >= delivery_start)');
            $table->check('(selling_end IS NULL OR selling_start IS NULL OR selling_end >= selling_start)');
        });

        Schema::create('collections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('brand_id');
            $table->uuid('season_id');
            $table->string('code', 80);
            $table->string('name', 180);
            $table->string('concept', 500)->nullable();
            $table->string('target_market', 160);
            $table->unsignedInteger('current_version')->default(1);
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('owner_user_id');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'brand_id'])
                ->references(['tenant_id', 'id'])->on('brands')->restrictOnDelete();
            $table->foreign(['tenant_id', 'season_id'])
                ->references(['tenant_id', 'id'])->on('seasons')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('collection_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('collection_id');
            $table->unsignedInteger('version_no');
            $table->text('story');
            $table->string('target_consumer', 180);
            $table->jsonb('category_architecture')->default(DB::raw("'[]'::jsonb"));
            $table->jsonb('price_margin_envelope')->default(DB::raw("'{}'::jsonb"));
            $table->decimal('budget_amount', 20, 4)->nullable();
            $table->char('budget_currency', 3)->nullable();
            $table->date('delivery_start')->nullable();
            $table->date('delivery_end')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->string('change_reason', 500);
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'collection_id', 'version_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'collection_id'])
                ->references(['tenant_id', 'id'])->on('collections')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('(delivery_end IS NULL OR delivery_start IS NULL OR delivery_end >= delivery_start)');
        });

        Schema::create('range_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('collection_id');
            $table->unsignedInteger('collection_version');
            $table->string('code', 80);
            $table->date('plan_start');
            $table->date('plan_end');
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('owner_user_id');
            $table->timestampTz('frozen_at')->nullable();
            $table->uuid('frozen_by')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'collection_id', 'collection_version'])
                ->references(['tenant_id', 'collection_id', 'version_no'])->on('collection_versions')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('frozen_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('plan_end >= plan_start');
        });

        Schema::create('range_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('range_plan_id');
            $table->string('line_code', 80);
            $table->string('category_code', 80);
            $table->string('target_tier', 80)->nullable();
            $table->string('product_hypothesis', 500);
            $table->unsignedInteger('planned_units');
            $table->decimal('target_retail_price', 18, 4)->nullable();
            $table->decimal('target_wholesale_price', 18, 4)->nullable();
            $table->char('price_currency', 3)->nullable();
            $table->decimal('target_margin_percent', 7, 4)->nullable();
            $table->jsonb('channel_scope')->default(DB::raw("'[]'::jsonb"));
            $table->jsonb('size_colour_intent')->default(DB::raw("'{}'::jsonb"));
            $table->string('lifecycle_type', 24)->default('NEW');
            $table->string('forecast_basis', 500);
            $table->date('launch_date')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'range_plan_id', 'line_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'range_plan_id'])
                ->references(['tenant_id', 'id'])->on('range_plans')->restrictOnDelete();
            $table->check('planned_units >= 0');
            $table->check('(target_margin_percent IS NULL OR target_margin_percent BETWEEN -100 AND 100)');
            $table->check('(target_retail_price IS NULL OR target_retail_price >= 0)');
            $table->check('(target_wholesale_price IS NULL OR target_wholesale_price >= 0)');
        });

        Schema::create('assortment_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('range_plan_id');
            $table->string('code', 80);
            $table->string('channel_code', 60);
            $table->string('market_scope', 160);
            $table->string('account_reference', 120)->nullable();
            $table->string('location_cluster_reference', 120)->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('owner_user_id');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'range_plan_id'])
                ->references(['tenant_id', 'id'])->on('range_plans')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('assortment_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('assortment_plan_id');
            $table->uuid('range_line_id');
            $table->jsonb('variant_size_curve')->default(DB::raw("'{}'::jsonb"));
            $table->unsignedInteger('planned_units');
            $table->date('launch_date')->nullable();
            $table->unsignedInteger('minimum_depth')->nullable();
            $table->unsignedInteger('maximum_depth')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'assortment_plan_id', 'range_line_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'assortment_plan_id'])
                ->references(['tenant_id', 'id'])->on('assortment_plans')->restrictOnDelete();
            $table->foreign(['tenant_id', 'range_line_id'])
                ->references(['tenant_id', 'id'])->on('range_lines')->restrictOnDelete();
            $table->check('planned_units >= 0');
            $table->check('(maximum_depth IS NULL OR minimum_depth IS NULL OR maximum_depth >= minimum_depth)');
        });

        Schema::create('demand_forecast_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('grain', 80);
            $table->string('method', 80);
            $table->string('model_version', 80)->nullable();
            $table->string('horizon', 80);
            $table->string('scenario_code', 80);
            $table->date('forecast_start');
            $table->date('forecast_end');
            $table->string('status', 24)->default('DRAFT');
            $table->jsonb('assumptions')->default(DB::raw("'{}'::jsonb"));
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('forecast_end >= forecast_start');
        });

        Schema::create('demand_forecasts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('forecast_run_id');
            $table->jsonb('dimension_refs')->default(DB::raw("'{}'::jsonb"));
            $table->date('time_bucket');
            $table->decimal('forecast_quantity', 20, 4);
            $table->decimal('forecast_value', 20, 4)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('confidence_low', 20, 4)->nullable();
            $table->decimal('confidence_high', 20, 4)->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'forecast_run_id'])
                ->references(['tenant_id', 'id'])->on('demand_forecast_runs')->restrictOnDelete();
            $table->check('(confidence_low IS NULL OR confidence_high IS NULL OR confidence_high >= confidence_low)');
        });

        Schema::create('forecast_adjustments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('forecast_id');
            $table->decimal('baseline_value', 20, 4);
            $table->decimal('override_value', 20, 4);
            $table->decimal('delta_value', 20, 4);
            $table->string('reason', 500);
            $table->string('evidence_reference', 1000)->nullable();
            $table->uuid('authored_by');
            $table->uuid('approved_by')->nullable();
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'forecast_id'])
                ->references(['tenant_id', 'id'])->on('demand_forecasts')->restrictOnDelete();
            $table->foreign('authored_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('otb_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('legal_entity_id');
            $table->string('code', 80);
            $table->string('budget_basis', 24);
            $table->string('formula_version', 40)->default('RETAIL_OTB_V1');
            $table->string('valuation_basis', 24);
            $table->jsonb('inclusion_rules')->default(DB::raw("'{}'::jsonb"));
            $table->jsonb('approval_thresholds')->default(DB::raw("'{}'::jsonb"));
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('(effective_to IS NULL OR effective_to >= effective_from)');
        });

        Schema::create('otb_periods', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('otb_policy_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->char('currency', 3);
            $table->decimal('beginning_inventory', 20, 4)->default(0);
            $table->decimal('planned_sales', 20, 4)->default(0);
            $table->decimal('planned_markdowns', 20, 4)->default(0);
            $table->decimal('planned_ending_inventory', 20, 4)->default(0);
            $table->decimal('on_order_purchases', 20, 4)->default(0);
            $table->decimal('commitments', 20, 4)->default(0);
            $table->decimal('actual_receipts', 20, 4)->default(0);
            $table->decimal('available_budget', 20, 4)->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'otb_policy_id', 'period_start']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'otb_policy_id'])
                ->references(['tenant_id', 'id'])->on('otb_policies')->restrictOnDelete();
            $table->check('period_end >= period_start');
        });

        Schema::create('otb_scenarios', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('otb_period_id');
            $table->uuid('range_plan_id')->nullable();
            $table->uuid('forecast_run_id')->nullable();
            $table->jsonb('assumptions')->default(DB::raw("'{}'::jsonb"));
            $table->jsonb('result')->default(DB::raw("'{}'::jsonb"));
            $table->string('status', 24)->default('SIMULATED');
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'otb_period_id'])
                ->references(['tenant_id', 'id'])->on('otb_periods')->restrictOnDelete();
            $table->foreign(['tenant_id', 'range_plan_id'])
                ->references(['tenant_id', 'id'])->on('range_plans')->restrictOnDelete();
            $table->foreign(['tenant_id', 'forecast_run_id'])
                ->references(['tenant_id', 'id'])->on('demand_forecast_runs')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('price_lists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('legal_entity_id');
            $table->uuid('brand_id')->nullable();
            $table->string('code', 80);
            $table->string('channel_code', 60);
            $table->string('market_scope', 160);
            $table->string('account_reference', 120)->nullable();
            $table->char('currency', 3);
            $table->unsignedInteger('version_no')->default(1);
            $table->timestampTz('effective_from');
            $table->timestampTz('effective_to')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('created_by');
            $table->uuid('published_by')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code', 'version_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->foreign(['tenant_id', 'brand_id'])
                ->references(['tenant_id', 'id'])->on('brands')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('(effective_to IS NULL OR effective_to > effective_from)');
        });

        Schema::create('price_list_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('price_list_id');
            $table->string('product_reference', 120);
            $table->decimal('list_price', 18, 4);
            $table->decimal('wholesale_price', 18, 4)->nullable();
            $table->decimal('price_floor', 18, 4)->nullable();
            $table->decimal('cost_basis', 18, 4)->nullable();
            $table->string('tax_profile_reference', 120)->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'price_list_id', 'product_reference']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'price_list_id'])
                ->references(['tenant_id', 'id'])->on('price_lists')->restrictOnDelete();
            $table->check('list_price >= 0');
            $table->check('(wholesale_price IS NULL OR wholesale_price >= 0)');
            $table->check('(price_floor IS NULL OR price_floor >= 0)');
            $table->check('(cost_basis IS NULL OR cost_basis >= 0)');
        });

        Schema::create('promotions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('price_list_id');
            $table->string('code', 80);
            $table->string('promotion_type', 24);
            $table->decimal('discount_value', 18, 4);
            $table->string('stack_policy', 24)->default('NO_STACK');
            $table->decimal('budget_amount', 20, 4)->nullable();
            $table->string('funded_by', 80)->nullable();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->decimal('margin_floor', 7, 4)->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'price_list_id'])
                ->references(['tenant_id', 'id'])->on('price_lists')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
            $table->check('ends_at > starts_at');
            $table->check('discount_value >= 0');
            $table->check('(margin_floor IS NULL OR margin_floor BETWEEN -100 AND 100)');
        });

        Schema::create('promotion_eligibility', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('promotion_id');
            $table->string('scope_type', 24);
            $table->string('scope_reference', 160);
            $table->boolean('is_exclusion')->default(false);
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'promotion_id', 'scope_type', 'scope_reference', 'is_exclusion']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'promotion_id'])
                ->references(['tenant_id', 'id'])->on('promotions')->restrictOnDelete();
        });

        $mutableTables = [
            'brands',
            'seasons',
            'collections',
            'range_plans',
            'range_lines',
            'assortment_plans',
            'assortment_lines',
            'otb_policies',
            'otb_periods',
            'price_lists',
            'price_list_lines',
            'promotions',
            'promotion_eligibility',
        ];

        $appendOnlyTables = [
            'brand_strategy_versions',
            'merchandising_decisions',
            'collection_versions',
            'demand_forecast_runs',
            'demand_forecasts',
            'forecast_adjustments',
            'otb_scenarios',
        ];

        foreach (array_merge($mutableTables, $appendOnlyTables) as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        foreach ($mutableTables as $table) {
            DB::statement("GRANT SELECT, INSERT, UPDATE ON {$table} TO app_runtime");
            DB::statement("REVOKE DELETE ON {$table} FROM app_runtime");
        }

        foreach ($appendOnlyTables as $table) {
            DB::statement("GRANT SELECT, INSERT ON {$table} TO app_runtime");
            DB::statement("REVOKE UPDATE, DELETE ON {$table} FROM app_runtime");
        }

        DB::statement("CREATE OR REPLACE FUNCTION prevent_merchandising_history_mutation() RETURNS trigger LANGUAGE plpgsql AS 'BEGIN RAISE EXCEPTION ''approved merchandising versions and forecast history are append-only''; END;'");

        foreach (['brand_strategy_versions', 'merchandising_decisions', 'collection_versions', 'demand_forecast_runs', 'demand_forecasts', 'forecast_adjustments', 'otb_scenarios'] as $table) {
            DB::statement("CREATE TRIGGER {$table}_append_only BEFORE UPDATE OR DELETE ON {$table} FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()");
        }
    }

    public function down(): void
    {
        foreach (['brand_strategy_versions', 'collection_versions', 'demand_forecast_runs', 'demand_forecasts', 'forecast_adjustments', 'otb_scenarios'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_append_only ON {$table}");
        }
        DB::statement('DROP FUNCTION IF EXISTS prevent_merchandising_history_mutation()');

        foreach ([
            'promotion_eligibility',
            'promotions',
            'price_list_lines',
            'price_lists',
            'otb_scenarios',
            'otb_periods',
            'otb_policies',
            'forecast_adjustments',
            'demand_forecasts',
            'demand_forecast_runs',
            'assortment_lines',
            'assortment_plans',
            'range_lines',
            'range_plans',
            'collection_versions',
            'collections',
            'seasons',
            'merchandising_decisions',
            'brand_strategy_versions',
            'brands',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
