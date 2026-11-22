<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'measurement_spec_versions',
        'measurement_spec_points',
        'measurement_spec_values',
        'pattern_sets',
        'pattern_revisions',
        'pattern_pieces',
        'grade_rules',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Technical product development requires PostgreSQL with Row-Level Security.');
        }

        Schema::create('measurement_spec_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('style_id');
            $table->unsignedInteger('style_revision_no');
            $table->unsignedInteger('version_no');
            $table->string('size_system_code', 80);
            $table->jsonb('size_sequence');
            $table->string('base_size_code', 32);
            $table->string('measurement_condition', 120);
            $table->string('status', 32)->default('DRAFT');
            $table->string('change_reason', 500);
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'style_id', 'style_revision_no', 'version_no'], 'measurement_spec_versions_style_rev_version_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id', 'style_revision_no'], 'measurement_spec_versions_style_revision_fk')
                ->references(['tenant_id', 'style_id', 'revision_no'])->on('style_revisions')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'style_id', 'status', 'version_no']);
        });

        Schema::create('measurement_spec_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('measurement_spec_version_id');
            $table->string('point_code', 80);
            $table->string('name', 180);
            $table->text('measurement_method');
            $table->string('unit_code', 8);
            $table->boolean('is_required')->default(true);
            $table->string('diagram_reference', 240)->nullable();
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'measurement_spec_version_id', 'id']);
            $table->unique(['tenant_id', 'measurement_spec_version_id', 'point_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_spec_version_id'])
                ->references(['tenant_id', 'id'])->on('measurement_spec_versions')->restrictOnDelete();
            $table->index(['tenant_id', 'measurement_spec_version_id']);
        });

        Schema::create('measurement_spec_values', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('measurement_point_id');
            $table->string('size_code', 32);
            $table->decimal('nominal_value', 14, 4);
            $table->decimal('tolerance_minus', 10, 4)->default(0);
            $table->decimal('tolerance_plus', 10, 4)->default(0);
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'measurement_point_id', 'size_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_point_id'])
                ->references(['tenant_id', 'id'])->on('measurement_spec_points')->restrictOnDelete();
            $table->index(['tenant_id', 'measurement_point_id', 'size_code']);
        });

        Schema::create('pattern_sets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('style_id');
            $table->string('pattern_code', 80);
            $table->string('name', 180);
            $table->string('status', 32)->default('DRAFT');
            $table->unsignedInteger('current_revision')->default(0);
            $table->uuid('owner_user_id');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'id', 'style_id']);
            $table->unique(['tenant_id', 'style_id', 'pattern_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id'])
                ->references(['tenant_id', 'id'])->on('styles')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'style_id', 'status']);
        });

        Schema::create('pattern_revisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('pattern_set_id');
            $table->uuid('style_id');
            $table->unsignedInteger('style_revision_no');
            $table->uuid('measurement_spec_version_id');
            $table->unsignedInteger('revision_no');
            $table->string('size_system_code', 80);
            $table->string('base_size_code', 32);
            $table->string('status', 32)->default('DRAFT');
            $table->string('change_summary', 500);
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'id', 'measurement_spec_version_id']);
            $table->unique(['tenant_id', 'pattern_set_id', 'revision_no']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'pattern_set_id', 'style_id'])
                ->references(['tenant_id', 'id', 'style_id'])->on('pattern_sets')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id', 'style_revision_no'])
                ->references(['tenant_id', 'style_id', 'revision_no'])->on('style_revisions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_spec_version_id'])
                ->references(['tenant_id', 'id'])->on('measurement_spec_versions')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'pattern_set_id', 'revision_no']);
        });

        Schema::create('pattern_pieces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('pattern_revision_id');
            $table->string('piece_code', 80);
            $table->string('description', 240);
            $table->string('material_reference', 120)->nullable();
            $table->string('grain_direction', 40)->nullable();
            $table->jsonb('seam_allowance')->default(DB::raw("'{}'::jsonb"));
            $table->unsignedInteger('piece_count')->default(1);
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'pattern_revision_id', 'piece_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'pattern_revision_id'])
                ->references(['tenant_id', 'id'])->on('pattern_revisions')->restrictOnDelete();
            $table->index(['tenant_id', 'pattern_revision_id']);
        });

        Schema::create('grade_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('pattern_revision_id');
            $table->uuid('measurement_spec_version_id');
            $table->uuid('measurement_point_id');
            $table->string('from_size_code', 32);
            $table->string('to_size_code', 32);
            $table->decimal('increment', 14, 4);
            $table->string('unit_code', 8);
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'pattern_revision_id', 'measurement_point_id', 'from_size_code', 'to_size_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'pattern_revision_id', 'measurement_spec_version_id'])
                ->references(['tenant_id', 'id', 'measurement_spec_version_id'])->on('pattern_revisions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_spec_version_id', 'measurement_point_id'])
                ->references(['tenant_id', 'measurement_spec_version_id', 'id'])->on('measurement_spec_points')->restrictOnDelete();
            $table->index(['tenant_id', 'pattern_revision_id', 'measurement_point_id']);
        });

        DB::statement('ALTER TABLE measurement_spec_versions ADD CONSTRAINT measurement_spec_version_number_check CHECK (version_no > 0)');
        DB::statement("ALTER TABLE measurement_spec_versions ADD CONSTRAINT measurement_spec_status_check CHECK (status IN ('DRAFT', 'APPROVED', 'REJECTED'))");
        DB::statement('ALTER TABLE measurement_spec_values ADD CONSTRAINT measurement_spec_values_nonnegative_check CHECK (nominal_value >= 0 AND tolerance_minus >= 0 AND tolerance_plus >= 0)');
        DB::statement('ALTER TABLE pattern_sets ADD CONSTRAINT pattern_set_revision_check CHECK (current_revision >= 0)');
        DB::statement('ALTER TABLE pattern_revisions ADD CONSTRAINT pattern_revision_number_check CHECK (revision_no > 0)');
        DB::statement("ALTER TABLE pattern_revisions ADD CONSTRAINT pattern_revision_status_check CHECK (status IN ('DRAFT', 'APPROVED', 'REJECTED'))");
        DB::statement('ALTER TABLE pattern_pieces ADD CONSTRAINT pattern_piece_count_check CHECK (piece_count > 0)');
        DB::statement('ALTER TABLE grade_rules ADD CONSTRAINT grade_rule_distinct_sizes_check CHECK (from_size_code <> to_size_code)');

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::statement('GRANT SELECT, INSERT ON measurement_spec_versions TO app_runtime');
        DB::statement('GRANT UPDATE (status, approved_by, approved_at) ON measurement_spec_versions TO app_runtime');
        DB::statement('REVOKE DELETE ON measurement_spec_versions FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT ON measurement_spec_points, measurement_spec_values TO app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON measurement_spec_points, measurement_spec_values FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON pattern_sets TO app_runtime');
        DB::statement('GRANT UPDATE (status, current_revision, updated_at) ON pattern_sets TO app_runtime');
        DB::statement('REVOKE DELETE ON pattern_sets FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT ON pattern_revisions TO app_runtime');
        DB::statement('GRANT UPDATE (status, approved_by, approved_at) ON pattern_revisions TO app_runtime');
        DB::statement('REVOKE DELETE ON pattern_revisions FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON pattern_pieces, grade_rules TO app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON pattern_pieces, grade_rules FROM app_runtime');

        DB::statement("CREATE OR REPLACE FUNCTION prevent_technical_spec_history_mutation() RETURNS trigger LANGUAGE plpgsql AS 'BEGIN RAISE EXCEPTION ''technical specification history is append-only''; END;'");

        foreach (['measurement_spec_points', 'measurement_spec_values', 'pattern_pieces', 'grade_rules'] as $table) {
            DB::statement("CREATE TRIGGER {$table}_append_only BEFORE UPDATE OR DELETE ON {$table} FOR EACH ROW EXECUTE FUNCTION prevent_technical_spec_history_mutation()");
        }
    }

    public function down(): void
    {
        foreach (['measurement_spec_points', 'measurement_spec_values', 'pattern_pieces', 'grade_rules'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_append_only ON {$table}");
        }

        DB::statement('DROP FUNCTION IF EXISTS prevent_technical_spec_history_mutation()');

        foreach (['grade_rules', 'pattern_pieces', 'pattern_revisions', 'pattern_sets', 'measurement_spec_values', 'measurement_spec_points', 'measurement_spec_versions'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
