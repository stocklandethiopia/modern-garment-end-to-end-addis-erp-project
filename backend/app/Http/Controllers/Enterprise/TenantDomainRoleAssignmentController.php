<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\Authorization\TenantDomainRoleAssignmentService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TenantDomainRoleAssignmentController extends Controller
{
    public function __construct(
        private readonly TenantDomainRoleAssignmentService $assignments,
        private readonly TenantContextResolver $tenant
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('tenant.assign-domain-roles');
        $data = $request->validate(['user_id' => ['nullable', 'uuid']]);

        return response()->json(['data' => $this->assignments->forTenant(
            $this->tenant->requireTenantId(), $data['user_id'] ?? null
        )]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('tenant.assign-domain-roles');
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
            'role_code' => ['required', 'in:PROCUREMENT_MANAGER,SOURCING_MANAGER,SUPPLIER_QUALITY_MANAGER,PROCUREMENT_OFFICER,PROCUREMENT_APPROVER,WAREHOUSE_MANAGER,WAREHOUSE_RECEIVER,INVENTORY_MANAGER,MATERIAL_CONTROLLER,INVENTORY_OPERATOR,QUALITY_INSPECTOR,QUALITY_MANAGER,QUALITY_HEAD,PROCESS_IMPROVEMENT_MANAGER,ASSIGNED_ACTION_OWNER,INDUSTRIAL_ENGINEER,IE_MANAGER,PRODUCT_ENGINEERING_MANAGER,FACTORY_MANAGER,PRODUCTION_PLANNER,PRODUCTION_MANAGER,PRODUCTION_SUPERVISOR,LINE_SUPERVISOR,FACTORY_OPERATOR,MAINTENANCE_MANAGER,SKILLS_ASSESSOR,TRAINING_MANAGER,HR_MANAGER,SALES_MANAGER,WHOLESALE_MANAGER,RETAIL_MANAGER,STORE_MANAGER,CREDIT_CONTROLLER,CREDIT_MANAGER,FINANCE_MANAGER,FINANCE_DIRECTOR,FINANCIAL_CONTROLLER,CHIEF_ACCOUNTANT,ACCOUNTANT,PAYROLL_MANAGER,HR_DIRECTOR,LEGAL_COUNSEL,DOCUMENT_CONTROLLER,DEPARTMENT_MANAGER,BUSINESS_USER,DOMAIN_APPROVER,DOMAIN_MANAGER,PROCESS_OWNER,ORDER_FULFILMENT_MANAGER,DISTRIBUTION_MANAGER,LOGISTICS_MANAGER,STORE_OPERATOR'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'grant_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        return response()->json(['data' => $this->assignments->create(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data
        )], 201);
    }

    public function approve(Request $request, string $assignmentId): JsonResponse
    {
        Gate::authorize('tenant.approve-domain-roles');
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);

        return response()->json(['data' => $this->assignments->approve(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $assignmentId, $data['reason']
        )]);
    }

    public function revoke(Request $request, string $assignmentId): JsonResponse
    {
        Gate::authorize('tenant.revoke-domain-roles');
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);

        return response()->json(['data' => $this->assignments->revoke(
            $this->tenant->requireTenantId(), $this->tenant->requireActorId(), $assignmentId, $data['reason']
        )]);
    }
}
