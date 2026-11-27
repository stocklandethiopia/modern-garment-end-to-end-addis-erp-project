<?php

declare(strict_types=1);

namespace App\\Services;

use Illuminate\\Support\\Facades\\DB;

class ScopedAccessService
{
    public function assertBrand(string $tenantId, string $userId, string $brandId, string $permission): void
    {
        $this->assertScope($tenantId, $userId, 'BRAND', $brandId, null, $permission);
    }

    public function assertSite(string $tenantId, string $userId, string $siteId, string $permission): void
    {
        $this->assertScope($tenantId, $userId, 'SITE', $siteId, null, $permission);
    }

    public function assertMarket(string $tenantId, string $userId, string $market, string $permission): void
    {
        $this->assertScope($tenantId, $userId, 'MARKET', null, trim($market), $permission);
    }

    public function setMode(string $targetUserId, string $mode, string $reason): array
    {
        $result = DB::selectOne(
            'SELECT * FROM public.set_tenant_member_scope_mode(?::uuid, ?, ?)',
            [$targetUserId, $mode, trim($reason)],
        );

        if ($result === null) {
            throw new \\RuntimeException('The database did not return the updated tenant member scope mode.');
        }

        return [
            'user_id' => (string) $result->user_id,
            'scope_mode' => (string) $result->scope_mode,
        ];
    }

    public function grant(string $targetUserId, array $data): object
    {
        $scopeType = strtoupper(trim((string) $data['scope_type']));
        $scopeId = $scopeType === 'MARKET' ? null : ($data['scope_id'] ?? null);
        $scopeCode = $scopeType === 'MARKET'
            ? strtoupper(trim((string) ($data['scope_code'] ?? '')))
            : null;

        $result = DB::selectOne(
            'SELECT * FROM public.grant_tenant_access_scope(
                ?::uuid, ?, ?::uuid, ?, ?, ?::date, ?::date, ?
            )',
            [
                $targetUserId,
                $scopeType,
                $scopeId,
                $scopeCode,
                strtoupper(trim((string) $data['permission_code'])),
                $data['valid_from'] ?? now()->toDateString(),
                $data['valid_to'] ?? null,
                trim((string) $data['grant_reason']),
            ],
        );

        if ($result === null) {
            throw new \\RuntimeException('The database did not return the created tenant access scope.');
        }

        return $result;
    }

    public function revoke(string $scopeGrantId, string $reason): object
    {
        $result = DB::selectOne(
            'SELECT * FROM public.revoke_tenant_access_scope(?::uuid, ?)',
            [$scopeGrantId, trim($reason)],
        );

        if ($result === null) {
            throw new \\RuntimeException('The database did not return the revoked tenant access scope.');
        }

        return $result;
    }

    public function forMember(string $tenantId, string $targetUserId): array
    {
        $this->assertTenantMember($tenantId, $targetUserId);

        return [
            'user_id' => $targetUserId,
            'scope_mode' => $this->mode($tenantId, $targetUserId),
            'grants' => DB::table('tenant_access_scopes')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $targetUserId)
                ->orderByDesc('created_at')
                ->get(),
        ];
    }

    private function assertScope(
        string $tenantId,
        string $userId,
        string $scopeType,
        ?string $scopeId,
        ?string $scopeCode,
        string $permission,
    ): void {
        if ($this->mode($tenantId, $userId) !== 'SCOPED') {
            return;
        }

        $allowed = DB::table('tenant_access_scopes')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('scope_type', $scopeType)
            ->where('status', 'ACTIVE')
            ->whereIn('permission_code', [$permission, 'MANAGE'])
            ->whereDate('valid_from', '<=', now()->toDateString())
            ->where(function ($query): void {
                $query->whereNull('valid_to')->orWhereDate('valid_to', '>=', now()->toDateString());
            })
            ->when($scopeId !== null, fn ($query) => $query->where('scope_id', $scopeId))
            ->when($scopeCode !== null, fn ($query) => $query->whereRaw('UPPER(scope_code) = ?', [strtoupper($scopeCode)])
            )
            ->exists();

        abort_unless($allowed, 403, 'The authenticated tenant member does not have the required scope for this brand, site or market action.');
    }

    private function mode(string $tenantId, string $userId): string
    {
        return DB::table('tenant_access_scope_modes')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->value('scope_mode') ?? 'TENANT_WIDE';
    }

    private function assertTenantMember(string $tenantId, string $userId): void
    {
        $membership = DB::selectOne(
            'SELECT CASE WHEN public.is_active_member_of_current_tenant(?) THEN 1 ELSE 0 END AS active',
            [$userId],
        );

        abort_unless(
            $membership !== null && (int) $membership->active === 1,
            404,
            'Active tenant membership not found.',
        );
    }
}
