<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\Enterprise\EnterpriseOrganizationService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnterpriseOrganizationController extends Controller
{
    private const UNIT_TYPES = [
        'CORPORATE',
        'DIVISION',
        'DEPARTMENT',
        'SECTION',
        'TEAM',
        'FACTORY',
        'DISTRIBUTION_CENTRE',
        'DEPOT',
        'BRANCH',
        'STORE',
        'OTHER',
    ];

    private const POSITION_CLASSES = [
        'LEADERSHIP',
        'MANAGEMENT',
        'PROFESSIONAL',
        'OPERATIONAL',
        'SUPPORT',
        'OTHER',
    ];

    public function units(
        Request $request,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'q' => ['sometimes', 'string', 'max:120'],
            'unit_type' => ['sometimes', Rule::in(self::UNIT_TYPES)],
            'status' => ['sometimes', Rule::in(['ACTIVE', 'INACTIVE'])],
            'legal_entity_id' => ['sometimes', 'uuid'],
            'business_site_id' => ['sometimes', 'uuid'],
            'parent_id' => ['sometimes', 'uuid'],
            'valid_on' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        return response()->json($organization->units($tenantId, $filters));
    }

    public function storeUnit(
        Request $request,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $this->assertTenantAdministrator($tenantContext);
        $tenantId = $tenantContext->tenantId();

        $data = $request->validate([
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('org_units', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'legal_entity_id' => [
                'nullable',
                'uuid',
                Rule::exists('legal_entities', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'business_site_id' => [
                'nullable',
                'uuid',
                Rule::exists('business_sites', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'unit_code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/D',
                Rule::unique('org_units', 'unit_code')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'unit_name' => ['required', 'string', 'max:180'],
            'unit_type' => ['required', Rule::in(self::UNIT_TYPES)],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'valid_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
        ]);

        $unit = $organization->createUnit(
            $tenantId,
            (string) $request->user()->getAuthIdentifier(),
            $data,
        );

        return response()->json(['data' => $unit], 201);
    }

    public function positions(
        Request $request,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'q' => ['sometimes', 'string', 'max:120'],
            'org_unit_id' => [
                'sometimes',
                'uuid',
                Rule::exists('org_units', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'status' => ['sometimes', Rule::in(['ACTIVE', 'INACTIVE'])],
            'position_class' => ['sometimes', Rule::in(self::POSITION_CLASSES)],
            'valid_on' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        return response()->json($organization->positions($tenantId, $filters));
    }

    public function storePosition(
        Request $request,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $this->assertTenantAdministrator($tenantContext);
        $tenantId = $tenantContext->tenantId();

        $data = $request->validate([
            'org_unit_id' => [
                'required',
                'uuid',
                Rule::exists('org_units', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'position_code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/D',
                Rule::unique('job_positions', 'position_code')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'title' => ['required', 'string', 'max:180'],
            'job_family' => ['nullable', 'string', 'max:100'],
            'grade' => ['nullable', 'string', 'max:80'],
            'position_class' => ['sometimes', Rule::in(self::POSITION_CLASSES)],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'valid_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            // A job slot is not an employee or login identity. Incumbency is
            // deliberately excluded until the authoritative people model exists.
            'person_id' => ['prohibited'],
            'employee_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'membership_id' => ['prohibited'],
        ]);

        $position = $organization->createPosition(
            $tenantId,
            (string) $request->user()->getAuthIdentifier(),
            $data,
        );

        return response()->json(['data' => $position], 201);
    }

    public function managerAssignments(
        Request $request,
        string $unitId,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        abort_unless(
            DB::table('org_units')
                ->where('tenant_id', $tenantContext->tenantId())
                ->where('id', $unitId)
                ->exists(),
            404,
            'Organizational unit not found.',
        );
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'q' => ['sometimes', 'string', 'max:120'],
            'valid_on' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        return response()->json($organization->managerAssignments($tenantContext->tenantId(), $unitId, $filters));
    }

    public function assignUnitManager(
        Request $request,
        string $unitId,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $this->assertTenantAdministrator($tenantContext);
        $tenantId = $tenantContext->tenantId();

        $data = $request->validate([
            'manager_position_id' => [
                'required',
                'uuid',
                Rule::exists('job_positions', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'valid_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $assignment = $organization->assignUnitManager(
            $tenantId,
            (string) $request->user()->getAuthIdentifier(),
            $unitId,
            $data,
        );

        return response()->json(['data' => $assignment], 201);
    }

    public function reportingRelations(
        Request $request,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'q' => ['sometimes', 'string', 'max:120'],
            'reporting_type' => ['sometimes', Rule::in(['SOLID_LINE', 'FUNCTIONAL'])],
            'position_id' => [
                'sometimes',
                'uuid',
                Rule::exists('job_positions', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'valid_on' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        return response()->json($organization->reportingRelations($tenantId, $filters));
    }

    public function storeReportingRelation(
        Request $request,
        TenantContext $tenantContext,
        EnterpriseOrganizationService $organization,
    ): JsonResponse {
        $this->assertTenantAdministrator($tenantContext);
        $tenantId = $tenantContext->tenantId();

        $data = $request->validate([
            'subject_position_id' => [
                'required',
                'uuid',
                Rule::exists('job_positions', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'manager_position_id' => [
                'required',
                'uuid',
                Rule::exists('job_positions', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId),
                ),
            ],
            'reporting_type' => ['required', Rule::in(['SOLID_LINE', 'FUNCTIONAL'])],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'valid_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $relation = $organization->createReportingRelation(
            $tenantId,
            (string) $request->user()->getAuthIdentifier(),
            $data,
        );

        return response()->json(['data' => $relation], 201);
    }

    private function assertTenantAdministrator(TenantContext $tenantContext): void
    {
        abort_unless(
            $tenantContext->canAdministerTenant(),
            403,
            'Tenant administrator permission is required to configure organization structure.',
        );
    }
}
