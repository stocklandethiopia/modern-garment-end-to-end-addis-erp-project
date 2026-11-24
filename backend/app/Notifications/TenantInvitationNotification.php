<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $tenantName,
        public readonly string $inviterName,
        public readonly string $acceptanceUrl,
        public readonly string $expiresAt,
        public readonly string $assignedRole,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Invitation to join '.$this->tenantName)
            ->greeting('You have been invited to a garment ERP workspace')
            ->line($this->inviterName.' invited you to join '.$this->tenantName.'.')
            ->line('Your proposed workspace role is '.$this->assignedRole.'.')
            ->action('Review and accept invitation', $this->acceptanceUrl)
            ->line('This single-use invitation expires at '.$this->expiresAt.'.')
            ->line('If you were not expecting this invitation, you may ignore this email.');
    }
}
