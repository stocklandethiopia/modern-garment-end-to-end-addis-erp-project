<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantInvitationLifecycleTest extends TestCase
{
    private string $tenantId;

    private string $ownerId;

    private string $adminId;

    private string $adminEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $db = DB::connection('pgsql_test_admin');
        $this->tenantId = (string) Str::uuid();
        $this->ownerId = (string) Str::uuid();
        $this->adminId = (string) Str::uuid();
        $this->adminEmail = 'invitation-admin-'.Str::lower(Str::random(8)).'@example.test';
        $now = now();

        $db->table('tenants')->insert([
            'id' => $this->tenantId,
            'code' => 'INVITE-'.Str::upper(Str::random(8)),
            'name' => 'Invitation Lifecycle Tenant',
            'status' => 'ACTIVE',
            'locale' => 'en',
            'timezone' => 'Africa/Addis_Ababa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            [$this->ownerId, 'invitation-owner-'.Str::lower(Str::random(8)).'@example.test', 'Invitation Owner'],
            [$this->adminId, $this->adminEmail, 'Invitation Admin'],
        ] as [$userId, $email, $name]) {
            $db->table('users')->insert([
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('LongTestPassword#2026'),
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([[$this->ownerId, 'OWNER'], [$this->adminId, 'ADMIN']] as [$userId, $role]) {
            $db->table('tenant_memberships')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenantId,
                'user_id' => $userId,
                'membership_role' => $role,
                'status' => 'ACTIVE',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function actAs(string $userId): void
    {
        Sanctum::actingAs(User::query()->findOrFail($userId), ['tenant:select', 'erp:access']);
        $this->withHeader('X-Tenant-Id', $this->tenantId);
    }

    private function issueInvitation(string $email, string $role = 'MEMBER'): array
    {
        Notification::fake();
        $this->actAs($this->ownerId);

        $response = $this->postJson('/api/v1/enterprise/invitations', [
            'email' => $email,
            'membership_role' => $role,
            'reason' => 'Invite the person to the selected customer workspace.',
        ])->assertAccepted();

        $invitationId = $response->json('data.id');
        $row = DB::connection('pgsql_test_admin')->table('tenant_invitations')->where('id', $invitationId)->first();

        return [$invitationId, $row];
    }

    private function sentToken(): string
    {
        $token = null;
        Notification::assertSentOnDemand(
            TenantInvitationNotification::class,
            function (TenantInvitationNotification $notification, array $channels, object $notifiable) use (&$token): bool {
                $query = [];
                $fragment = (string) parse_url($notification->acceptanceUrl, PHP_URL_FRAGMENT);
                parse_str($fragment, $fragmentParams);
                $token = $fragmentParams['token'] ?? null;

                return in_array('mail', $channels, true) && is_string($token) && strlen($token) === 64;
            },
        );

        $this->assertIsString($token);

        return $token;
    }

    private function registrationPayload(string $token, string $email): array
    {
        return [
            'token' => $token,
            'email' => $email,
            'name' => 'New Invitation User',
            'password' => 'LongInvitationPassword#2026',
            'password_confirmation' => 'LongInvitationPassword#2026',
        ];
    }

    public function test_owner_invitation_is_single_use_and_only_a_token_digest_is_stored(): void
    {
        $email = 'new-invitee-'.Str::lower(Str::random(8)).'@example.test';
        [$id, $row] = $this->issueInvitation($email);

        $this->assertSame('PENDING', $row->status);
        $this->assertSame('MEMBER', $row->membership_role);
        $this->assertSame(64, strlen($row->token_hash));
        $this->assertNotNull($row->sent_at);
        $this->assertNull($row->accepted_at);

        $token = $this->sentToken();
        $this->assertSame(hash('sha256', $token), $row->token_hash);
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'action' => 'TENANT_INVITATION_ISSUED',
            'object_id' => $id,
        ], 'pgsql_test_admin');
    }

    public function test_administrator_cannot_invite_an_administrator(): void
    {
        Notification::fake();
        $this->actAs($this->adminId);

        $this->postJson('/api/v1/enterprise/invitations', [
            'email' => 'proposed-admin@example.test',
            'membership_role' => 'ADMIN',
            'reason' => 'Attempt to invite another administrator.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('tenant_invitations', [
            'tenant_id' => $this->tenantId,
            'email_normalized' => 'proposed-admin@example.test',
        ], 'pgsql_test_admin');
    }

    public function test_new_invitee_registers_verified_identity_and_accepts_membership_once(): void
    {
        $email = 'register-invitee-'.Str::lower(Str::random(8)).'@example.test';
        [$id, $row] = $this->issueInvitation($email);
        $token = $this->sentToken();

        $response = $this->postJson('/api/v1/invitations/register', $this->registrationPayload($token, $email))
            ->assertCreated()
            ->assertJsonPath('data.status', 'ACCEPTED');

        $user = DB::connection('pgsql_test_admin')->table('users')->where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotSame('LongInvitationPassword#2026', $user->password);

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $this->tenantId,
            'user_id' => $user->id,
            'membership_role' => 'MEMBER',
            'status' => 'ACTIVE',
        ], 'pgsql_test_admin');
        $this->assertDatabaseHas('tenant_invitations', [
            'id' => $id,
            'status' => 'ACCEPTED',
            'accepted_by' => $user->id,
            'token_hash' => str_repeat('0', 64),
        ], 'pgsql_test_admin');
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'action' => 'TENANT_INVITATION_ACCEPTED',
            'object_id' => $id,
        ], 'pgsql_test_admin');

        $this->postJson('/api/v1/invitations/register', $this->registrationPayload($token, $email))
            ->assertUnprocessable();
    }

    public function test_existing_global_user_accepts_invitation_only_when_authenticated_as_invitee(): void
    {
        $existingUserId = (string) Str::uuid();
        $email = 'existing-invitee-'.Str::lower(Str::random(8)).'@example.test';
        DB::connection('pgsql_test_admin')->table('users')->insert([
            'id' => $existingUserId,
            'name' => 'Existing Invitee',
            'email' => $email,
            'password' => Hash::make('ExistingLongPassword#2026'),
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        [$id] = $this->issueInvitation($email);
        $token = $this->sentToken();

        $this->postJson('/api/v1/invitations/register', $this->registrationPayload($token, $email))
            ->assertUnprocessable();

        $login = $this->postJson('/api/v1/auth/tokens', [
            'email' => $email,
            'password' => 'ExistingLongPassword#2026',
            'device_name' => 'invitation-test',
        ])->assertOk();

        $this->withToken($login->json('access_token'))
            ->postJson('/api/v1/invitations/accept', ['token' => $token])
            ->assertOk()
            ->assertJsonPath('data.status', 'ACCEPTED');

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $this->tenantId,
            'user_id' => $existingUserId,
            'status' => 'ACTIVE',
        ], 'pgsql_test_admin');
        $this->assertDatabaseHas('tenant_invitations', [
            'id' => $id,
            'status' => 'ACCEPTED',
            'accepted_by' => $existingUserId,
        ], 'pgsql_test_admin');
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $email = 'expired-invitee-'.Str::lower(Str::random(8)).'@example.test';
        [$id] = $this->issueInvitation($email);
        $token = $this->sentToken();

        DB::connection('pgsql_test_admin')->table('tenant_invitations')
            ->where('id', $id)
            ->update(['expires_at' => now()->subMinute()]);

        $this->postJson('/api/v1/invitations/register', $this->registrationPayload($token, $email))
            ->assertUnprocessable();
    }

    public function test_resend_replaces_prior_token_and_uses_supplied_reason(): void
    {
        $email = 'resend-invitee-'.Str::lower(Str::random(8)).'@example.test';
        [$id, $initial] = $this->issueInvitation($email);
        $oldToken = $this->sentToken();

        $this->actAs($this->ownerId);
        $this->postJson('/api/v1/enterprise/invitations/'.$id.'/resend', [
            'reason' => 'Resend after the invitee reported a missing message.',
        ])->assertAccepted();

        $row = DB::connection('pgsql_test_admin')->table('tenant_invitations')->where('id', $id)->first();
        $newToken = $this->sentToken();

        $this->assertNotSame($initial->token_hash, $row->token_hash);
        $this->assertSame(hash('sha256', $newToken), $row->token_hash);
        $this->assertNotSame($oldToken, $newToken);
        $this->assertSame(2, $row->delivery_attempts);
        $this->postJson('/api/v1/invitations/register', $this->registrationPayload($oldToken, $email))
            ->assertUnprocessable();
        $this->postJson('/api/v1/invitations/register', $this->registrationPayload($newToken, $email))
            ->assertCreated();
    }

    public function test_revoked_invitation_cannot_be_accepted(): void
    {
        $email = 'revoked-invitee-'.Str::lower(Str::random(8)).'@example.test';
        [$id] = $this->issueInvitation($email);
        $token = $this->sentToken();

        $this->actAs($this->ownerId);
        $this->postJson('/api/v1/enterprise/invitations/'.$id.'/revoke', [
            'reason' => 'The hiring or contractor request was withdrawn.',
        ])->assertOk();

        $this->postJson('/api/v1/invitations/register', $this->registrationPayload($token, $email))
            ->assertUnprocessable();
    }

    public function test_app_runtime_cannot_write_invitation_rows_directly(): void
    {
        $runtime = DB::connection('pgsql');
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'tenant_invitations', 'INSERT')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'tenant_invitations', 'UPDATE')"));
        $this->assertFalse((bool) $runtime->scalar("SELECT has_table_privilege('app_runtime', 'tenant_invitations', 'DELETE')"));
        $this->assertTrue((bool) $runtime->scalar("SELECT has_function_privilege('app_runtime', 'public.create_tenant_invitation(text,text,text,timestamptz,text)', 'EXECUTE')"));
        $this->assertTrue((bool) $runtime->scalar("SELECT has_function_privilege('app_runtime', 'public.accept_tenant_invitation_for_new_user(text,text,uuid,text,text)', 'EXECUTE')"));
    }
}
