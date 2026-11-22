<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_licences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('legal_entity_id');
            $table->uuid('site_id')->nullable();
            $table->string('activity_code', 80);
            $table->string('licence_number', 120);
            $table->string('issuing_authority', 180);
            $table->date('issue_date')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('evidence_reference', 255)->nullable();
            $table->string('status', 24)->default('REVIEW_REQUIRED');
            $table->timestampsTz();

            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'legal_entity_id', 'licence_number']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->foreign(['tenant_id', 'site_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id', 'legal_entity_id'])->on('business_sites')->restrictOnDelete();
            $table->index(['tenant_id', 'legal_entity_id', 'activity_code', 'status']);
            $table->index(['tenant_id', 'site_id', 'valid_to']);
        });

        DB::statement('ALTER TABLE entity_licences ENABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE entity_licences FORCE ROW LEVEL SECURITY');
        DB::statement(
            "CREATE POLICY tenant_isolation_entity_licences ON entity_licences
             USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
             WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
        );

        DB::statement('REVOKE ALL ON entity_licences FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT ON entity_licences TO app_runtime');

        // Branch-register evidence is append-only for the API runtime.
        DB::statement('REVOKE UPDATE, DELETE ON branch_registrations FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT ON branch_registrations TO app_runtime');
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_licences');
    }
};
