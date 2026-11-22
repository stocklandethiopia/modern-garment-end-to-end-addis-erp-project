<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function setMode(string $tenantId, string $targetUserId, string $actorId, string $mode, string $reason): array
    {
        $this->assertTenantMember($tenantId, $targetUserId);
        $existing = DB::table('tenant_access_scope_modes')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $targetUserId)
            ->lockForUpdate()
            ->first();

        $now = now();
        if ($existing === null) {
            $id = (string) Str::uuid();
            DB::table('tenant_access_scope_modes')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'user_id' => $targetUserId,
                'scope_mode' => $mode,
                'changed_by' => $actorId,
                'change_reason' => $reason,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('tenant_access_scope_modes')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $targetUserId)
                ->update([
                    'scope_mode' => $mode,
                    'changed_by' => $actorId,
                    'change_reason' => $reason,
                    'updated_at' => $now,
                ]);
        }

        $this->audit($tenantId, $actorId, 'TENANT_MEMBER_SCOPE_MODE_CHANGED', 'TENANT_MEMBER', $targetUserId, $reason);

        return [
            'user_id' => $targetUserId,
            'scope_mode' => $mode,
        ];
    }

    public function grant(
        string $tenantId,
        string $targetUserId,
        string $actorId,
        array $data,
    ): array {
        $this->assertTenantMember($tenantId, $targetUserId);
        $type = $data['scope_type'];
        $scopeId = $data['scope_id'] ?? null;
        $scopeCode = isset($data['scope_code']) ? strtoupper(trim($data['scope_code'])) : null;

        if ($type === 'BRAND') {
            abort_unless(
                DB::table('brands')->where('tenant_id', $tenantId)->where('id', $scopeId)->exists(),
                404,
                'Brand scope target does not exist in this tenant.',
            );
            $scopeCode = null;
        } elseif ($type === 'SITE') {
            abort_unless(
                DB::table('business_sites')->where('tenant_id', $tenantId)->where('id', $scopeId)->exists(),
                404,
                'Site scope target does not exist in this tenant.',
            );
            $scopeCode = null;
        } elseif ($type === 'MARKET') {
            abort_unless($scopeCode !== null && $scopeCode !== '', 422, 'Market scope code is required.');
            $scopeId = null;
        }

        $today = now()->toDateString();
        $validFrom = $data['valid_from'] ?? $today;
        $validTo = $data['valid_to'] ?? null;

        $duplicate = DB::table('tenant_access_scopes')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $targetUserId)
            ->where('scope_type', $type)
            ->where('permission_code', $data['permission_code'])
            ->where('status', 'ACTIVE')
            ->when($scopeId !== null, fn ($q) => $q->where('scope_id', $scopeId))
            ->when($scopeCode !== null, fn ($q) => $q->where('scope_code', $scopeCode))
            ->exists();

        abort_unless(! $duplicate, 422, 'An active grant for this user, scope and permission already exists.');

        $id = (string) Str::uuid();
        $now = now();
        DB::table('tenant_access_scopes')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'user_id' => $targetUserId,
            'scope_type' => $type,
            'scope_id' => $scopeId,
            'scope_code' => $scopeCode,
            'permission_code' => $data['permission_code'],
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'status' => 'ACTIVE',
            'grant_reason' => $data['grant_reason'],
            'granted_by' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->audit($tenantId, $actorId, 'TENANT_ACCESS_SCOPE_GRANTED', 'ACCESS_SCOPE', $id, $data['grant_reason']);

        return DB::table('tenant_access_scopes')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function revoke(
        string $tenantId,
        string $scopeGrantId,
        string $actorId,
        string $reason,
    ): object {
        $grant = DB::table('tenant_access_scopes')
            ->where('tenant_id', $tenantId)
            ->where('id', $scopeGrantId)
            ->lockForUpdate()
            ->first();

        abort_if($grant === null, 404);
        abort_unless($grant->status === 'ACTIVE', 422, 'Only an active access scope can be revoked.');

        DB::table('tenant_access_scopes')
            ->where('tenant_id', $tenantId)
            ->where('id', $scopeGrantId)
            ->update([
                'status' => 'REVOKED',
                'revoked_by' => $actorId,
                'revoked_at' => now(),
                'revocation_reason' => $reason,
                'updated_at' => now(),
            ]);

        $this->audit($tenantId, $actorId, 'TENANT_ACCESS_SCOPE_REVOKED', 'ACCESS_SCOPE', $scopeGrantId, $reason);

        return DB::table('tenant_access_scopes')->where('tenant_id', $tenantId)->where('id', $scopeGrantId)->first();
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
        abort_unless(
            DB::table('tenant_memberships')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('status', 'ACTIVE')
                ->exists(),
            404,
            'Active tenant membership not found.',
        );
    }

    private function audit(string $tenantId, string $actorId, string $action, string $objectType, string $objectId, string $reason): void
    {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'SCOPED_ACCESS'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
