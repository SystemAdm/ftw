<?php

use App\Enums\RolesEnum;
use App\Listeners\SyncMemberRole;
use App\Models\User;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create the member role if it doesn't exist
    Role::firstOrCreate(['name' => RolesEnum::MEMBER->value]);
});

it('assigns member role when subscription is created', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_test',
    ]);

    // Manually create a subscription for the user to simulate what Cashier does
    Subscription::create([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
    ]);

    $payload = [
        'type' => 'customer.subscription.created',
        'data' => [
            'object' => [
                'customer' => 'cus_test',
            ],
        ],
    ];

    $event = new WebhookHandled($payload);
    $listener = new SyncMemberRole;
    $listener->handle($event);

    expect($user->fresh()->hasRole(RolesEnum::MEMBER->value))->toBeTrue();
});

it('removes member role when subscription is deleted', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_test',
    ]);

    $user->assignRole(RolesEnum::MEMBER->value);
    expect($user->hasRole(RolesEnum::MEMBER->value))->toBeTrue();

    // No active subscription in database

    $payload = [
        'type' => 'customer.subscription.deleted',
        'data' => [
            'object' => [
                'customer' => 'cus_test',
            ],
        ],
    ];

    $event = new WebhookHandled($payload);
    $listener = new SyncMemberRole;
    $listener->handle($event);

    expect($user->fresh()->hasRole(RolesEnum::MEMBER->value))->toBeFalse();
});

it('syncs member role when subscription is updated', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_test',
    ]);

    // First, user is not a member
    expect($user->hasRole(RolesEnum::MEMBER->value))->toBeFalse();

    // Simulate subscription becoming active
    Subscription::create([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
    ]);

    $payload = [
        'type' => 'customer.subscription.updated',
        'data' => [
            'object' => [
                'customer' => 'cus_test',
            ],
        ],
    ];

    $event = new WebhookHandled($payload);
    $listener = new SyncMemberRole;
    $listener->handle($event);

    expect($user->fresh()->hasRole(RolesEnum::MEMBER->value))->toBeTrue();

    // Now simulate subscription becoming inactive (e.g. past_due or canceled)
    // In our implementation, subscribed() check usually depends on status.
    // Let's just delete the subscription to simulate "not subscribed"
    Subscription::where('user_id', $user->id)->delete();

    $event = new WebhookHandled($payload);
    $listener->handle($event);

    expect($user->fresh()->hasRole(RolesEnum::MEMBER->value))->toBeFalse();
});
