<?php

namespace App\Providers;



use App\Services\Authorization\DomainCapabilityAuthorizer;
use Illuminate\Support\Facades\Gate;
use App\Support\TenantContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
    }

    public function boot(): void
    {
        //
    
        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('sourcing.manage', fn ($user): bool => $domainAuthorizer->allows($user, 'sourcing.manage'));
        Gate::define('sourcing.approve-supplier', fn ($user): bool => $domainAuthorizer->allows($user, 'sourcing.approve-supplier'));
        Gate::define('sourcing.create-purchase-order', fn ($user): bool => $domainAuthorizer->allows($user, 'sourcing.create-purchase-order'));
        Gate::define('sourcing.submit-purchase-order', fn ($user): bool => $domainAuthorizer->allows($user, 'sourcing.submit-purchase-order'));
        Gate::define('sourcing.approve-purchase-order', fn ($user): bool => $domainAuthorizer->allows($user, 'sourcing.approve-purchase-order'));
        Gate::define('sourcing.dispatch-purchase-order', fn ($user): bool => $domainAuthorizer->allows($user, 'sourcing.dispatch-purchase-order'));
        Gate::define('inventory.receive-materials', fn ($user): bool => $domainAuthorizer->allows($user, 'inventory.receive-materials'));
        Gate::define('inventory.materialize-receipt', fn ($user): bool => $domainAuthorizer->allows($user, 'inventory.materialize-receipt'));
        Gate::define('quality.inspect', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.inspect'));
        Gate::define('quality.record-inspection-result', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.record-inspection-result'));
        Gate::define('quality.review-inspection', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.review-inspection'));
        Gate::define('quality.manage-capa', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.manage-capa'));
        Gate::define('quality.release-material', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.release-material'));
        Gate::define('manufacturing.manage-routes', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.manage-routes'));
        Gate::define('manufacturing.submit-route', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.submit-route'));
        Gate::define('manufacturing.approve-route', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.approve-route'));
        Gate::define('manufacturing.create-production-order', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.create-production-order'));
        Gate::define('manufacturing.release-production-order', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.release-production-order'));
        Gate::define('manufacturing.start-production-order', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.start-production-order'));
        Gate::define('manufacturing.record-execution', fn ($user): bool => $domainAuthorizer->allows($user, 'manufacturing.record-execution'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('inventory.receive-finished-goods', fn ($user): bool => $domainAuthorizer->allows($user, 'inventory.receive-finished-goods'));
        Gate::define('quality.release-finished-goods', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.release-finished-goods'));
        Gate::define('commerce.create-customer-order', fn ($user): bool => $domainAuthorizer->allows($user, 'commerce.create-customer-order'));
        Gate::define('commerce.confirm-customer-order', fn ($user): bool => $domainAuthorizer->allows($user, 'commerce.confirm-customer-order'));
        Gate::define('inventory.reserve-finished-goods', fn ($user): bool => $domainAuthorizer->allows($user, 'inventory.reserve-finished-goods'));
        Gate::define('distribution.dispatch-shipment', fn ($user): bool => $domainAuthorizer->allows($user, 'distribution.dispatch-shipment'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('finance.create-journal', fn ($user): bool => $domainAuthorizer->allows($user, 'finance.create-journal'));
        Gate::define('finance.submit-journal', fn ($user): bool => $domainAuthorizer->allows($user, 'finance.submit-journal'));
        Gate::define('finance.approve-journal', fn ($user): bool => $domainAuthorizer->allows($user, 'finance.approve-journal'));
        Gate::define('finance.post-journal', fn ($user): bool => $domainAuthorizer->allows($user, 'finance.post-journal'));
        Gate::define('payroll.prepare-draft', fn ($user): bool => $domainAuthorizer->allows($user, 'payroll.prepare-draft'));
        Gate::define('payroll.submit-for-review', fn ($user): bool => $domainAuthorizer->allows($user, 'payroll.submit-for-review'));
        Gate::define('payroll.professional-approval', fn ($user): bool => $domainAuthorizer->allows($user, 'payroll.professional-approval'));
        Gate::define('documents.register', fn ($user): bool => $domainAuthorizer->allows($user, 'documents.register'));
        Gate::define('documents.extract', fn ($user): bool => $domainAuthorizer->allows($user, 'documents.extract'));
        Gate::define('documents.stage-extraction', fn ($user): bool => $domainAuthorizer->allows($user, 'documents.stage-extraction'));
        Gate::define('documents.approve-import', fn ($user): bool => $domainAuthorizer->allows($user, 'documents.approve-import'));
        Gate::define('devices.record-scan', fn ($user): bool => $domainAuthorizer->allows($user, 'devices.record-scan'));
        Gate::define('agent.propose-action', fn ($user): bool => $domainAuthorizer->allows($user, 'agent.propose-action'));
        Gate::define('agent.approve-action', fn ($user): bool => $domainAuthorizer->allows($user, 'agent.approve-action'));
        Gate::define('agent.execute-approved-draft', fn ($user): bool => $domainAuthorizer->allows($user, 'agent.execute-approved-draft'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('quality.release-production-operation', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.release-production-operation'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('quality.set-disposition', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.set-disposition'));
        Gate::define('quality.implement-capa', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.implement-capa'));
        Gate::define('quality.verify-capa', fn ($user): bool => $domainAuthorizer->allows($user, 'quality.verify-capa'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('planning.manage-capacity', fn ($user): bool => $domainAuthorizer->allows($user, 'planning.manage-capacity'));
        Gate::define('planning.manage-ie-standard', fn ($user): bool => $domainAuthorizer->allows($user, 'planning.manage-ie-standard'));
        Gate::define('planning.approve-ie-standard', fn ($user): bool => $domainAuthorizer->allows($user, 'planning.approve-ie-standard'));
        Gate::define('planning.create-production-plan', fn ($user): bool => $domainAuthorizer->allows($user, 'planning.create-production-plan'));
        Gate::define('planning.approve-production-plan', fn ($user): bool => $domainAuthorizer->allows($user, 'planning.approve-production-plan'));
        Gate::define('workforce.assess-skills', fn ($user): bool => $domainAuthorizer->allows($user, 'workforce.assess-skills'));
        Gate::define('workforce.assign-operations', fn ($user): bool => $domainAuthorizer->allows($user, 'workforce.assign-operations'));
        Gate::define('workforce.confirm-assignments', fn ($user): bool => $domainAuthorizer->allows($user, 'workforce.confirm-assignments'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('commerce.review-credit', fn ($user): bool => $domainAuthorizer->allows($user, 'commerce.review-credit'));
        Gate::define('commerce.approve-credit-exception', fn ($user): bool => $domainAuthorizer->allows($user, 'commerce.approve-credit-exception'));

        $domainAuthorizer = app(DomainCapabilityAuthorizer::class);
        Gate::define('devices.resolve-scan', fn ($user): bool => $domainAuthorizer->allows($user, 'devices.resolve-scan'));
}
}
