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
            throw new RuntimeException('BOM and technical release governance require PostgreSQL Row-Level Security.');
        }

        Schema::create('style_bom_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('style_id');
            $table->unsignedInteger('style_revision_no');
            $table->unsignedInteger('version_no');
            $table->string('status', 24)->default('DRAFT');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('change_reason');
            $table->string('material_resolution_status', 32)->default('UNVERIFIED_WP07');
            $table->uuid('created_by');
            $table->uuid('submitted_by')->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->uuid('decided_by')->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'style_bom_versions_tenant_id_uq');
            $table->unique(['tenant_id', 'id', 'style_id', 'style_revision_no'], 'style_bom_versions_release_fk_uq');
            $table->unique(['tenant_id', 'style_id', 'style_revision_no', 'version_no'], 'style_bom_versions_style_ver_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id', 'style_revision_no'], 'style_bom_versions_style_rev_fk')
                ->references(['tenant_id', 'style_id', 'revision_no'])->on('style_revisions')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'style_id', 'status', 'version_no'], 'style_bom_versions_style_status_idx');
        });

        Schema::create('style_bom_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('bom_version_id');
            $table->unsignedInteger('line_no');
            $table->string('component_code', 100);
            $table->string('component_type', 32);
            $table->string('description', 240);
            $table->string('material_reference', 160);
            $table->string('supplier_reference', 160)->nullable();
            $table->string('unit_code', 16);
            $table->decimal('net_quantity_per_finished_unit', 16, 6);
            $table->decimal('wastage_percent', 8, 4);
            $table->string('wastage_basis_code', 32);
            $table->jsonb('variant_applicability')->default(DB::raw("'{}'::jsonb"));
            $table->string('attachment_position', 160)->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->text('technical_notes')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'style_bom_lines_tenant_id_uq');
            $table->unique(['tenant_id', 'bom_version_id', 'line_no'], 'style_bom_lines_version_line_uq');
            $table->unique(['tenant_id', 'bom_version_id', 'component_code'], 'style_bom_lines_version_code_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'bom_version_id'], 'style_bom_lines_version_fk')
                ->references(['tenant_id', 'id'])->on('style_bom_versions')->restrictOnDelete();
            $table->index(['tenant_id', 'bom_version_id', 'component_type'], 'style_bom_lines_type_idx');
        });

        Schema::create('style_bom_substitution_candidates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('bom_line_id');
            $table->string('alternative_component_reference', 160);
            $table->text('technical_equivalence_basis');
            $table->text('risk_and_limitations');
            $table->string('status', 32)->default('PROPOSED');
            $table->string('quality_approval_status', 32)->default('PENDING_WP11');
            $table->uuid('proposed_by');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'style_bom_subst_tenant_id_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'bom_line_id'], 'style_bom_subst_line_fk')
                ->references(['tenant_id', 'id'])->on('style_bom_lines')->restrictOnDelete();
            $table->foreign('proposed_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('style_bom_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('bom_version_id');
            $table->unsignedInteger('version_no');
            $table->string('decision', 24);
            $table->text('reason');
            $table->jsonb('actions')->default(DB::raw("'[]'::jsonb"));
            $table->uuid('decided_by');
            $table->timestampTz('decided_at');
            $table->unique(['tenant_id', 'id'], 'style_bom_decisions_tenant_id_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'bom_version_id'], 'style_bom_decisions_version_fk')
                ->references(['tenant_id', 'id'])->on('style_bom_versions')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('measurement_spec_versions', function (Blueprint $table): void {
            $table->unique(
                ['tenant_id', 'id', 'style_id', 'style_revision_no'],
                'measurement_spec_versions_techpack_fk_uq',
            );
        });

        Schema::table('pattern_revisions', function (Blueprint $table): void {
            $table->unique(
                ['tenant_id', 'id', 'style_id', 'style_revision_no', 'measurement_spec_version_id'],
                'pattern_revisions_techpack_fk_uq',
            );
        });

        Schema::table('sample_requests', function (Blueprint $table): void {
            $table->unique(
                ['tenant_id', 'id', 'style_id', 'style_revision_no', 'pattern_revision_id', 'measurement_spec_version_id'],
                'sample_requests_techpack_fk_uq',
            );
        });

        Schema::create('tech_pack_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('style_id');
            $table->unsignedInteger('style_revision_no');
            $table->uuid('bom_version_id');
            $table->uuid('measurement_spec_version_id');
            $table->uuid('pattern_revision_id');
            $table->uuid('sample_request_id')->nullable();
            $table->unsignedInteger('version_no');
            $table->text('construction_specification');
            $table->text('seam_and_stitch_specification');
            $table->text('care_instructions');
            $table->text('label_specification');
            $table->text('packaging_specification');
            $table->jsonb('quality_checkpoints')->default(DB::raw("'[]'::jsonb"));
            $table->string('status', 24)->default('DRAFT');
            $table->char('asset_manifest_sha256', 64)->nullable();
            $table->char('release_manifest_sha256', 64)->nullable();
            $table->uuid('created_by');
            $table->uuid('submitted_by')->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->uuid('decided_by')->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->uuid('released_by')->nullable();
            $table->timestampTz('released_at')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'tech_pack_versions_tenant_id_uq');
            $table->unique(['tenant_id', 'id', 'style_id', 'style_revision_no'], 'tech_pack_versions_release_fk_uq');
            $table->unique(['tenant_id', 'style_id', 'style_revision_no', 'version_no'], 'tech_pack_versions_style_ver_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'style_id', 'style_revision_no'], 'tech_pack_versions_style_rev_fk')
                ->references(['tenant_id', 'style_id', 'revision_no'])->on('style_revisions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'bom_version_id', 'style_id', 'style_revision_no'], 'tech_pack_versions_bom_fk')
                ->references(['tenant_id', 'id', 'style_id', 'style_revision_no'])->on('style_bom_versions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'measurement_spec_version_id', 'style_id', 'style_revision_no'], 'tech_pack_versions_measurement_fk')
                ->references(['tenant_id', 'id', 'style_id', 'style_revision_no'])->on('measurement_spec_versions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'pattern_revision_id', 'style_id', 'style_revision_no', 'measurement_spec_version_id'], 'tech_pack_versions_pattern_fk')
                ->references(['tenant_id', 'id', 'style_id', 'style_revision_no', 'measurement_spec_version_id'])->on('pattern_revisions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'sample_request_id', 'style_id', 'style_revision_no', 'pattern_revision_id', 'measurement_spec_version_id'], 'tech_pack_versions_sample_fk')
                ->references(['tenant_id', 'id', 'style_id', 'style_revision_no', 'pattern_revision_id', 'measurement_spec_version_id'])->on('sample_requests')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('released_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'style_id', 'status', 'version_no'], 'tech_pack_versions_style_status_idx');
        });

        Schema::create('tech_pack_assets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('tech_pack_version_id');
            $table->string('asset_type', 32);
            $table->string('asset_reference', 500);
            $table->string('file_name', 240);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->text('usage_rights_basis');
            $table->string('rights_owner_reference', 160);
            $table->date('rights_expiry')->nullable();
            $table->string('review_status', 24)->default('PENDING_REVIEW');
            $table->uuid('reviewed_by')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id'], 'tech_pack_assets_tenant_id_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'tech_pack_version_id'], 'tech_pack_assets_version_fk')
                ->references(['tenant_id', 'id'])->on('tech_pack_versions')->restrictOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'tech_pack_version_id', 'asset_type', 'review_status'], 'tech_pack_assets_type_status_idx');
        });

        Schema::create('tech_pack_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('tech_pack_version_id');
            $table->unsignedInteger('version_no');
            $table->string('decision', 24);
            $table->text('reason');
            $table->jsonb('actions')->default(DB::raw("'[]'::jsonb"));
            $table->uuid('decided_by');
            $table->timestampTz('decided_at');
            $table->unique(['tenant_id', 'id'], 'tech_pack_decisions_tenant_id_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'tech_pack_version_id'], 'tech_pack_decisions_version_fk')
                ->references(['tenant_id', 'id'])->on('tech_pack_versions')->restrictOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('tech_pack_delivery_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('tech_pack_version_id');
            $table->string('recipient_domain', 24);
            $table->string('recipient_reference', 160);
            $table->char('manifest_sha256', 64);
            $table->string('acknowledgement_reference', 240);
            $table->uuid('acknowledged_by');
            $table->timestampTz('acknowledged_at');
            $table->text('acknowledgement_note')->nullable();
            $table->unique(['tenant_id', 'id'], 'tech_pack_receipts_tenant_id_uq');
            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
            $table->foreign(['tenant_id', 'tech_pack_version_id'], 'tech_pack_receipts_version_fk')
                ->references(['tenant_id', 'id'])->on('tech_pack_versions')->restrictOnDelete();
            $table->foreign('acknowledged_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'tech_pack_version_id', 'recipient_domain'], 'tech_pack_receipts_recipient_idx');
        });

        DB::statement("ALTER TABLE style_bom_versions
            ADD CONSTRAINT style_bom_versions_status_chk
                CHECK (status IN ('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'SUPERSEDED')),
            ADD CONSTRAINT style_bom_versions_dates_chk
                CHECK (effective_to IS NULL OR effective_to >= effective_from),
            ADD CONSTRAINT style_bom_versions_material_status_chk
                CHECK (material_resolution_status IN ('UNVERIFIED_WP07', 'VERIFIED')),
            ADD CONSTRAINT style_bom_versions_decision_chk
                CHECK (
                    (status IN ('DRAFT', 'PENDING_APPROVAL') AND decided_by IS NULL AND decided_at IS NULL AND decision_reason IS NULL)
                    OR (status IN ('APPROVED', 'REJECTED', 'SUPERSEDED') AND decided_by IS NOT NULL AND decided_at IS NOT NULL AND decision_reason IS NOT NULL)
                )");

        DB::statement("ALTER TABLE style_bom_lines
            ADD CONSTRAINT style_bom_lines_component_type_chk
                CHECK (component_type IN ('FABRIC', 'LINING', 'INTERLINING', 'TRIM', 'THREAD', 'LABEL', 'PACKAGING', 'CHEMICAL', 'PROCESS_INPUT')),
            ADD CONSTRAINT style_bom_lines_unit_chk
                CHECK (unit_code IN ('M', 'CM', 'KG', 'G', 'PC', 'PAIR', 'SET', 'L', 'ML', 'M2', 'ROLL')),
            ADD CONSTRAINT style_bom_lines_quantity_chk
                CHECK (net_quantity_per_finished_unit > 0 AND wastage_percent >= 0 AND wastage_percent <= 100),
            ADD CONSTRAINT style_bom_lines_waste_basis_chk
                CHECK (wastage_basis_code IN ('CUTTING_LOSS', 'KNITTING_LOSS', 'SPREADING_LOSS', 'PRINTING_LOSS', 'EMBROIDERY_LOSS', 'SEWING_LOSS', 'FINISHING_LOSS', 'PACKAGING_LOSS', 'PROCESS_LOSS'))");

        DB::statement("ALTER TABLE style_bom_substitution_candidates
            ADD CONSTRAINT style_bom_subst_status_chk
                CHECK (status IN ('PROPOSED', 'WITHDRAWN')),
            ADD CONSTRAINT style_bom_subst_quality_chk
                CHECK (quality_approval_status = 'PENDING_WP11')");

        DB::statement("ALTER TABLE tech_pack_versions
            ADD CONSTRAINT tech_pack_versions_status_chk
                CHECK (status IN ('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'RELEASED', 'SUPERSEDED', 'REJECTED')),
            ADD CONSTRAINT tech_pack_versions_decision_chk
                CHECK (
                    (status IN ('DRAFT', 'PENDING_APPROVAL', 'RELEASE_BLOCKED') AND decided_by IS NULL AND decided_at IS NULL AND decision_reason IS NULL)
                    OR (status IN ('APPROVED', 'RELEASED', 'SUPERSEDED', 'REJECTED') AND decided_by IS NOT NULL AND decided_at IS NOT NULL AND decision_reason IS NOT NULL)
                ),
            ADD CONSTRAINT tech_pack_versions_release_chk
                CHECK (
                    (status = 'RELEASED' AND released_by IS NOT NULL AND released_at IS NOT NULL AND release_manifest_sha256 IS NOT NULL)
                    OR (status <> 'RELEASED' AND released_by IS NULL AND released_at IS NULL)
                )");

        DB::statement("ALTER TABLE tech_pack_assets
            ADD CONSTRAINT tech_pack_assets_type_chk
                CHECK (asset_type IN ('TECHNICAL_FLAT', 'PATTERN_FILE', 'CONSTRUCTION_DETAIL', 'CARE_LABEL_ARTWORK', 'ARTWORK_MASTER', 'VIRTUAL_FIT_REPORT', 'OTHER')),
            ADD CONSTRAINT tech_pack_assets_size_chk
                CHECK (size_bytes > 0),
            ADD CONSTRAINT tech_pack_assets_review_chk
                CHECK (
                    (review_status = 'PENDING_REVIEW' AND reviewed_by IS NULL AND reviewed_at IS NULL AND review_reason IS NULL)
                    OR (review_status IN ('VERIFIED', 'REJECTED') AND reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL AND review_reason IS NOT NULL)
                )");

        DB::statement("CREATE OR REPLACE FUNCTION guard_style_bom_version_state()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.status = 'DRAFT' AND NEW.status = 'PENDING_APPROVAL'
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.style_id IS NOT DISTINCT FROM OLD.style_id
                    AND NEW.style_revision_no IS NOT DISTINCT FROM OLD.style_revision_no
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.effective_from IS NOT DISTINCT FROM OLD.effective_from
                    AND NEW.effective_to IS NOT DISTINCT FROM OLD.effective_to
                    AND NEW.change_reason IS NOT DISTINCT FROM OLD.change_reason
                    AND NEW.material_resolution_status IS NOT DISTINCT FROM OLD.material_resolution_status
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at THEN
                    RETURN NEW;
                END IF;

                IF OLD.status = 'PENDING_APPROVAL' AND NEW.status IN ('APPROVED', 'REJECTED')
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.style_id IS NOT DISTINCT FROM OLD.style_id
                    AND NEW.style_revision_no IS NOT DISTINCT FROM OLD.style_revision_no
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.effective_from IS NOT DISTINCT FROM OLD.effective_from
                    AND NEW.effective_to IS NOT DISTINCT FROM OLD.effective_to
                    AND NEW.change_reason IS NOT DISTINCT FROM OLD.change_reason
                    AND NEW.material_resolution_status IS NOT DISTINCT FROM OLD.material_resolution_status
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at
                    AND NEW.submitted_by IS NOT DISTINCT FROM OLD.submitted_by
                    AND NEW.submitted_at IS NOT DISTINCT FROM OLD.submitted_at THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'BOM versions are immutable after submission; create a new version';
            END;
            $$");

        DB::statement('CREATE TRIGGER style_bom_version_state_guard
            BEFORE UPDATE ON style_bom_versions
            FOR EACH ROW EXECUTE FUNCTION guard_style_bom_version_state()');

        DB::statement("CREATE OR REPLACE FUNCTION guard_style_bom_lines_state()
            RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE
                parent_status text;
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN
                    SELECT status INTO parent_status FROM style_bom_versions
                    WHERE tenant_id = OLD.tenant_id AND id = OLD.bom_version_id;
                    IF parent_status IS DISTINCT FROM 'DRAFT' THEN
                        RAISE EXCEPTION 'BOM lines are immutable after their version is submitted';
                    END IF;
                END IF;

                IF TG_OP IN ('INSERT', 'UPDATE') THEN
                    SELECT status INTO parent_status FROM style_bom_versions
                    WHERE tenant_id = NEW.tenant_id AND id = NEW.bom_version_id;
                    IF parent_status IS DISTINCT FROM 'DRAFT' THEN
                        RAISE EXCEPTION 'BOM lines can only be changed while their parent version is a draft';
                    END IF;
                END IF;

                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;
                RETURN NEW;
            END;
            $$");

        DB::statement('CREATE TRIGGER style_bom_lines_state_guard
            BEFORE INSERT OR UPDATE OR DELETE ON style_bom_lines
            FOR EACH ROW EXECUTE FUNCTION guard_style_bom_lines_state()');

        DB::statement("CREATE OR REPLACE FUNCTION guard_tech_pack_version_state()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF OLD.status = 'DRAFT' AND NEW.status = 'PENDING_APPROVAL'
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.style_id IS NOT DISTINCT FROM OLD.style_id
                    AND NEW.style_revision_no IS NOT DISTINCT FROM OLD.style_revision_no
                    AND NEW.bom_version_id IS NOT DISTINCT FROM OLD.bom_version_id
                    AND NEW.measurement_spec_version_id IS NOT DISTINCT FROM OLD.measurement_spec_version_id
                    AND NEW.pattern_revision_id IS NOT DISTINCT FROM OLD.pattern_revision_id
                    AND NEW.sample_request_id IS NOT DISTINCT FROM OLD.sample_request_id
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.construction_specification IS NOT DISTINCT FROM OLD.construction_specification
                    AND NEW.seam_and_stitch_specification IS NOT DISTINCT FROM OLD.seam_and_stitch_specification
                    AND NEW.care_instructions IS NOT DISTINCT FROM OLD.care_instructions
                    AND NEW.label_specification IS NOT DISTINCT FROM OLD.label_specification
                    AND NEW.packaging_specification IS NOT DISTINCT FROM OLD.packaging_specification
                    AND NEW.quality_checkpoints IS NOT DISTINCT FROM OLD.quality_checkpoints
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at THEN
                    RETURN NEW;
                END IF;

                IF OLD.status = 'PENDING_APPROVAL' AND NEW.status IN ('APPROVED', 'REJECTED')
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.style_id IS NOT DISTINCT FROM OLD.style_id
                    AND NEW.style_revision_no IS NOT DISTINCT FROM OLD.style_revision_no
                    AND NEW.bom_version_id IS NOT DISTINCT FROM OLD.bom_version_id
                    AND NEW.measurement_spec_version_id IS NOT DISTINCT FROM OLD.measurement_spec_version_id
                    AND NEW.pattern_revision_id IS NOT DISTINCT FROM OLD.pattern_revision_id
                    AND NEW.sample_request_id IS NOT DISTINCT FROM OLD.sample_request_id
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.construction_specification IS NOT DISTINCT FROM OLD.construction_specification
                    AND NEW.seam_and_stitch_specification IS NOT DISTINCT FROM OLD.seam_and_stitch_specification
                    AND NEW.care_instructions IS NOT DISTINCT FROM OLD.care_instructions
                    AND NEW.label_specification IS NOT DISTINCT FROM OLD.label_specification
                    AND NEW.packaging_specification IS NOT DISTINCT FROM OLD.packaging_specification
                    AND NEW.quality_checkpoints IS NOT DISTINCT FROM OLD.quality_checkpoints
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at
                    AND NEW.submitted_by IS NOT DISTINCT FROM OLD.submitted_by
                    AND NEW.submitted_at IS NOT DISTINCT FROM OLD.submitted_at
                    AND NEW.asset_manifest_sha256 IS NOT DISTINCT FROM OLD.asset_manifest_sha256
                    THEN
                    RETURN NEW;
                END IF;

                IF OLD.status = 'APPROVED' AND NEW.status = 'RELEASED'
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.style_id IS NOT DISTINCT FROM OLD.style_id
                    AND NEW.style_revision_no IS NOT DISTINCT FROM OLD.style_revision_no
                    AND NEW.bom_version_id IS NOT DISTINCT FROM OLD.bom_version_id
                    AND NEW.measurement_spec_version_id IS NOT DISTINCT FROM OLD.measurement_spec_version_id
                    AND NEW.pattern_revision_id IS NOT DISTINCT FROM OLD.pattern_revision_id
                    AND NEW.sample_request_id IS NOT DISTINCT FROM OLD.sample_request_id
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.construction_specification IS NOT DISTINCT FROM OLD.construction_specification
                    AND NEW.seam_and_stitch_specification IS NOT DISTINCT FROM OLD.seam_and_stitch_specification
                    AND NEW.care_instructions IS NOT DISTINCT FROM OLD.care_instructions
                    AND NEW.label_specification IS NOT DISTINCT FROM OLD.label_specification
                    AND NEW.packaging_specification IS NOT DISTINCT FROM OLD.packaging_specification
                    AND NEW.quality_checkpoints IS NOT DISTINCT FROM OLD.quality_checkpoints
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at
                    AND NEW.submitted_by IS NOT DISTINCT FROM OLD.submitted_by
                    AND NEW.submitted_at IS NOT DISTINCT FROM OLD.submitted_at
                    AND NEW.decided_at IS NOT DISTINCT FROM OLD.decided_at
                    AND NEW.decision_reason IS NOT DISTINCT FROM OLD.decision_reason
                    AND NEW.asset_manifest_sha256 IS NOT DISTINCT FROM OLD.asset_manifest_sha256
                    AND NEW.release_manifest_sha256 IS NOT NULL
                    AND NEW.released_by IS NOT NULL
                    AND NEW.released_at IS NOT NULL THEN
                    RETURN NEW;
                END IF;

                IF OLD.status IN ('APPROVED', 'RELEASED') AND NEW.status = 'SUPERSEDED'
                    AND NEW.tenant_id IS NOT DISTINCT FROM OLD.tenant_id
                    AND NEW.style_id IS NOT DISTINCT FROM OLD.style_id
                    AND NEW.style_revision_no IS NOT DISTINCT FROM OLD.style_revision_no
                    AND NEW.bom_version_id IS NOT DISTINCT FROM OLD.bom_version_id
                    AND NEW.measurement_spec_version_id IS NOT DISTINCT FROM OLD.measurement_spec_version_id
                    AND NEW.pattern_revision_id IS NOT DISTINCT FROM OLD.pattern_revision_id
                    AND NEW.sample_request_id IS NOT DISTINCT FROM OLD.sample_request_id
                    AND NEW.version_no IS NOT DISTINCT FROM OLD.version_no
                    AND NEW.construction_specification IS NOT DISTINCT FROM OLD.construction_specification
                    AND NEW.seam_and_stitch_specification IS NOT DISTINCT FROM OLD.seam_and_stitch_specification
                    AND NEW.care_instructions IS NOT DISTINCT FROM OLD.care_instructions
                    AND NEW.label_specification IS NOT DISTINCT FROM OLD.label_specification
                    AND NEW.packaging_specification IS NOT DISTINCT FROM OLD.packaging_specification
                    AND NEW.quality_checkpoints IS NOT DISTINCT FROM OLD.quality_checkpoints
                    AND NEW.created_by IS NOT DISTINCT FROM OLD.created_by
                    AND NEW.created_at IS NOT DISTINCT FROM OLD.created_at THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'Tech-pack versions are immutable after submission; create a new version';
            END;
            $$");

        DB::statement('CREATE TRIGGER tech_pack_version_state_guard
            BEFORE UPDATE ON tech_pack_versions
            FOR EACH ROW EXECUTE FUNCTION guard_tech_pack_version_state()');

        DB::statement("CREATE OR REPLACE FUNCTION guard_tech_pack_assets_state()
            RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE
                parent_status text;
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN
                    SELECT status INTO parent_status FROM tech_pack_versions
                    WHERE tenant_id = OLD.tenant_id AND id = OLD.tech_pack_version_id;
                    IF parent_status IS DISTINCT FROM 'DRAFT' THEN
                        RAISE EXCEPTION 'Tech-pack assets are immutable after their parent version is submitted';
                    END IF;
                END IF;

                IF TG_OP IN ('INSERT', 'UPDATE') THEN
                    SELECT status INTO parent_status FROM tech_pack_versions
                    WHERE tenant_id = NEW.tenant_id AND id = NEW.tech_pack_version_id;
                    IF parent_status IS DISTINCT FROM 'DRAFT' THEN
                        RAISE EXCEPTION 'Tech-pack assets can only be added while the parent version is a draft';
                    END IF;
                END IF;

                IF TG_OP = 'DELETE' THEN
                    RETURN OLD;
                END IF;
                RETURN NEW;
            END;
            $$");

        DB::statement('CREATE TRIGGER tech_pack_assets_state_guard
            BEFORE INSERT OR UPDATE OR DELETE ON tech_pack_assets
            FOR EACH ROW EXECUTE FUNCTION guard_tech_pack_assets_state()');

        DB::statement("CREATE OR REPLACE FUNCTION guard_style_bom_substitution_state()
            RETURNS trigger LANGUAGE plpgsql AS $
            DECLARE
                parent_status text;
            BEGIN
                SELECT v.status INTO parent_status
                FROM style_bom_lines AS l
                JOIN style_bom_versions AS v
                  ON v.tenant_id = l.tenant_id AND v.id = l.bom_version_id
                WHERE l.tenant_id = NEW.tenant_id AND l.id = NEW.bom_line_id;

                IF parent_status IS DISTINCT FROM 'DRAFT' THEN
                    RAISE EXCEPTION 'Substitution candidates can only be proposed against a draft BOM';
                END IF;

                RETURN NEW;
            END;
            $$");

        DB::statement('CREATE TRIGGER style_bom_substitution_state_guard
            BEFORE INSERT ON style_bom_substitution_candidates
            FOR EACH ROW EXECUTE FUNCTION guard_style_bom_substitution_state()');

        DB::statement("CREATE OR REPLACE FUNCTION guard_tech_pack_delivery_receipt()
            RETURNS trigger LANGUAGE plpgsql AS $
            DECLARE
                parent_status text;
                parent_manifest char(64);
            BEGIN
                SELECT status, release_manifest_sha256
                    INTO parent_status, parent_manifest
                FROM tech_pack_versions
                WHERE tenant_id = NEW.tenant_id AND id = NEW.tech_pack_version_id;

                IF parent_status IS DISTINCT FROM 'RELEASED'
                    OR parent_manifest IS DISTINCT FROM NEW.manifest_sha256 THEN
                    RAISE EXCEPTION 'Delivery acknowledgements require the matching released tech-pack manifest';
                END IF;

                RETURN NEW;
            END;
            $$");

        DB::statement('CREATE TRIGGER tech_pack_receipt_release_guard
            BEFORE INSERT ON tech_pack_delivery_receipts
            FOR EACH ROW EXECUTE FUNCTION guard_tech_pack_delivery_receipt()');

        foreach ([
            'style_bom_versions',
            'style_bom_lines',
            'style_bom_substitution_candidates',
            'style_bom_decisions',
            'tech_pack_versions',
            'tech_pack_assets',
            'tech_pack_decisions',
            'tech_pack_delivery_receipts',
        ] as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        DB::statement('GRANT SELECT, INSERT ON style_bom_versions TO app_runtime');
        DB::statement('REVOKE DELETE ON style_bom_versions FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON style_bom_lines TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON style_bom_substitution_candidates TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON style_bom_decisions TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON tech_pack_versions TO app_runtime');
        DB::statement('REVOKE DELETE ON tech_pack_versions FROM app_runtime');
        DB::statement('GRANT SELECT, INSERT ON tech_pack_assets TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON tech_pack_decisions TO app_runtime');
        DB::statement('GRANT SELECT, INSERT ON tech_pack_delivery_receipts TO app_runtime');

        DB::statement('GRANT UPDATE (status, submitted_by, submitted_at, decided_by, decided_at, decision_reason, updated_at) ON style_bom_versions TO app_runtime');
        DB::statement('GRANT UPDATE (status, submitted_by, submitted_at, decided_by, decided_at, decision_reason, asset_manifest_sha256, released_by, released_at, release_manifest_sha256, updated_at) ON tech_pack_versions TO app_runtime');
        DB::statement('GRANT UPDATE (review_status, reviewed_by, reviewed_at, review_reason, updated_at) ON tech_pack_assets TO app_runtime');

        DB::statement('CREATE TRIGGER style_bom_versions_append_only BEFORE DELETE ON style_bom_versions FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('CREATE TRIGGER style_bom_subst_append_only BEFORE DELETE ON style_bom_substitution_candidates FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('CREATE TRIGGER style_bom_decisions_append_only BEFORE UPDATE OR DELETE ON style_bom_decisions FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('CREATE TRIGGER tech_pack_versions_append_only BEFORE DELETE ON tech_pack_versions FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('CREATE TRIGGER tech_pack_assets_append_only BEFORE DELETE ON tech_pack_assets FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('CREATE TRIGGER tech_pack_decisions_append_only BEFORE UPDATE OR DELETE ON tech_pack_decisions FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('CREATE TRIGGER tech_pack_receipts_append_only BEFORE UPDATE OR DELETE ON tech_pack_delivery_receipts FOR EACH ROW EXECUTE FUNCTION prevent_merchandising_history_mutation()');
        DB::statement('REVOKE UPDATE, DELETE ON style_bom_substitution_candidates FROM app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON style_bom_decisions FROM app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON tech_pack_decisions FROM app_runtime');
        DB::statement('REVOKE UPDATE, DELETE ON tech_pack_delivery_receipts FROM app_runtime');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_receipts_append_only ON tech_pack_delivery_receipts');
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_decisions_append_only ON tech_pack_decisions');
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_assets_append_only ON tech_pack_assets');
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_versions_append_only ON tech_pack_versions');
        DB::statement('DROP TRIGGER IF EXISTS style_bom_decisions_append_only ON style_bom_decisions');
        DB::statement('DROP TRIGGER IF EXISTS style_bom_subst_append_only ON style_bom_substitution_candidates');
        DB::statement('DROP TRIGGER IF EXISTS style_bom_versions_append_only ON style_bom_versions');
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_receipt_release_guard ON tech_pack_delivery_receipts');
        DB::statement('DROP TRIGGER IF EXISTS style_bom_substitution_state_guard ON style_bom_substitution_candidates');
        DB::statement('DROP FUNCTION IF EXISTS guard_tech_pack_delivery_receipt()');
        DB::statement('DROP FUNCTION IF EXISTS guard_style_bom_substitution_state()');
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_assets_state_guard ON tech_pack_assets');
        DB::statement('DROP TRIGGER IF EXISTS tech_pack_version_state_guard ON tech_pack_versions');
        DB::statement('DROP TRIGGER IF EXISTS style_bom_lines_state_guard ON style_bom_lines');
        DB::statement('DROP TRIGGER IF EXISTS style_bom_version_state_guard ON style_bom_versions');
        DB::statement('DROP FUNCTION IF EXISTS guard_tech_pack_assets_state()');
        DB::statement('DROP FUNCTION IF EXISTS guard_tech_pack_version_state()');
        DB::statement('DROP FUNCTION IF EXISTS guard_style_bom_lines_state()');
        DB::statement('DROP FUNCTION IF EXISTS guard_style_bom_version_state()');
        Schema::dropIfExists('tech_pack_delivery_receipts');
        Schema::dropIfExists('tech_pack_decisions');
        Schema::dropIfExists('tech_pack_assets');
        Schema::dropIfExists('tech_pack_versions');
        Schema::dropIfExists('style_bom_decisions');
        Schema::dropIfExists('style_bom_substitution_candidates');
        Schema::dropIfExists('style_bom_lines');
        Schema::dropIfExists('style_bom_versions');
        Schema::table('sample_requests', function (Blueprint $table): void {
            $table->dropUnique('sample_requests_techpack_fk_uq');
        });
        Schema::table('pattern_revisions', function (Blueprint $table): void {
            $table->dropUnique('pattern_revisions_techpack_fk_uq');
        });
        Schema::table('measurement_spec_versions', function (Blueprint $table): void {
            $table->dropUnique('measurement_spec_versions_techpack_fk_uq');
        });
    }
};
