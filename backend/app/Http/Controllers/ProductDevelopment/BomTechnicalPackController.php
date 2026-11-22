<?php

namespace App\Http\Controllers\ProductDevelopment;

use App\Http\Controllers\Controller;
use App\Services\ProductDevelopment\BomTechnicalPackService;
use App\Services\ScopedAccessService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BomTechnicalPackController extends Controller
{
    public function bomVersions(
        string $styleId,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $this->assertStyleScope($tenantId, $styleId, (string) request()->user()->getAuthIdentifier(), $scopedAccess, 'READ');

        return response()->json(['data' => $service->bomVersions($tenantId, $styleId)]);
    }

    public function createBomVersion(
        string $styleId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $this->assertStyleScope($tenantId, $styleId, $actorId, $scopedAccess, 'CREATE');

        $data = $request->validate([
            'style_revision_no' => ['required', 'integer', 'min:1'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        return response()->json([
            'data' => $service->createBomVersion($tenantId, $styleId, $actorId, $data),
        ], 201);
    }

    public function bomLines(
        string $bomVersionId,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        return response()->json([
            'data' => $service->bomLines($tenantContext->tenantId(), $bomVersionId),
        ]);
    }

    public function addBomLine(
        string $bomVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $data = $request->validate([
            'component_code' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/'],
            'component_type' => ['required', Rule::in([
                'FABRIC',
                'LINING',
                'INTERLINING',
                'TRIM',
                'THREAD',
                'LABEL',
                'PACKAGING',
                'CHEMICAL',
                'PROCESS_INPUT',
            ])],
            'description' => ['required', 'string', 'min:2', 'max:240'],
            'material_reference' => ['required', 'string', 'min:2', 'max:160'],
            'supplier_reference' => ['nullable', 'string', 'max:160'],
            'unit_code' => ['required', Rule::in(['M', 'CM', 'KG', 'G', 'PC', 'PAIR', 'SET', 'L', 'ML', 'M2', 'ROLL'])],
            'net_quantity_per_finished_unit' => ['required', 'numeric', 'gt:0', 'max:1000000000'],
            'wastage_percent' => ['required', 'numeric', 'between:0,100'],
            'wastage_basis_code' => ['required', Rule::in([
                'CUTTING_LOSS',
                'KNITTING_LOSS',
                'SPREADING_LOSS',
                'PRINTING_LOSS',
                'EMBROIDERY_LOSS',
                'SEWING_LOSS',
                'FINISHING_LOSS',
                'PACKAGING_LOSS',
                'PROCESS_LOSS',
            ])],
            'variant_applicability' => ['nullable', 'array', 'max:100'],
            'attachment_position' => ['nullable', 'string', 'max:160'],
            'is_mandatory' => ['sometimes', 'boolean'],
            'technical_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        return response()->json([
            'data' => $service->addBomLine(
                $tenantContext->tenantId(),
                $bomVersionId,
                (string) $request->user()->getAuthIdentifier(),
                $data,
            ),
        ], 201);
    }

    public function proposeSubstitution(
        string $bomLineId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $data = $request->validate([
            'alternative_component_reference' => ['required', 'string', 'min:2', 'max:160'],
            'technical_equivalence_basis' => ['required', 'string', 'min:20', 'max:5000'],
            'risk_and_limitations' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        return response()->json([
            'data' => $service->proposeSubstitution(
                $tenantContext->tenantId(),
                $bomLineId,
                (string) $request->user()->getAuthIdentifier(),
                $data,
            ),
        ], 201);
    }

    public function submitBom(
        string $bomVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        return response()->json([
            'data' => $service->submitBom(
                $tenantContext->tenantId(),
                $bomVersionId,
                (string) $request->user()->getAuthIdentifier(),
            ),
        ]);
    }

    public function decideBom(
        string $bomVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or administrator approval is required.');
        $data = $request->validate([
            'decision' => ['required', Rule::in(['APPROVE', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        return response()->json([
            'data' => $service->decideBom(
                $tenantContext->tenantId(),
                $bomVersionId,
                (string) $request->user()->getAuthIdentifier(),
                $data['decision'],
                trim($data['reason']),
            ),
        ]);
    }

    public function techPacks(
        string $styleId,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $this->assertStyleScope($tenantId, $styleId, (string) request()->user()->getAuthIdentifier(), $scopedAccess, 'READ');

        return response()->json(['data' => $service->techPacks($tenantId, $styleId)]);
    }

    public function createTechPack(
        string $styleId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $this->assertStyleScope($tenantId, $styleId, $actorId, $scopedAccess, 'CREATE');

        $data = $request->validate([
            'style_revision_no' => ['required', 'integer', 'min:1'],
            'bom_version_id' => ['required', 'uuid', Rule::exists('style_bom_versions', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'measurement_spec_version_id' => ['required', 'uuid', Rule::exists('measurement_spec_versions', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'pattern_revision_id' => ['required', 'uuid', Rule::exists('pattern_revisions', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'sample_request_id' => ['required', 'uuid', Rule::exists('sample_requests', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'construction_specification' => ['required', 'string', 'min:20', 'max:20000'],
            'seam_and_stitch_specification' => ['required', 'string', 'min:20', 'max:20000'],
            'care_instructions' => ['required', 'string', 'min:10', 'max:10000'],
            'label_specification' => ['required', 'string', 'min:10', 'max:10000'],
            'packaging_specification' => ['required', 'string', 'min:10', 'max:10000'],
            'quality_checkpoints' => ['required', 'array', 'min:1', 'max:100'],
            'quality_checkpoints.*' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        return response()->json([
            'data' => $service->createTechPack($tenantId, $styleId, $actorId, $data),
        ], 201);
    }

    public function addTechPackAsset(
        string $techPackVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        $data = $request->validate([
            'asset_type' => ['required', Rule::in([
                'TECHNICAL_FLAT',
                'PATTERN_FILE',
                'CONSTRUCTION_DETAIL',
                'CARE_LABEL_ARTWORK',
                'ARTWORK_MASTER',
                'VIRTUAL_FIT_REPORT',
                'OTHER',
            ])],
            'asset_reference' => ['required', 'string', 'min:5', 'max:500'],
            'file_name' => ['required', 'string', 'min:1', 'max:240'],
            'mime_type' => ['required', 'string', 'min:3', 'max:120'],
            'size_bytes' => ['required', 'integer', 'min:1', 'max:2000000000'],
            'sha256' => ['required', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
            'usage_rights_basis' => ['required', 'string', 'min:10', 'max:5000'],
            'rights_owner_reference' => ['required', 'string', 'min:2', 'max:160'],
            'rights_expiry' => ['nullable', 'date'],
        ]);

        return response()->json([
            'data' => $service->addTechPackAsset(
                $tenantContext->tenantId(),
                $techPackVersionId,
                (string) $request->user()->getAuthIdentifier(),
                $data,
            ),
        ], 201);
    }

    public function reviewTechPackAsset(
        string $assetId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or administrator review is required.');
        $data = $request->validate([
            'decision' => ['required', Rule::in(['VERIFY', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        return response()->json([
            'data' => $service->reviewTechPackAsset(
                $tenantContext->tenantId(),
                $assetId,
                (string) $request->user()->getAuthIdentifier(),
                $data['decision'],
                trim($data['reason']),
            ),
        ]);
    }

    public function submitTechPack(
        string $techPackVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        return response()->json([
            'data' => $service->submitTechPack(
                $tenantContext->tenantId(),
                $techPackVersionId,
                (string) $request->user()->getAuthIdentifier(),
            ),
        ]);
    }

    public function decideTechPack(
        string $techPackVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or administrator approval is required.');
        $data = $request->validate([
            'decision' => ['required', Rule::in(['APPROVE', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        return response()->json([
            'data' => $service->decideTechPack(
                $tenantContext->tenantId(),
                $techPackVersionId,
                (string) $request->user()->getAuthIdentifier(),
                $data['decision'],
                trim($data['reason']),
            ),
        ]);
    }

    public function releaseReadiness(
        string $techPackVersionId,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        return response()->json([
            'data' => $service->releaseReadiness($tenantContext->tenantId(), $techPackVersionId),
        ]);
    }

    public function release(
        string $techPackVersionId,
        Request $request,
        TenantContext $tenantContext,
        BomTechnicalPackService $service,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or administrator release approval is required.');
        $result = $service->release(
            $tenantContext->tenantId(),
            $techPackVersionId,
            (string) $request->user()->getAuthIdentifier(),
        );

        return response()->json(['data' => $result], $result['released'] ? 200 : 409);
    }

    private function assertStyleScope(
        string $tenantId,
        string $styleId,
        string $actorId,
        ScopedAccessService $scopedAccess,
        string $permission,
    ): void {
        $style = DB::table('styles')->where('tenant_id', $tenantId)->where('id', $styleId)->first();
        abort_if($style === null, 404);
        $scopedAccess->assertBrand($tenantId, $actorId, $style->brand_id, $permission);
    }
}
