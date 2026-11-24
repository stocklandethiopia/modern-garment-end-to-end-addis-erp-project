<?php

declare(strict_types=1);

namespace App\Services;

use App\Notifications\TenantInvitationNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class TenantInvitationService
{
    public function invitations(string $tenantId): object
    {
        return DB::table('tenant_invitations')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate(100, [
                'id', 'email_normalized', 'membership_role', 'status', 'invited_by', 'accepted_by',
                'reason', 'expires_at', 'sent_at', 'accepted_at', 'revoked_at',
                'delivery_attempts', 'last_delivery_error_code', 'created_at',
            ]);
    }

    public function invite(
        string $email,
        string $role,
        string $reason,
        string $tenantId,
        string $tenantName,
        string $inviterName,
        string $actorId,
    ): array {
        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addHours(72);
        $id = $this->callScalar(
            'SELECT public.create_tenant_invitation(?, ?, ?, ?::timestamptz, ?) AS invitation_id',
            [mb_strtolower(trim($email)), $role, hash('sha256', $token), $expiresAt->toIso8601String(), $reason],
        );

        $this->deliverAfterCommit($id, $email, $role, $tenantId, $tenantName, $inviterName, $actorId, $token, $expiresAt->toIso8601String());

        return ['id' => $id, 'status' => 'PENDING'];
    }

    public function resend(
        string $invitationId,
        string $email,
        string $role,
        string $tenantId,
        string $tenantName,
        string $inviterName,
        string $actorId,
        string $reason,
    ): array {
        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addHours(72);
        $this->callScalar(
            'SELECT public.rotate_tenant_invitation(?::uuid, ?, ?::timestamptz, ?) AS invitation_id',
            [$invitationId, hash('sha256', $token), $expiresAt->toIso8601String(), $reason],
        );

        $this->deliverAfterCommit($invitationId, $email, $role, $tenantId, $tenantName, $inviterName, $actorId, $token, $expiresAt->toIso8601String());

        return ['id' => $invitationId, 'status' => 'PENDING'];
    }

    public function revoke(string $invitationId, string $reason): void
    {
        $this->callVoid('SELECT public.revoke_tenant_invitation(?::uuid, ?)', [$invitationId, $reason]);
    }

    public function acceptForNewUser(string $token, string $email, string $name, string $passwordHash, string $userId): array
    {
        return $this->callJson(
            'SELECT public.accept_tenant_invitation_for_new_user(?, ?, ?::uuid, ?, ?) AS result',
            [hash('sha256', $token), mb_strtolower(trim($email)), $userId, trim($name), $passwordHash],
        );
    }

    public function acceptForExistingUser(string $token, string $userId): array
    {
        return $this->callJson(
            'SELECT public.accept_tenant_invitation_for_existing_user(?, ?::uuid) AS result',
            [hash('sha256', $token), $userId],
        );
    }

    private function deliverAfterCommit(
        string $id,
        string $email,
        string $role,
        string $tenantId,
        string $tenantName,
        string $inviterName,
        string $actorId,
        string $token,
        string $expiresAt,
    ): void {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        if ($frontendUrl === '') {
            throw new RuntimeException('FRONTEND_URL must be configured before tenant invitations can be issued.');
        }

        $url = $frontendUrl.'/accept-invitation?email='.rawurlencode(mb_strtolower(trim($email))).'#token='.rawurlencode($token);

        // Send only after the tenant request transaction commits. The callback
        // records SENT/DELIVERY_FAILED in a new short transaction; the raw token
        // exists only in the callback memory and the single email payload.
        DB::afterCommit(function () use ($id, $email, $role, $tenantId, $tenantName, $inviterName, $actorId, $url, $expiresAt): void {
            try {
                Notification::route('mail', $email)->notify(new TenantInvitationNotification(
                    tenantName: $tenantName,
                    inviterName: $inviterName,
                    acceptanceUrl: $url,
                    expiresAt: $expiresAt,
                    assignedRole: $role,
                ));

                $this->withInvitationContext($tenantId, $actorId, function () use ($id): void {
                    DB::select('SELECT public.mark_tenant_invitation_sent(?::uuid)', [$id]);
                });
            } catch (Throwable $exception) {
                $this->withInvitationContext($tenantId, $actorId, function () use ($id, $exception): void {
                    DB::select(
                        'SELECT public.mark_tenant_invitation_delivery_failed(?::uuid, ?)',
                        [$id, class_basename($exception)],
                    );
                });

                report($exception);
            }
        });
    }

    private function withInvitationContext(string $tenantId, string $actorId, callable $callback): void
    {
        DB::connection('pgsql')->transaction(function () use ($tenantId, $actorId, $callback): void {
            DB::select("select set_config('app.current_tenant_id', ?, true)", [$tenantId]);
            DB::select("select set_config('app.current_user_id', ?, true)", [$actorId]);
            $callback();
        });
    }

    private function callScalar(string $sql, array $bindings): string
    {
        try {
            return (string) DB::selectOne($sql, $bindings)->invitation_id;
        } catch (QueryException $exception) {
            $this->translate($exception);
        }
    }

    private function callVoid(string $sql, array $bindings): void
    {
        try {
            DB::select($sql, $bindings);
        } catch (QueryException $exception) {
            $this->translate($exception);
        }
    }

    private function callJson(string $sql, array $bindings): array
    {
        try {
            $result = DB::selectOne($sql, $bindings)->result;

            return is_string($result) ? (array) json_decode($result, true, 512, JSON_THROW_ON_ERROR) : (array) $result;
        } catch (QueryException $exception) {
            $this->translate($exception);
        }
    }

    private function translate(QueryException $exception): never
    {
        $state = $exception->errorInfo[0] ?? null;
        $message = $exception->getMessage();

        if ($state === '42501') {
            abort(403, $this->safeMessage($message));
        }
        if ($state === '23505') {
            throw ValidationException::withMessages(['email' => ['An active member or open invitation already exists for this email in this tenant.']]);
        }
        if ($state === 'P0002') {
            throw ValidationException::withMessages(['token' => ['Invitation is invalid, expired, already used or revoked.']]);
        }
        if ($state === 'P0003') {
            throw ValidationException::withMessages(['email' => ['An account already exists for this email. Sign in and accept the invitation instead.']]);
        }
        if ($state === '22023') {
            throw ValidationException::withMessages(['invitation' => [$this->safeMessage($message)]]);
        }

        throw $exception;
    }

    private function safeMessage(string $message): string
    {
        $allowed = [
            'Only the tenant owner may invite an administrator.',
            'Only the tenant owner may resend an administrator invitation.',
            'Only the tenant owner may revoke an administrator invitation.',
            'Tenant owner or administrator permission is required.',
            'A verified tenant and actor context is required.',
            'A valid invitation email is required.',
            'Only MEMBER or ADMIN invitations may be issued.',
        ];

        foreach ($allowed as $candidate) {
            if (str_contains($message, $candidate)) {
                return $candidate;
            }
        }

        return 'The invitation request could not be completed.';
    }
}
