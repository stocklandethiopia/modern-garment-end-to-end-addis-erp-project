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
        'quality.release-material' => ['TENANT_OWNER','TENANT_ADMIN','QUALITY_HEAD','QUALITY_MANAGER','INVENTORY_MANAGER'],
        'manufacturing.manage-routes' => ['TENANT_OWNER','TENANT_ADMIN','INDUSTRIAL_ENGINEER','IE_MANAGER','PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.submit-route' => ['TENANT_OWNER','TENANT_ADMIN','INDUSTRIAL_ENGINEER','IE_MANAGER','PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.approve-route' => ['TENANT_OWNER','TENANT_ADMIN','IE_MANAGER','FACTORY_MANAGER','PRODUCT_ENGINEERING_MANAGER'],
        'manufacturing.create-production-order' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_PLANNER','PRODUCTION_MANAGER','FACTORY_MANAGER'],
        'manufacturing.release-production-order' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER'],
        'manufacturing.start-production-order' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER','PRODUCTION_SUPERVISOR'],
        'manufacturing.record-execution' => ['TENANT_OWNER','TENANT_ADMIN','PRODUCTION_MANAGER','FACTORY_MANAGER','PRODUCTION_SUPERVISOR','LINE_SUPERVISOR','FACTORY_OPERATOR'],
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
