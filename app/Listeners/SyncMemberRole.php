<?php

namespace App\Listeners;

use App\Models\User;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Events\WebhookReceived;

class SyncMemberRole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookHandled|WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;

        $relevantTypes = [
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ];

        if (in_array($type, $relevantTypes)) {
            $stripeId = $payload['data']['object']['customer'] ?? null;

            if ($stripeId) {
                $user = User::where('stripe_id', $stripeId)->first();

                if ($user) {
                    $user->syncMemberRole();
                }
            }
        }
    }
}
