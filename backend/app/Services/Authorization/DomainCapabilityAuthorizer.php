<?php

namespace App\Services\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class DomainCapabilityAuthorizer
{
    private const ROLE_MATRIX = [
        'sourcing.manage' => ['PROCUREMENT_MANAGER', 'SOURCING_MANAGER'],
        'sourcing.approve-supplier' => ['PROCUREMENT_MANAGER', 'SUPPLIER_QUALITY_MANAGER'],
        'sourcing.create-purchase-order' => ['PROCUREMENT_MANAGER', 'SOURCING_MANAGER', 'PROCUREMENT_OFFICER'],
        'sourcing.submit-purchase-order' => ['PROCUREMENT_MANAGER', 'SOURCING_MANAGER', 'PROCUREMENT_OFFICER'],
        'sourcing.approve-purchase-order' => ['PROCUREMENT_APPROVER', 'PROCUREMENT_MANAGER'],
        'sourcing.dispatch-purchase-order' => ['PROCUREMENT_MANAGER', 'SOURCING_MANAGER'],
        'inventory.receive-materials' => ['WAREHOUSE_MANAGER', 'WAREHOUSE_RECEIVER', 'INVENTORY_MANAGER'],
        'inventory.materialize-receipt' => ['WAREHOUSE_MANAGER', 'MATERIAL_CONTROLLER', 'QUALITY_MANAGER'],
        'quality.inspect' => ['QUALITY_INSPECTOR', 'QUALITY_MANAGER', 'QUALITY_HEAD'],
        'quality.record-inspection-result' => ['QUALITY_INSPECTOR', 'QUALITY_MANAGER', 'QUALITY_HEAD'],
        'quality.review-inspection' => ['QUALITY_MANAGER', 'QUALITY_HEAD'],
        'quality.manage-capa' => ['QUALITY_MANAGER', 'QUALITY_HEAD', 'PROCESS_IMPROVEMENT_MANAGER'],
        'quality.set-disposition' => ['QUALITY_MANAGER', 'QUALITY_HEAD', 'PRODUCTION_MANAGER'],
        'quality.implement-capa' => ['QUALITY_MANAGER', 'PROCESS_IMPROVEMENT_MANAGER', 'ASSIGNED_ACTION_OWNER'],
        'quality.verify-capa' => ['QUALITY_HEAD', 'QUALITY_MANAGER', 'PROCESS_IMPROVEMENT_MANAGER'],
        'quality.release-material' => ['QUALITY_HEAD', 'QUALITY_MANAGER'],
        'quality.release-production-operation' => ['QUALITY_HEAD', 'QUALITY_MANAGER'],
        'quality.release-finished-goods' => ['QUALITY_HEAD', 'QUALITY_MANAGER'],
        'manufacturing.manage-routes' => ['INDUSTRIAL_ENGINEER', 'IE_MANAGER', 'PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.submit-route' => ['INDUSTRIAL_ENGINEER', 'IE_MANAGER', 'PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.approve-route' => ['IE_MANAGER', 'FACTORY_MANAGER', 'PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.create-production-order' => ['PRODUCTION_PLANNER', 'PRODUCTION_MANAGER', 'FACTORY_MANAGER'],
        'manufacturing.release-production-order' => ['PRODUCTION_MANAGER', 'FACTORY_MANAGER'],
        'manufacturing.start-production-order' => ['PRODUCTION_MANAGER', 'FACTORY_MANAGER', 'PRODUCTION_SUPERVISOR'],
        'manufacturing.record-execution' => ['PRODUCTION_MANAGER', 'FACTORY_MANAGER', 'PRODUCTION_SUPERVISOR', 'LINE_SUPERVISOR', 'FACTORY_OPERATOR'],
        'planning.manage-capacity' => ['IE_MANAGER', 'FACTORY_MANAGER', 'PRODUCTION_MANAGER', 'MAINTENANCE_MANAGER'],
        'planning.manage-ie-standard' => ['INDUSTRIAL_ENGINEER', 'IE_MANAGER'],
        'planning.approve-ie-standard' => ['IE_MANAGER', 'FACTORY_MANAGER'],
        'planning.create-production-plan' => ['PRODUCTION_PLANNER', 'PRODUCTION_MANAGER', 'IE_MANAGER'],
        'planning.approve-production-plan' => ['PRODUCTION_MANAGER', 'FACTORY_MANAGER'],
        'workforce.assess-skills' => ['SKILLS_ASSESSOR', 'TRAINING_MANAGER', 'HR_MANAGER', 'PRODUCTION_MANAGER'],
        'workforce.assign-operations' => ['PRODUCTION_SUPERVISOR', 'LINE_SUPERVISOR', 'PRODUCTION_MANAGER'],
        'workforce.confirm-assignments' => ['PRODUCTION_MANAGER', 'FACTORY_MANAGER', 'HR_MANAGER'],
        'inventory.receive-finished-goods' => ['WAREHOUSE_MANAGER', 'WAREHOUSE_RECEIVER', 'PRODUCTION_MANAGER'],
        'commerce.create-customer-order' => ['SALES_MANAGER', 'WHOLESALE_MANAGER', 'RETAIL_MANAGER', 'STORE_MANAGER'],
        'commerce.confirm-customer-order' => ['SALES_MANAGER', 'CREDIT_CONTROLLER', 'FINANCE_MANAGER'],
        'commerce.review-credit' => ['CREDIT_CONTROLLER', 'FINANCE_MANAGER'],
        'commerce.approve-credit-exception' => ['CREDIT_MANAGER', 'FINANCE_DIRECTOR'],
        'inventory.reserve-finished-goods' => ['WAREHOUSE_MANAGER', 'ORDER_FULFILMENT_MANAGER', 'INVENTORY_MANAGER'],
        'distribution.dispatch-shipment' => ['DISTRIBUTION_MANAGER', 'WAREHOUSE_MANAGER', 'LOGISTICS_MANAGER'],
        'finance.create-journal' => ['FINANCE_MANAGER', 'ACCOUNTANT'],
        'finance.submit-journal' => ['FINANCE_MANAGER', 'ACCOUNTANT'],
        'finance.approve-journal' => ['FINANCE_MANAGER', 'FINANCIAL_CONTROLLER'],
        'finance.post-journal' => ['CHIEF_ACCOUNTANT', 'FINANCIAL_CONTROLLER'],
        'payroll.prepare-draft' => ['PAYROLL_MANAGER', 'HR_MANAGER'],
        'payroll.submit-for-review' => ['PAYROLL_MANAGER', 'HR_MANAGER'],
        'payroll.professional-approval' => ['PAYROLL_MANAGER', 'HR_DIRECTOR', 'FINANCE_DIRECTOR', 'LEGAL_COUNSEL'],
        'documents.register' => ['DOCUMENT_CONTROLLER', 'DEPARTMENT_MANAGER', 'BUSINESS_USER'],
        'documents.extract' => ['DOCUMENT_CONTROLLER', 'DEPARTMENT_MANAGER', 'BUSINESS_USER'],
        'documents.stage-extraction' => ['DOCUMENT_CONTROLLER', 'DEPARTMENT_MANAGER', 'BUSINESS_USER'],
        'documents.approve-import' => ['DOCUMENT_CONTROLLER', 'DOMAIN_APPROVER'],
        'devices.record-scan' => ['WAREHOUSE_RECEIVER', 'INVENTORY_OPERATOR', 'PRODUCTION_SUPERVISOR', 'FACTORY_OPERATOR', 'STORE_OPERATOR'],
        'devices.resolve-scan' => ['WAREHOUSE_RECEIVER', 'INVENTORY_OPERATOR', 'PRODUCTION_SUPERVISOR', 'FACTORY_OPERATOR', 'WAREHOUSE_MANAGER'],
        'agent.propose-action' => ['BUSINESS_USER', 'DOMAIN_MANAGER'],
        'agent.approve-action' => ['DOMAIN_APPROVER', 'PROCESS_OWNER'],
        'agent.execute-approved-draft' => ['DOMAIN_MANAGER', 'PROCESS_OWNER'],
    ];

    private const TENANT_ADMIN_ABILITIES = [
        'tenant.assign-domain-roles',
        'tenant.revoke-domain-roles',
    ];

    private const OWNER_ONLY_ABILITIES = [
        'tenant.approve-domain-roles',
    ];

    public function allows(?Authenticatable $user, string $ability): bool
    {
        if ($user === null) {
            return false;
        }

        $tenantId = DB::selectOne(
            "SELECT NULLIF(current_setting('app.current_tenant_id', true), '')::text AS tenant_id"
        )?->tenant_id;

        if (! is_string($tenantId) || $tenantId === '') {
            return false;
        }

        $userId = (string) $user->getAuthIdentifier();
        $membership = DB::table('tenant_memberships')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->first(['membership_role']);

        if ($membership === null) {
            return false;
        }

        $membershipRole = strtoupper((string) $membership->membership_role);
        if (in_array($ability, self::OWNER_ONLY_ABILITIES, true)) {
            return $membershipRole === 'OWNER';
        }

        if (in_array($ability, self::TENANT_ADMIN_ABILITIES, true)) {
            return in_array($membershipRole, ['OWNER', 'ADMIN'], true);
        }

        if (! isset(self::ROLE_MATRIX[$ability]) || $membershipRole === 'OWNER' || $membershipRole === 'ADMIN') {
            return false;
        }

        if ($membershipRole !== 'MEMBER') {
            return false;
        }

        // This initial role-assignment release is tenant-wide only. A member
        // placed in SCOPED mode is denied by these gates until the endpoint
        // also proves the exact site/brand/market scope for its target record.
        $scopeMode = DB::table('tenant_access_scope_modes')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->value('scope_mode') ?? 'TENANT_WIDE';

        if ($scopeMode !== 'TENANT_WIDE') {
            return false;
        }

        $roles = DB::table('tenant_domain_role_assignments')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->whereDate('valid_from', '<=', now()->toDateString())
            ->where(function ($query): void {
                $query->whereNull('valid_to')->orWhereDate('valid_to', '>=', now()->toDateString());
            })
            ->whereIn('role_code', self::ROLE_MATRIX[$ability])
            ->pluck('role_code')
            ->all();

        return $roles !== [];
    }
}
