<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuardianRelationConfirmation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\User $minor)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.guardian_confirmation.subject'))
            ->line(__('emails.guardian_confirmation.line1', ['name' => $this->minor->name]))
            ->line(__('emails.guardian_confirmation.line2'))
            ->action(__('emails.guardian_confirmation.action'), url('/settings/profile'))
            ->line(__('emails.guardian_confirmation.line3'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'minor_id' => $this->minor->id,
            'minor_name' => $this->minor->name,
            'message' => __('emails.guardian_confirmation.line1', ['name' => $this->minor->name]),
            'action_url' => '/settings/profile',
            'type' => 'guardian_confirmation',
        ];
    }
}
