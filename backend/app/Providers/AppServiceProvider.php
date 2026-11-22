<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(\App\Support\TenantContext::class);
    }

    public function boot(): void
    {
        Sanctum::ignoreMigrations();
    }
}
