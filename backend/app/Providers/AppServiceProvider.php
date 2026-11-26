<?php

namespace App\Providers;

use App\Services\Authorization\DomainCapabilityAuthorizer;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
    }

    public function boot(): void
    {
        $abilities = [
            'sourcing.manage',
            'sourcing.approve-supplier',
            'sourcing.create-purchase-order',
            'sourcing.submit-purchase-order',
            'sourcing.approve-purchase-order',
            'sourcing.dispatch-purchase-order',
            'inventory.receive-materials',
            'inventory.materialize-receipt',
            'quality.inspect',
            'quality.record-inspection-result',
            'quality.review-inspection',
            'quality.manage-capa',
            'quality.set-disposition',
            'quality.implement-capa',
            'quality.verify-capa',
            'quality.release-material',
            'quality.release-production-operation',
            'quality.release-finished-goods',
            'manufacturing.manage-routes',
            'manufacturing.submit-route',
            'manufacturing.approve-route',
            'manufacturing.create-production-order',
            'manufacturing.release-production-order',
            'manufacturing.start-production-order',
            'manufacturing.record-execution',
            'planning.manage-capacity',
            'planning.manage-ie-standard',
            'planning.approve-ie-standard',
            'planning.create-production-plan',
            'planning.approve-production-plan',
            'workforce.assess-skills',
            'workforce.assign-operations',
            'workforce.confirm-assignments',
            'inventory.receive-finished-goods',
            'commerce.create-customer-order',
            'commerce.confirm-customer-order',
            'commerce.review-credit',
            'commerce.approve-credit-exception',
            'inventory.reserve-finished-goods',
            'distribution.dispatch-shipment',
            'finance.create-journal',
            'finance.submit-journal',
            'finance.approve-journal',
            'finance.post-journal',
            'payroll.prepare-draft',
            'payroll.submit-for-review',
            'payroll.professional-approval',
            'documents.register',
            'documents.extract',
            'documents.stage-extraction',
            'documents.approve-import',
            'devices.record-scan',
            'devices.resolve-scan',
            'agent.propose-action',
            'agent.approve-action',
            'agent.execute-approved-draft',
            'tenant.assign-domain-roles',
            'tenant.approve-domain-roles',
            'tenant.revoke-domain-roles',
        ];

        foreach ($abilities as $ability) {
            Gate::define(
                $ability,
                static fn ($user): bool => app(DomainCapabilityAuthorizer::class)->allows($user, $ability),
            );
        }
    }
}
