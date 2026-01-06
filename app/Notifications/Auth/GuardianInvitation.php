<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuardianInvitation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\User $minor, public string $contact)
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.guardian_invitation.subject'))
            ->line(__('emails.guardian_invitation.line1', ['name' => $this->minor->name]))
            ->line(__('emails.guardian_invitation.line2'))
            ->action(__('emails.guardian_invitation.action'), url('/register'))
            ->line(__('emails.guardian_invitation.line3'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
