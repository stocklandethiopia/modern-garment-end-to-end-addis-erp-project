<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'sample_requests',
        'sample_rounds',
        'fit_sessions',
        'fit_observations',
        'fit_decisions',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Sample-room and fit workflows require PostgreSQL with Row-Level Security.');
        }

        DB::statement('ALTER TABLE measurement_spec_versions ADD CONSTRAINT measurement_spec_versions_sample_source_uq UNIQUE (tenant_id, id, style_id, style_revision_no)');
        DB::statement('ALTER TABLE pattern_revisions ADD CONSTRAINT pattern_revisions_sample_source_uq UNIQUE (tenant_id, id, pattern_set_id, style_id, style_revision_no, measurement_spec_version_id)');

        Schema::create('sample_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('style_id');
            $table->unsignedInteger('style_revision_no');
            $table->uuid('pattern_set_id');
            $table->uuid('pattern_revision_id');
            $table->uuid('measurement_spec_version_id');
            $table->string('sample_type', 40);
            $table->text('purpose');
            $table->unsignedInteger('requested_quantity');
            $table->jsonb('requested_size_codes');
            $table->jsonb('requested_colour_codes')->default(DB::raw("'[]'::jsonb"));
            $table->date('required_by')->nullable();
            $table->string('status', 32)->default('REQUESTED');
            $table->unsignedInteger('current_round_no')->default(1);
            $table->uuid('owner_user_id');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'id', 'measurement_spec_version_id'], 'sample_requests_id_spec_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id', 'style_revision_no'])
                ->references(['tenant_id', 'style_id', 'revision_no'])->on('style_revisions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'pattern_set_id', 'style_id'])
                ->references(['tenant_id', 'id', 'style_id'])->on('pattern_sets')->restrictOnDelete();
            $table->foreign(['tenant_id', 'pattern_revision_id', 'pattern_set_id', 'style_id', 'style_revision_no', 'measurement_spec_version_id'])
                ->references(['tenant_id', 'id', 'pattern_set_id', 'style_id', 'style_revision_no', 'measurement_spec_version_id'])
                ->on('pattern_revisions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_spec_version_id', 'style_id', 'style_revision_no'])
                ->references(['tenant_id', 'id', 'style_id', 'style_revision_no'])
                ->on('measurement_spec_versions')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'style_id', 'status', 'created_at'], 'sample_requests_style_status_idx');
        });

        Schema::create('sample_rounds', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('sample_request_id');
            $table->unsignedInteger('round_no');
            $table->unsignedInteger('planned_quantity');
            $table->unsignedInteger('actual_quantity')->nullable();
            $table->string('status', 32)->default('REQUESTED');
            $table->string('round_reason', 500);
            $table->text('completion_summary')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'id', 'sample_request_id'], 'sample_rounds_id_request_uq');
            $table->unique(['tenant_id', 'sample_request_id', 'round_no'], 'sample_rounds_request_round_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'sample_request_id'])
                ->references(['tenant_id', 'id'])->on('sample_requests')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'sample_request_id', 'round_no'], 'sample_rounds_request_idx');
        });

        Schema::create('fit_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('sample_request_id');
            $table->uuid('sample_round_id');
            $table->uuid('measurement_spec_version_id');
            $table->string('fit_size_code', 32);
            $table->string('fit_model_code', 80);
            $table->string('posture_code', 24);
            $table->text('condition_notes');
            $table->date('session_date');
            $table->string('status', 32)->default('DRAFT');
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'id', 'measurement_spec_version_id'], 'fit_sessions_id_spec_uq');
            $table->unique(['tenant_id', 'sample_round_id', 'fit_size_code'], 'fit_sessions_round_size_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'sample_round_id', 'sample_request_id'])
                ->references(['tenant_id', 'id', 'sample_request_id'])->on('sample_rounds')->restrictOnDelete();
            $table->foreign(['tenant_id', 'sample_request_id', 'measurement_spec_version_id'])
                ->references(['tenant_id', 'id', 'measurement_spec_version_id'])->on('sample_requests')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'sample_request_id', 'status'], 'fit_sessions_request_status_idx');
        });

        Schema::create('fit_observations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('fit_session_id');
            $table->uuid('measurement_spec_version_id');
            $table->uuid('measurement_point_id');
            $table->string('size_code', 32);
            $table->decimal('nominal_value', 14, 4);
            $table->decimal('tolerance_minus', 10, 4);
            $table->decimal('tolerance_plus', 10, 4);
            $table->decimal('observed_value', 14, 4);
            $table->string('unit_code', 8);
            $table->string('issue_code', 80)->default('NONE');
            $table->string('severity', 16)->default('NONE');
            $table->boolean('within_tolerance');
            $table->text('finding')->nullable();
            $table->text('corrective_action')->nullable();
            $table->uuid('created_by');
            $table->timestampTz('created_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'fit_session_id', 'measurement_point_id'], 'fit_observations_session_point_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'fit_session_id', 'measurement_spec_version_id'])
                ->references(['tenant_id', 'id', 'measurement_spec_version_id'])->on('fit_sessions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_spec_version_id', 'measurement_point_id'])
                ->references(['tenant_id', 'measurement_spec_version_id', 'id'])->on('measurement_spec_points')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'fit_session_id'], 'fit_observations_session_idx');
        });

        Schema::create('fit_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('fit_session_id');
            $table->string('decision', 32);
            $table->text('reason');
            $table->jsonb('actions')->default(DB::raw("'[]'::jsonb"));
            $table->uuid('decided_by');
            $table->timestampTz('decided_at');
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'fit_session_id'], 'fit_decisions_session_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'fit_session_id'])
                ->references(['tenant_id', 'id'])->on('fit_sessions')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'decided_by', 'decided_at'], 'fit_decisions_actor_idx');
        });

        DB::statement('ALTER TABLE sample_requests ADD CONSTRAINT sample_requests_quantity_check CHECK (requested_quantity > 0 AND current_round_no > 0)');
        DB::statement('ALTER TABLE sample_requests ADD CONSTRAINT sample_requests_sample_type_check CHECK (sample_type IN (\'PROTOTYPE\', \'FIT\', \'SIZE_SET\', \'SALESMAN\', \'PHOTO\', \'PRE_PRODUCTION\', \'WASH_TEST\', \'STRIKE_OFF\', \'PILOT\', \'COUNTER\'))');
        DB::statement('ALTER TABLE sample_requests ADD CONSTRAINT sample_requests_status_check CHECK (status IN (\'REQUESTED\', \'SAMPLE_IN_PROGRESS\', \'AWAITING_FIT\', \'CORRECTION_REQUIRED\', \'FIT_APPROVED\', \'REJECTED\'))');
        DB::statement('ALTER TABLE sample_requests ADD CONSTRAINT sample_requests_size_array_check CHECK (jsonb_typeof(requested_size_codes) = \'array\' AND jsonb_array_length(requested_size_codes) > 0 AND jsonb_typeof(requested_colour_codes) = \'array\')');
        DB::statement('ALTER TABLE sample_rounds ADD CONSTRAINT sample_rounds_quantity_check CHECK (round_no > 0 AND planned_quantity > 0 AND (actual_quantity IS NULL OR (actual_quantity > 0 AND actual_quantity <= planned_quantity)))');
        DB::statement('ALTER TABLE sample_rounds ADD CONSTRAINT sample_rounds_status_check CHECK (status IN (\'REQUESTED\', \'IN_PROGRESS\', \'COMPLETED\', \'CANCELLED\'))');
        DB::statement('ALTER TABLE fit_sessions ADD CONSTRAINT fit_sessions_status_check CHECK (status IN (\'DRAFT\', \'SUBMITTED_FOR_REVIEW\', \'DECIDED\'))');
        DB::statement('ALTER TABLE fit_sessions ADD CONSTRAINT fit_sessions_posture_check CHECK (posture_code IN (\'STANDING\', \'SITTING\', \'MOVING\', \'OTHER\'))');
        DB::statement('ALTER TABLE fit_observations ADD CONSTRAINT fit_observations_values_check CHECK (nominal_value >= 0 AND tolerance_minus >= 0 AND tolerance_plus >= 0 AND observed_value >= 0)');
        DB::statement('ALTER TABLE fit_observations ADD CONSTRAINT fit_observations_severity_check CHECK (severity IN (\'NONE\', \'MINOR\', \'MAJOR\', \'CRITICAL\'))');
        DB::statement('ALTER TABLE fit_decisions ADD CONSTRAINT fit_decisions_type_check CHECK (decision IN (\'APPROVE\', \'APPROVE_WITH_COMMENTS\', \'CORRECTION_REQUIRED\', \'REJECT\'))');

        foreach ($this->tables as $table) {
            DB::statement(sprintf('ALTER TABLE %s ENABLE ROW LEVEL SECURITY', $table));
            DB::statement(sprintf('ALTER TABLE %s FORCE ROW LEVEL SECURITY', $table));
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::statement('GRANT SELECT, INSERT ON sample_requests TO app_runtime');
        DB::statement('GRANT UPDATE (status, current_round_no, updated_at) ON sample_requests TO app_runtime');
        DB::statement('REVOKE DELETE ON sample_requests FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON sample_rounds TO app_runtime');
        DB::statement('GRANT UPDATE (status, actual_quantity, started_at, completed_at, completion_summary, updated_at) ON sample_rounds TO app_runtime');
        DB::statement('REVOKE DELETE ON sample_rounds FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON fit_sessions TO app_runtime');
        DB::statement('GRANT UPDATE (status, updated_at) ON fit_sessions TO app_runtime');
        DB::statement('REVOKE DELETE ON fit_sessions FROM app_runtime');

        DB::statement('GRANT SELECT, INSERT ON fit_observations, fit_decisions TO app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON fit_observations, fit_decisions FROM app_runtime');

        DB::statement('CREATE OR REPLACE FUNCTION prevent_sample_fit_history_mutation() RETURNS trigger LANGUAGE plpgsql AS \'BEGIN RAISE EXCEPTION \'\'fit observations and decisions are append-only\'\'; END;\'');

        foreach (['fit_observations', 'fit_decisions'] as $table) {
            DB::statement(sprintf('CREATE TRIGGER %s_append_only BEFORE UPDATE OR DELETE ON %s FOR EACH ROW EXECUTE FUNCTION prevent_sample_fit_history_mutation()', $table, $table));
        }
    }

    public function down(): void
    {
        foreach (['fit_observations', 'fit_decisions'] as $table) {
            DB::statement(sprintf('DROP TRIGGER IF EXISTS %s_append_only ON %s', $table, $table));
        }

        DB::statement('DROP FUNCTION IF EXISTS prevent_sample_fit_history_mutation()');

        foreach (['fit_decisions', 'fit_observations', 'fit_sessions', 'sample_rounds', 'sample_requests'] as $table) {
            Schema::dropIfExists($table);
        }

        DB::statement('ALTER TABLE pattern_revisions DROP CONSTRAINT IF EXISTS pattern_revisions_sample_source_uq');
        DB::statement('ALTER TABLE measurement_spec_versions DROP CONSTRAINT IF EXISTS measurement_spec_versions_sample_source_uq');
    }
};
