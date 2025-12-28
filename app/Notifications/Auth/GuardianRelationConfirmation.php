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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Guardian Relation Confirmation')
            ->line($this->minor->name.' has registered and listed you as their guardian.')
            ->line('Please confirm this relationship by logging into your account.')
            ->action('Confirm Relation', url('/settings/profile'))
            ->line('Thank you for being part of our community!');
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
