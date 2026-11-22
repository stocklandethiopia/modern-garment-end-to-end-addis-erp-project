<?php

namespace App\Services\ProductDevelopment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BomTechnicalPackService
{
    public function bomVersions(string $tenantId, string $styleId): mixed
    {
        $this->style($tenantId, $styleId);

        return DB::table('style_bom_versions')
            ->where('tenant_id', $tenantId)
            ->where('style_id', $styleId)
            ->orderByDesc('version_no')
            ->paginate(50);
    }

    public function createBomVersion(
        string $tenantId,
        string $styleId,
        string $actorId,
        array $data,
    ): object {
        return DB::transaction(function () use ($tenantId, $styleId, $actorId, $data): object {
            $style = $this->style($tenantId, $styleId, true);
            abort_unless(
                $style->owner_user_id === $actorId || $this->admin($tenantId, $actorId),
                403,
                'Only the style owner or a tenant administrator may create a BOM version.',
            );

            $revision = DB::table('style_revisions')
                ->where('tenant_id', $tenantId)
                ->where('style_id', $styleId)
                ->where('revision_no', $data['style_revision_no'])
                ->first();

            abort_if($revision === null, 404);
            $this->assertAcceptedBrief($tenantId, $style);

            DB::selectOne(
                'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$tenantId.':style-bom:'.$styleId.':'.$data['style_revision_no']],
            );

            $version = (int) DB::table('style_bom_versions')
                ->where('tenant_id', $tenantId)
                ->where('style_id', $styleId)
                ->where('style_revision_no', $data['style_revision_no'])
                ->max('version_no') + 1;

            $id = (string) Str::uuid();
            $now = now();

            DB::table('style_bom_versions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'style_revision_no' => $data['style_revision_no'],
                'version_no' => $version,
                'status' => 'DRAFT',
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'change_reason' => trim($data['change_reason']),
                'material_resolution_status' => 'UNVERIFIED_WP07',
                'created_by' => $actorId,
                'submitted_by' => null,
                'submitted_at' => null,
                'decided_by' => null,
                'decided_at' => null,
                'decision_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit($tenantId, $actorId, 'STYLE_BOM_DRAFT_CREATED', 'STYLE_BOM_VERSION', $id, $data['change_reason']);

            return $this->bom($tenantId, $id);
        });
    }

    public function bomLines(string $tenantId, string $bomVersionId): mixed
    {
        $this->bom($tenantId, $bomVersionId);

        return DB::table('style_bom_lines')
            ->where('tenant_id', $tenantId)
            ->where('bom_version_id', $bomVersionId)
            ->orderBy('line_no')
            ->get();
    }

    public function addBomLine(string $tenantId, string $bomVersionId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $bomVersionId, $actorId, $data): object {
            $bom = $this->bom($tenantId, $bomVersionId, true);
            abort_unless($bom->status === 'DRAFT', 422, 'BOM lines can only be edited in a draft version.');
            $style = $this->style($tenantId, $bom->style_id);
            abort_unless(
                $style->owner_user_id === $actorId || $this->admin($tenantId, $actorId),
                403,
                'Only the style owner or a tenant administrator may edit this BOM.',
            );

            $lineNo = (int) DB::table('style_bom_lines')
                ->where('tenant_id', $tenantId)
                ->where('bom_version_id', $bomVersionId)
                ->max('line_no') + 1;

            $id = (string) Str::uuid();
            $now = now();
            DB::table('style_bom_lines')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'bom_version_id' => $bomVersionId,
                'line_no' => $lineNo,
                'component_code' => strtoupper(trim($data['component_code'])),
                'component_type' => $data['component_type'],
                'description' => trim($data['description']),
                'material_reference' => trim($data['material_reference']),
                'supplier_reference' => isset($data['supplier_reference']) ? trim($data['supplier_reference']) : null,
                'unit_code' => $data['unit_code'],
                'net_quantity_per_finished_unit' => $data['net_quantity_per_finished_unit'],
                'wastage_percent' => $data['wastage_percent'],
                'wastage_basis_code' => $data['wastage_basis_code'],
                'variant_applicability' => json_encode($data['variant_applicability'] ?? [], JSON_THROW_ON_ERROR),
                'attachment_position' => isset($data['attachment_position']) ? trim($data['attachment_position']) : null,
                'is_mandatory' => $data['is_mandatory'] ?? true,
                'technical_notes' => isset($data['technical_notes']) ? trim($data['technical_notes']) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit(
                $tenantId,
                $actorId,
                'STYLE_BOM_LINE_ADDED',
                'STYLE_BOM_LINE',
                $id,
                'Technical component reference added to a draft BOM. Material master/availability remains unverified until WP07.',
            );

            return DB::table('style_bom_lines')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });
    }

    public function proposeSubstitution(
        string $tenantId,
        string $bomLineId,
        string $actorId,
        array $data,
    ): object {
        return DB::transaction(function () use ($tenantId, $bomLineId, $actorId, $data): object {
            $line = DB::table('style_bom_lines')
                ->where('tenant_id', $tenantId)
                ->where('id', $bomLineId)
                ->first();

            abort_if($line === null, 404);
            $bom = $this->bom($tenantId, $line->bom_version_id, true);
            abort_unless($bom->status === 'DRAFT', 422, 'Substitution candidates can only be proposed against a draft BOM.');
            $style = $this->style($tenantId, $bom->style_id);
            abort_unless(
                $style->owner_user_id === $actorId || $this->admin($tenantId, $actorId),
                403,
                'Only the style owner or a tenant administrator may propose a technical substitute.',
            );

            $id = (string) Str::uuid();
            $now = now();
            DB::table('style_bom_substitution_candidates')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'bom_line_id' => $bomLineId,
                'alternative_component_reference' => trim($data['alternative_component_reference']),
                'technical_equivalence_basis' => trim($data['technical_equivalence_basis']),
                'risk_and_limitations' => trim($data['risk_and_limitations']),
                'status' => 'PROPOSED',
                'quality_approval_status' => 'PENDING_WP11',
                'proposed_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit(
                $tenantId,
                $actorId,
                'STYLE_BOM_SUBSTITUTION_PROPOSED',
                'STYLE_BOM_SUBSTITUTION',
                $id,
                'Substitute proposed for later WP11 quality approval; it is not an approved material replacement.',
            );

            return DB::table('style_bom_substitution_candidates')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();
        });
    }

    public function submitBom(string $tenantId, string $bomVersionId, string $actorId): object
    {
        return DB::transaction(function () use ($tenantId, $bomVersionId, $actorId): object {
            $bom = $this->bom($tenantId, $bomVersionId, true);
            abort_unless($bom->status === 'DRAFT', 422, 'Only a draft BOM can be submitted.');
            $style = $this->style($tenantId, $bom->style_id);
            abort_unless(
                $style->owner_user_id === $actorId || $this->admin($tenantId, $actorId),
                403,
                'Only the style owner or a tenant administrator may submit this BOM.',
            );

            $mandatoryCount = DB::table('style_bom_lines')
                ->where('tenant_id', $tenantId)
                ->where('bom_version_id', $bomVersionId)
                ->where('is_mandatory', true)
                ->count();

            abort_unless($mandatoryCount > 0, 422, 'At least one mandatory BOM component is required.');
            abort_unless(
                DB::table('style_bom_versions')
                    ->where('tenant_id', $tenantId)
                    ->where('id', $bomVersionId)
                    ->where('material_resolution_status', 'UNVERIFIED_WP07')
                    ->exists(),
                422,
                'Material master resolution is owned by WP07.',
            );

            DB::table('style_bom_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $bomVersionId)
                ->update([
                    'status' => 'PENDING_APPROVAL',
                    'submitted_by' => $actorId,
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->audit($tenantId, $actorId, 'STYLE_BOM_SUBMITTED', 'STYLE_BOM_VERSION', $bomVersionId, 'BOM submitted for independent technical approval.');

            return $this->bom($tenantId, $bomVersionId);
        });
    }

    public function decideBom(
        string $tenantId,
        string $bomVersionId,
        string $actorId,
        string $decision,
        string $reason,
    ): object {
        return DB::transaction(function () use ($tenantId, $bomVersionId, $actorId, $decision, $reason): object {
            $bom = $this->bom($tenantId, $bomVersionId, true);
            abort_unless($bom->status === 'PENDING_APPROVAL', 422, 'BOM version is not awaiting a decision.');
            abort_unless($bom->created_by !== $actorId, 403, 'The BOM author cannot approve or reject their own version.');
            abort_unless($this->admin($tenantId, $actorId), 403, 'Tenant owner or administrator decision is required.');

            $status = $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED';
            DB::table('style_bom_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'bom_version_id' => $bomVersionId,
                'version_no' => $bom->version_no,
                'decision' => $decision,
                'reason' => $reason,
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            DB::table('style_bom_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $bomVersionId)
                ->update([
                    'status' => $status,
                    'decided_by' => $actorId,
                    'decided_at' => now(),
                    'decision_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $this->audit($tenantId, $actorId, 'STYLE_BOM_'.$status, 'STYLE_BOM_VERSION', $bomVersionId, $reason);

            return $this->bom($tenantId, $bomVersionId);
        });
    }

    public function techPacks(string $tenantId, string $styleId): mixed
    {
        $this->style($tenantId, $styleId);

        return DB::table('tech_pack_versions')
            ->where('tenant_id', $tenantId)
            ->where('style_id', $styleId)
            ->orderByDesc('version_no')
            ->paginate(50);
    }

    public function createTechPack(
        string $tenantId,
        string $styleId,
        string $actorId,
        array $data,
    ): object {
        return DB::transaction(function () use ($tenantId, $styleId, $actorId, $data): object {
            $style = $this->style($tenantId, $styleId, true);
            abort_unless(
                $style->owner_user_id === $actorId || $this->admin($tenantId, $actorId),
                403,
                'Only the style owner or a tenant administrator may create a technical pack.',
            );
            $this->assertAcceptedBrief($tenantId, $style);

            $bom = DB::table('style_bom_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['bom_version_id'])
                ->where('style_id', $styleId)
                ->where('style_revision_no', $data['style_revision_no'])
                ->where('status', 'APPROVED')
                ->first();

            abort_if($bom === null, 422, 'An independently approved BOM for the exact style revision is required.');

            $measurement = DB::table('measurement_spec_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['measurement_spec_version_id'])
                ->where('style_id', $styleId)
                ->where('style_revision_no', $data['style_revision_no'])
                ->where('status', 'APPROVED')
                ->first();

            abort_if($measurement === null, 422, 'An independently approved measurement specification for the exact style revision is required.');

            $pattern = DB::table('pattern_revisions')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['pattern_revision_id'])
                ->where('style_id', $styleId)
                ->where('style_revision_no', $data['style_revision_no'])
                ->where('measurement_spec_version_id', $data['measurement_spec_version_id'])
                ->where('status', 'APPROVED')
                ->first();

            abort_if($pattern === null, 422, 'An independently approved pattern revision linked to the measurement version is required.');

            $sample = DB::table('sample_requests')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['sample_request_id'])
                ->where('style_id', $styleId)
                ->where('style_revision_no', $data['style_revision_no'])
                ->where('pattern_revision_id', $data['pattern_revision_id'])
                ->where('measurement_spec_version_id', $data['measurement_spec_version_id'])
                ->where('status', 'FIT_APPROVED')
                ->first();

            abort_if($sample === null, 422, 'A sample request with approved fit for the exact pattern and measurement versions is required.');

            DB::selectOne(
                'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$tenantId.':tech-pack:'.$styleId.':'.$data['style_revision_no']],
            );

            $version = (int) DB::table('tech_pack_versions')
                ->where('tenant_id', $tenantId)
                ->where('style_id', $styleId)
                ->where('style_revision_no', $data['style_revision_no'])
                ->max('version_no') + 1;

            $id = (string) Str::uuid();
            $now = now();
            DB::table('tech_pack_versions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'style_revision_no' => $data['style_revision_no'],
                'bom_version_id' => $bom->id,
                'measurement_spec_version_id' => $measurement->id,
                'pattern_revision_id' => $pattern->id,
                'sample_request_id' => $sample->id,
                'version_no' => $version,
                'construction_specification' => trim($data['construction_specification']),
                'seam_and_stitch_specification' => trim($data['seam_and_stitch_specification']),
                'care_instructions' => trim($data['care_instructions']),
                'label_specification' => trim($data['label_specification']),
                'packaging_specification' => trim($data['packaging_specification']),
                'quality_checkpoints' => json_encode($data['quality_checkpoints'] ?? [], JSON_THROW_ON_ERROR),
                'status' => 'DRAFT',
                'asset_manifest_sha256' => null,
                'release_manifest_sha256' => null,
                'created_by' => $actorId,
                'submitted_by' => null,
                'submitted_at' => null,
                'decided_by' => null,
                'decided_at' => null,
                'decision_reason' => null,
                'released_by' => null,
                'released_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit($tenantId, $actorId, 'TECH_PACK_DRAFT_CREATED', 'TECH_PACK_VERSION', $id, 'Technical pack pinned to approved BOM, measurement, pattern and fit-approved sample versions.');

            return $this->techPack($tenantId, $id);
        });
    }

    public function addTechPackAsset(string $tenantId, string $techPackVersionId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $techPackVersionId, $actorId, $data): object {
            $pack = $this->techPack($tenantId, $techPackVersionId, true);
            abort_unless($pack->status === 'DRAFT', 422, 'Assets can only be attached to a draft technical pack.');
            abort_unless($pack->created_by === $actorId || $this->admin($tenantId, $actorId), 403, 'Only the pack author or a tenant administrator may attach assets.');

            $id = (string) Str::uuid();
            $now = now();
            DB::table('tech_pack_assets')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'tech_pack_version_id' => $techPackVersionId,
                'asset_type' => $data['asset_type'],
                'asset_reference' => trim($data['asset_reference']),
                'file_name' => trim($data['file_name']),
                'mime_type' => strtolower(trim($data['mime_type'])),
                'size_bytes' => $data['size_bytes'],
                'sha256' => strtolower(trim($data['sha256'])),
                'usage_rights_basis' => trim($data['usage_rights_basis']),
                'rights_owner_reference' => trim($data['rights_owner_reference']),
                'rights_expiry' => $data['rights_expiry'] ?? null,
                'review_status' => 'PENDING_REVIEW',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_reason' => null,
                'created_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->audit($tenantId, $actorId, 'TECH_PACK_ASSET_ATTACHED', 'TECH_PACK_ASSET', $id, 'Asset reference and declared rights metadata attached; object storage verification is not yet integrated.');

            return DB::table('tech_pack_assets')->where('tenant_id', $tenantId)->where('id', $id)->first();
        });
    }

    public function reviewTechPackAsset(
        string $tenantId,
        string $assetId,
        string $actorId,
        string $decision,
        string $reason,
    ): object {
        return DB::transaction(function () use ($tenantId, $assetId, $actorId, $decision, $reason): object {
            abort_unless($this->admin($tenantId, $actorId), 403, 'Tenant owner or administrator review is required.');
            $asset = DB::table('tech_pack_assets')
                ->where('tenant_id', $tenantId)
                ->where('id', $assetId)
                ->lockForUpdate()
                ->first();

            abort_if($asset === null, 404);
            abort_unless($asset->created_by !== $actorId, 403, 'The asset author cannot review their own rights declaration.');
            abort_unless($asset->review_status === 'PENDING_REVIEW', 422, 'Asset rights metadata has already been reviewed.');

            $pack = $this->techPack($tenantId, $asset->tech_pack_version_id);
            abort_unless($pack->status === 'DRAFT', 422, 'Assets must be reviewed while the parent technical pack is a draft.');
            if ($decision === 'VERIFY') {
                abort_if(
                    $asset->rights_expiry !== null && $asset->rights_expiry < now()->toDateString(),
                    422,
                    'The declared asset usage rights have expired.',
                );
            }

            DB::table('tech_pack_assets')
                ->where('tenant_id', $tenantId)
                ->where('id', $assetId)
                ->update([
                    'review_status' => $decision === 'VERIFY' ? 'VERIFIED' : 'REJECTED',
                    'reviewed_by' => $actorId,
                    'reviewed_at' => now(),
                    'review_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $this->audit(
                $tenantId,
                $actorId,
                'TECH_PACK_ASSET_'.$decision,
                'TECH_PACK_ASSET',
                $assetId,
                $reason,
            );

            return DB::table('tech_pack_assets')->where('tenant_id', $tenantId)->where('id', $assetId)->first();
        });
    }

    public function submitTechPack(string $tenantId, string $techPackVersionId, string $actorId): object
    {
        return DB::transaction(function () use ($tenantId, $techPackVersionId, $actorId): object {
            $pack = $this->techPack($tenantId, $techPackVersionId, true);
            abort_unless($pack->status === 'DRAFT', 422, 'Only a draft technical pack can be submitted.');
            abort_unless($pack->created_by === $actorId || $this->admin($tenantId, $actorId), 403, 'Only the technical pack author or a tenant administrator may submit it.');

            $assets = DB::table('tech_pack_assets')
                ->where('tenant_id', $tenantId)
                ->where('tech_pack_version_id', $techPackVersionId)
                ->orderBy('asset_type')
                ->orderBy('id')
                ->get();

            $requiredTypes = ['TECHNICAL_FLAT', 'PATTERN_FILE', 'CONSTRUCTION_DETAIL', 'CARE_LABEL_ARTWORK'];
            $presentTypes = $assets->where('review_status', 'VERIFIED')->pluck('asset_type')->unique()->all();
            $missing = array_values(array_diff($requiredTypes, $presentTypes));
            abort_unless($missing === [], 422, 'Missing independently verified required tech-pack assets: '.implode(', ', $missing).'.');

            $expired = $assets->first(fn (object $asset): bool => $asset->rights_expiry !== null && $asset->rights_expiry < now()->toDateString());
            abort_if($expired !== null, 422, 'A technical asset has expired usage rights.');

            $assetManifest = $assets->map(fn (object $asset): array => [
                'id' => $asset->id,
                'asset_type' => $asset->asset_type,
                'asset_reference' => $asset->asset_reference,
                'file_name' => $asset->file_name,
                'mime_type' => $asset->mime_type,
                'size_bytes' => (int) $asset->size_bytes,
                'sha256' => $asset->sha256,
                'usage_rights_basis' => $asset->usage_rights_basis,
                'rights_owner_reference' => $asset->rights_owner_reference,
                'rights_expiry' => $asset->rights_expiry,
                'review_status' => $asset->review_status,
            ])->all();

            $assetManifestHash = hash('sha256', json_encode($assetManifest, JSON_THROW_ON_ERROR));

            DB::table('tech_pack_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $techPackVersionId)
                ->update([
                    'status' => 'PENDING_APPROVAL',
                    'asset_manifest_sha256' => $assetManifestHash,
                    'submitted_by' => $actorId,
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->audit($tenantId, $actorId, 'TECH_PACK_SUBMITTED', 'TECH_PACK_VERSION', $techPackVersionId, 'Tech pack submitted with an immutable hash of manually reviewed asset references and rights metadata.');

            return $this->techPack($tenantId, $techPackVersionId);
        });
    }

    public function decideTechPack(
        string $tenantId,
        string $techPackVersionId,
        string $actorId,
        string $decision,
        string $reason,
    ): object {
        return DB::transaction(function () use ($tenantId, $techPackVersionId, $actorId, $decision, $reason): object {
            abort_unless($this->admin($tenantId, $actorId), 403, 'Tenant owner or administrator approval is required.');
            $pack = $this->techPack($tenantId, $techPackVersionId, true);
            abort_unless($pack->status === 'PENDING_APPROVAL', 422, 'Technical pack is not awaiting a decision.');
            abort_unless($pack->created_by !== $actorId, 403, 'The technical pack author cannot approve or reject their own version.');

            DB::table('tech_pack_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'tech_pack_version_id' => $techPackVersionId,
                'version_no' => $pack->version_no,
                'decision' => $decision,
                'reason' => $reason,
                'actions' => json_encode([], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            DB::table('tech_pack_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $techPackVersionId)
                ->update([
                    'status' => $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED',
                    'decided_by' => $actorId,
                    'decided_at' => now(),
                    'decision_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $this->audit($tenantId, $actorId, 'TECH_PACK_'.$decision, 'TECH_PACK_VERSION', $techPackVersionId, $reason);

            return $this->techPack($tenantId, $techPackVersionId);
        });
    }

    public function releaseReadiness(string $tenantId, string $techPackVersionId): array
    {
        $pack = $this->techPack($tenantId, $techPackVersionId);
        $bom = $this->bom($tenantId, $pack->bom_version_id);
        $assets = DB::table('tech_pack_assets')
            ->where('tenant_id', $tenantId)
            ->where('tech_pack_version_id', $techPackVersionId)
            ->get();

        $blockers = [];
        if (! in_array($pack->status, ['APPROVED', 'RELEASED'], true)) {
            $blockers[] = ['code' => 'TECH_PACK_NOT_APPROVED', 'dependency' => 'WP05', 'reason' => 'The technical pack must receive independent approval before release.'];
        }
        if ($bom->status !== 'APPROVED') {
            $blockers[] = ['code' => 'BOM_NOT_APPROVED', 'dependency' => 'WP05', 'reason' => 'The exact BOM version pinned to this pack is not approved.'];
        }
        if ($bom->material_resolution_status !== 'VERIFIED') {
            $blockers[] = ['code' => 'MATERIAL_MASTER_UNVERIFIED', 'dependency' => 'WP07', 'reason' => 'Material identities, supplier approvals, stock units and availability must be verified by the sourcing/material domain.'];
        }
        if ($bom->material_resolution_status === 'VERIFIED') {
            $unapprovedSubstitutions = DB::table('style_bom_substitution_candidates')
                ->where('tenant_id', $tenantId)
                ->whereIn('bom_line_id', DB::table('style_bom_lines')
                    ->select('id')
                    ->where('tenant_id', $tenantId)
                    ->where('bom_version_id', $bom->id))
                ->where('quality_approval_status', '<>', 'APPROVED')
                ->count();

            if ($unapprovedSubstitutions > 0) {
                $blockers[] = ['code' => 'SUBSTITUTIONS_NOT_QUALITY_APPROVED', 'dependency' => 'WP11', 'reason' => 'Every proposed BOM substitution requires quality approval before technical release.'];
            }
        }

        $requiredAssets = ['TECHNICAL_FLAT', 'PATTERN_FILE', 'CONSTRUCTION_DETAIL', 'CARE_LABEL_ARTWORK'];
        $verifiedTypes = $assets->where('review_status', 'VERIFIED')->pluck('asset_type')->unique()->all();
        if (array_diff($requiredAssets, $verifiedTypes) !== []) {
            $blockers[] = ['code' => 'TECH_PACK_ASSETS_UNVERIFIED', 'dependency' => 'WP05', 'reason' => 'Required technical assets and usage rights must be independently reviewed.'];
        }
        if ($pack->asset_manifest_sha256 === null) {
            $blockers[] = ['code' => 'ASSET_MANIFEST_MISSING', 'dependency' => 'WP05', 'reason' => 'No immutable asset manifest hash has been captured.'];
        }
        $blockers[] = ['code' => 'OBJECT_STORAGE_NOT_VERIFIED', 'dependency' => 'WP15', 'reason' => 'Asset metadata and declared rights are recorded, but private object storage, malware scan, retrieval verification and immutable object version are not integrated.'];
        $blockers[] = ['code' => 'INDUSTRIAL_ENGINEERING_NOT_VERIFIED', 'dependency' => 'WP06', 'reason' => 'Operation breakdown, SMV/allowed time, method and validated capacity have not been approved.'];
        $blockers[] = ['code' => 'ROUTE_AND_SOP_NOT_VERIFIED', 'dependency' => 'WP08', 'reason' => 'The approved manufacturing route and operator work instructions have not been linked.'];
        $blockers[] = ['code' => 'QUALITY_RELEASE_NOT_VERIFIED', 'dependency' => 'WP11', 'reason' => 'Quality control plan, critical-to-quality characteristics, compliance tests and release disposition have not been approved.'];
        $blockers[] = ['code' => 'PRODUCT_COSTING_NOT_VERIFIED', 'dependency' => 'WP13', 'reason' => 'Material rates, process costs, overhead allocation, FX and authorized cost roll-up have not been approved.'];

        return [
            'tech_pack_id' => $techPackVersionId,
            'status' => $pack->status,
            'bom_version_id' => $bom->id,
            'bom_material_resolution_status' => $bom->material_resolution_status,
            'asset_manifest_sha256' => $pack->asset_manifest_sha256,
            'release_ready' => $blockers === [],
            'blockers' => $blockers,
        ];
    }

    public function release(string $tenantId, string $techPackVersionId, string $actorId): array
    {
        return DB::transaction(function () use ($tenantId, $techPackVersionId, $actorId): array {
            abort_unless($this->admin($tenantId, $actorId), 403, 'Tenant owner or administrator release approval is required.');
            $pack = $this->techPack($tenantId, $techPackVersionId, true);
            $readiness = $this->releaseReadiness($tenantId, $techPackVersionId);

            if (! $readiness['release_ready']) {
                return [
                    'released' => false,
                    'readiness' => $readiness,
                ];
            }

            $releasePayload = [
                'tech_pack_id' => $pack->id,
                'version_no' => (int) $pack->version_no,
                'style_id' => $pack->style_id,
                'style_revision_no' => (int) $pack->style_revision_no,
                'bom_version_id' => $pack->bom_version_id,
                'measurement_spec_version_id' => $pack->measurement_spec_version_id,
                'pattern_revision_id' => $pack->pattern_revision_id,
                'sample_request_id' => $pack->sample_request_id,
                'asset_manifest_sha256' => $pack->asset_manifest_sha256,
                'released_by' => $actorId,
            ];
            $releaseManifest = hash('sha256', json_encode($releasePayload, JSON_THROW_ON_ERROR));

            DB::table('tech_pack_versions')
                ->where('tenant_id', $tenantId)
                ->where('id', $techPackVersionId)
                ->update([
                    'status' => 'RELEASED',
                    'released_by' => $actorId,
                    'released_at' => now(),
                    'release_manifest_sha256' => $releaseManifest,
                    'updated_at' => now(),
                ]);

            $this->audit($tenantId, $actorId, 'TECH_PACK_RELEASED', 'TECH_PACK_VERSION', $techPackVersionId, 'Immutable technical release manifest generated after all dependency gates passed.');

            return [
                'released' => true,
                'readiness' => $this->releaseReadiness($tenantId, $techPackVersionId),
                'release_manifest_sha256' => $releaseManifest,
            ];
        });
    }

    private function style(string $tenantId, string $styleId, bool $lock = false): ?object
    {
        $query = DB::table('styles')->where('tenant_id', $tenantId)->where('id', $styleId);
        if ($lock) {
            $query->lockForUpdate();
        }

        $record = $query->first();
        abort_if($record === null, 404);

        return $record;
    }

    private function bom(string $tenantId, string $bomVersionId, bool $lock = false): ?object
    {
        $query = DB::table('style_bom_versions')->where('tenant_id', $tenantId)->where('id', $bomVersionId);
        if ($lock) {
            $query->lockForUpdate();
        }

        $record = $query->first();
        abort_if($record === null, 404);

        return $record;
    }

    private function techPack(string $tenantId, string $id, bool $lock = false): ?object
    {
        $query = DB::table('tech_pack_versions')->where('tenant_id', $tenantId)->where('id', $id);
        if ($lock) {
            $query->lockForUpdate();
        }

        $record = $query->first();
        abort_if($record === null, 404);

        return $record;
    }

    private function assertAcceptedBrief(string $tenantId, object $style): void
    {
        $brief = DB::table('product_briefs')
            ->where('tenant_id', $tenantId)
            ->where('id', $style->product_brief_id)
            ->where('status', 'ACCEPTED')
            ->where('accepted_version', $style->product_brief_version)
            ->where('current_version', $style->product_brief_version)
            ->exists();

        abort_unless($brief, 422, 'The style must remain pinned to the accepted and current product brief version.');
    }

    private function admin(string $tenantId, string $actorId): bool
    {
        return DB::table('tenant_memberships')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $actorId)
            ->where('status', 'ACTIVE')
            ->whereIn('role', ['OWNER', 'ADMIN'])
            ->exists();
    }

    private function audit(string $tenantId, string $actorId, string $action, string $objectType, string $objectId, string $reason): void
    {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'PRODUCT_DEVELOPMENT'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
