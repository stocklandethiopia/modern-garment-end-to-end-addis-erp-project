<?php

namespace App\Support;

final class TenantContext
{
    private ?string $tenantId = null;

    private ?string $membershipRole = null;

    public function set(string $tenantId, string $membershipRole): void
    {
        $this->tenantId = $tenantId;
        $this->membershipRole = $membershipRole;
    }

    public function tenantId(): string
    {
        return $this->tenantId ?? throw new \LogicException('Tenant context has not been resolved.');
    }

    public function membershipRole(): string
    {
        return $this->membershipRole ?? throw new \LogicException('Tenant membership role has not been resolved.');
    }

    public function canAdministerTenant(): bool
    {
        return in_array($this->membershipRole, ['OWNER', 'ADMIN'], true);
    }

    public function clear(): void
    {
        $this->tenantId = null;
        $this->membershipRole = null;
    }
}
