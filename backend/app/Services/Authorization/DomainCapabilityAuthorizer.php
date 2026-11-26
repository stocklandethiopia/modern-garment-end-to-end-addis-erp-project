<?php

namespace App\Services\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DomainCapabilityAuthorizer
{
    private const ROLE_FIELD_CANDIDATES = ['role_code', 'tenant_role', 'membership_role', 'role', 'access_level'];
    private const STATUS_FIELD_CANDIDATES = ['status', 'membership_status', 'state'];

    private const ROLE_MATRIX = [
        'sourcing.manage' => ['TENANT_OWNER','TENANT_ADMIN','PROCUREMENT_MANAGER','SOURCING_MANAGER'],
        'sourcing.approve-supplier' => ['TENANT_OWNER','TENANT_ADMIN','PROCUREMENT_MANAGER','SUPPLIER_QUALITY_MANAGER'],
        'sourcing.create-purchase-order' => ['TENANT_OWNER','TENANT_ADMIN','PROCUREMENT_MANAGER','SOURCING_MANAGER','PROCUREMENT_OFFICER'],
        'sourcing.submit-purchase-order' => ['TENANT_OWNER','TENANT_ADMIN','PROCUREMENT_MANAGER','SOURCING_MANAGER','PROCUREMENT_OFFICER'],
        'sourcing.approve-purchase-order' => ['TENANT_OWNER','TENANT_ADMIN','PROCUREMENT_APPROVER','PROCUREMENT_MANAGER','FINANCE_MANAGER'],
        'sourcing.dispatch-purchase-order' => ['TENANT_OWNER','TENANT_ADMIN','PROCUREMENT_MANAGER','SOURCING_MANAGER'],
        'inventory.receive-materials' => ['TENANT_OWNER','TENANT_ADMIN','WAREHOUSE_MANAGER','WAREHOUSE_RECEIVER','INVENTORY_MANAGER'],
        'inventory.materialize-receipt' => ['TENANT_OWNER','TENANT_ADMIN','WAREHOUSE_MANAGER','MATERIAL_CONTROLLER','QUALITY_MANAGER'],
        'quality.inspect' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_INSPECTOR','QUALITY_MANAGER','QUALITY_HEAD'],
        'quality.record-inspection-result' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_INSPECTOR','QUALITY_MANAGER','QUALITY_HEAD'],
        'quality.review-inspection' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_MANAGER','QUALITY_HEAD'],
        'quality.manage-capa' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_MANAGER','QUALITY_HEAD','PROCESS_IMPROVEMENT_MANAGER'],
        'quality.set-disposition' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_MANAGER','QUALITY_HEAD','PRODUCTION_MANAGER'],
        'quality.implement-capa' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_MANAGER','PROCESS_IMPROVEMENT_MANAGER','ASSIGNED_ACTION_OWNER'],
        'quality.verify-capa' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_HEAD','QUALITY_MANAGER','PROCESS_IMPROVEMENT_MANAGER'],
        'quality.release-material' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_HEAD','QUALITY_MANAGER','INVENTORY_MANAGER'],
        'manufacturing.manage-routes' => ['TENANT_OWNER','TENANT_ADMIN','INDUSTRIAL_ENGINEER','IE_MANAGER','PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.submit-route' => ['TENANT_OWNER','TENANT_ADMIN','INDUSTRIAL_ENGINEER','IE_MANAGER','PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.approve-route' => ['TENANT_OWNER','TENANT_ADMIN','IE_MANAGER','FACTORY_MANAGER','PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.create-production-order' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_PLANNER','PRODUCTION_MANAGER','FACTORY_MANAGER'],
        'manufacturing.release-production-order' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER'],
        'manufacturing.start-production-order' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER','PRODUCTION_SUPERVISOR'],
        'manufacturing.record-execution' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER','PRODUCTION_SUPERVISOR','LINE_SUPERVISOR','FACTORY_OPERATOR'],
        'planning.manage-capacity' => ['TENANT_OWNER','TENANT_ADMIN','IE_MANAGER','FACTORY_MANAGER','PRODUCTION_MANAGER','MAINTENANCE_MANAGER'],,
        'planning.manage-ie-standard' => ['TENANT_OWNER','TENANT_ADMIN','INDUSTRIAL_ENGINEER','IE_MANAGER'],,
        'planning.approve-ie-standard' => ['TENANT_OWNER','TENANT_ADMIN','IE_MANAGER','FACTORY_MANAGER'],,
        'planning.create-production-plan' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_PLANNER','PRODUCTION_MANAGER','IE_MANAGER'],,
        'planning.approve-production-plan' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER'],,
        'workforce.assess-skills' => ['TENANT_OWNER','TENANT_ADMIN','SKILLS_ASSESSOR','TRAINING_MANAGER','HR_MANAGER','PRODUCTION_MANAGER'],,
        'workforce.assign-operations' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_SUPERVISOR','LINE_SUPERVISOR','PRODUCTION_MANAGER'],,
        'workforce.confirm-assignments' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER','HR_MANAGER'],
        'inventory.receive-finished-goods' => ['TENANT_OWNER','TENANT_ADMIN','WAREHOUSE_MANAGER','WAREHOUSE_RECEIVER','PRODUCTION_MANAGER'],
        'quality.release-finished-goods' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_HEAD','QUALITY_MANAGER'],
        'commerce.create-customer-order' => ['TENANT_OWNER','TENANT_ADMIN','SALES_MANAGER','WHOLESALE_MANAGER','RETAIL_MANAGER','STORE_MANAGER'],
        'commerce.confirm-customer-order' => ['TENANT_OWNER','TENANT_ADMIN','SALES_MANAGER','CREDIT_CONTROLLER','FINANCE_MANAGER'],
        'inventory.reserve-finished-goods' => ['TENANT_OWNER','TENANT_ADMIN','WAREHOUSE_MANAGER','ORDER_FULFILMENT_MANAGER','INVENTORY_MANAGER'],
        'distribution.dispatch-shipment' => ['TENANT_OWNER','TENANT_ADMIN','DISTRIBUTION_MANAGER','WAREHOUSE_MANAGER','LOGISTICS_MANAGER'],
        'finance.create-journal' => ['TENANT_OWNER','TENANT_ADMIN','FINANCE_MANAGER','ACCOUNTANT'],
        'finance.submit-journal' => ['TENANT_OWNER','TENANT_ADMIN','FINANCE_MANAGER','ACCOUNTANT'],
        'finance.approve-journal' => ['TENANT_OWNER','TENANT_ADMIN','FINANCE_MANAGER','FINANCIAL_CONTROLLER'],
        'finance.post-journal' => ['TENANT_OWNER','TENANT_ADMIN','CHIEF_ACCOUNTANT','FINANCIAL_CONTROLLER'],
        'payroll.prepare-draft' => ['TENANT_OWNER','TENANT_ADMIN','PAYROLL_MANAGER','HR_MANAGER'],
        'payroll.submit-for-review' => ['TENANT_OWNER','TENANT_ADMIN','PAYROLL_MANAGER','HR_MANAGER'],
        'payroll.professional-approval' => ['TENANT_OWNER','TENANT_ADMIN','PAYROLL_MANAGER','HR_DIRECTOR','FINANCE_DIRECTOR','LEGAL_COUNSEL'],
        'documents.register' => ['TENANT_OWNER','TENANT_ADMIN','DOCUMENT_CONTROLLER','DEPARTMENT_MANAGER','BUSINESS_USER'],
        'documents.extract' => ['TENANT_OWNER','TENANT_ADMIN','DOCUMENT_CONTROLLER','DEPARTMENT_MANAGER','BUSINESS_USER'],
        'documents.stage-extraction' => ['TENANT_OWNER','TENANT_ADMIN','DOCUMENT_CONTROLLER','DEPARTMENT_MANAGER','BUSINESS_USER'],
        'documents.approve-import' => ['TENANT_OWNER','TENANT_ADMIN','DOCUMENT_CONTROLLER','DOMAIN_APPROVER'],
        'devices.record-scan' => ['TENANT_OWNER','TENANT_ADMIN','WAREHOUSE_RECEIVER','INVENTORY_OPERATOR','PRODUCTION_SUPERVISOR','FACTORY_OPERATOR','STORE_OPERATOR'],
        'agent.propose-action' => ['TENANT_OWNER','TENANT_ADMIN','BUSINESS_USER','DOMAIN_MANAGER'],
        'agent.approve-action' => ['TENANT_OWNER','TENANT_ADMIN','DOMAIN_APPROVER','PROCESS_OWNER'],
        'agent.execute-approved-draft' => ['TENANT_OWNER','TENANT_ADMIN','DOMAIN_MANAGER','PROCESS_OWNER'],
    ];

    public function allows(?Authenticatable $user, string $ability): bool
    {
        if ($user === null || ! isset(self::ROLE_MATRIX[$ability])) {
            return false;
        }

        $tenantId = DB::selectOne("SELECT NULLIF(current_setting('app.current_tenant_id', true), '')::text AS tenant_id")?->tenant_id;
        if (! is_string($tenantId) || $tenantId === '' || ! isset($user->id)) {
            return false;
        }

        foreach (['tenant_memberships', 'tenant_user_memberships', 'tenant_users', 'tenant_members'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $tenantColumn = in_array('tenant_id', $columns, true) ? 'tenant_id' : null;
            $userColumn = collect(['user_id', 'member_user_id', 'account_user_id'])->first(fn (string $column): bool => in_array($column, $columns, true));

            if ($tenantColumn === null || $userColumn === null) {
                continue;
            }

            $membership = DB::table($table)
                ->where($tenantColumn, $tenantId)
                ->where($userColumn, (string) $user->id)
                ->first();

            if ($membership === null) {
                continue;
            }

            foreach (self::STATUS_FIELD_CANDIDATES as $statusColumn) {
                if (in_array($statusColumn, $columns, true)) {
                    $status = strtoupper((string) ($membership->{$statusColumn} ?? ''));
                    if (! in_array($status, ['ACTIVE','ACCEPTED','APPROVED','ENABLED'], true)) {
                        return false;
                    }
                    break;
                }
            }

            foreach (self::ROLE_FIELD_CANDIDATES as $roleColumn) {
                if (! in_array($roleColumn, $columns, true)) {
                    continue;
                }

                $rawRole = $membership->{$roleColumn} ?? null;
                $roles = is_array($rawRole)
                    ? $rawRole
                    : (is_string($rawRole) && str_starts_with(trim($rawRole), '[')
                        ? (json_decode($rawRole, true) ?: [])
                        : [$rawRole]);

                $normalised = array_map(static fn ($role): string => strtoupper(trim((string) $role)), $roles);
                return count(array_intersect(self::ROLE_MATRIX[$ability], $normalised)) > 0;
            }
        }

        return false;
    }
}
