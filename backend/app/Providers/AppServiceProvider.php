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
}
}
