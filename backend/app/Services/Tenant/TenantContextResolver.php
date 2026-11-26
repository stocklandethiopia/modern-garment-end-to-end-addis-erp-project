<?php

namespace App\Services\Tenant;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class TenantContextResolver
{
    public function requireTenantId(): string
    {
        $row = DB::selectOne("SELECT NULLIF(current_setting('app.current_tenant_id', true), '')::text AS tenant_id");
        $tenantId = $row?->tenant_id;

        if (! is_string($tenantId) || $tenantId === '') {
            throw new AuthorizationException('A validated tenant context is required for this operation.');
        }

        return $tenantId;
    }

    public function requireActorId(): string
    {
        $actorId = auth()->id();
        if (! is_string($actorId) && ! is_int($actorId)) {
            throw new AuthorizationException('An authenticated actor is required for this operation.');
        }

        return (string) $actorId;
    }
}
