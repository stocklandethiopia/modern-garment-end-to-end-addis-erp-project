<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    private string $tenantId;
    private string $userId;
    private string $email;
    private string $password = 'a-long-test-password';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = (string) Str::uuid();
        $this->userId = (string) Str::uuid();
        $this->email = 'access-owner-'.Str::lower(Str::random(10)).'@example.test';
        $now = now();
        $admin = DB::connection('pgsql_test_admin');

        $admin->table('tenants')->insert([
            'id' => $this->tenantId,
            'code' => 'TOKEN-'.Str::upper(Str::random(8)),
            'name' => 'Token Test Tenant',
            'status' => 'ACTIVE',
            'locale' => 'en',
            'timezone' => 'Africa/Addis_Ababa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('users')->insert([
            'id' => $this->userId,
            'name' => 'Token Test Owner',
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('tenant_memberships')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'membership_role' => 'OWNER',
            'status' => 'ACTIVE',
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function test_access_token_can_select_tenant_and_last_used_timestamp_is_updated(): void
    {
        $tokenResponse = $this->postJson('/api/v1/auth/tokens', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'feature-test',
        ]);

        $tokenResponse->assertOk()
            ->assertJsonPath('token_type', 'Bearer');

        $plainTextToken = $tokenResponse->json('access_token');
        $this->assertIsString($plainTextToken);

        $response = $this->withToken($plainTextToken)->getJson('/api/v1/tenants');

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->tenantId]);

        $storedToken = DB::connection('pgsql_test_admin')->table('personal_access_tokens')
            ->where('tokenable_id', $this->userId)
            ->first();

        $this->assertNotNull($storedToken);
        $this->assertNotNull($storedToken->last_used_at);
    }

    public function test_revoked_token_cannot_access_tenant_selector(): void
    {
        $plainTextToken = $this->postJson('/api/v1/auth/tokens', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'revoke-test',
        ])->assertOk()->json('access_token');

        $this->withToken($plainTextToken)->deleteJson('/api/v1/auth/tokens/current')
            ->assertOk()
            ->assertJson(['status' => 'revoked']);

        $this->withToken($plainTextToken)->getJson('/api/v1/tenants')
            ->assertUnauthorized();
    }
}
