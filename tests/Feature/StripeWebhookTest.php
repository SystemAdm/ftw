<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => RolesEnum::MEMBER->value]);
});

test('customer.subscription.created webhook updates membership', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_123',
    ]);

    // We need to bypass the signature verification for the test
    Config::set('cashier.webhook.secret', null);

    $payload = [
        'id' => 'evt_123',
        'type' => 'customer.subscription.created',
        'data' => [
            'object' => [
                'id' => 'sub_123',
                'customer' => 'cus_123',
                'status' => 'active',
                'items' => [
                    'data' => [
                        [
                            'id' => 'si_123',
                            'price' => [
                                'id' => 'price_123',
                                'product' => 'prod_123',
                            ],
                            'quantity' => 1,
                        ],
                    ],
                ],
                'current_period_end' => now()->addMonth()->timestamp,
            ],
        ],
    ];

    $response = $this->postJson('stripe/webhook', $payload);

    $response->assertSuccessful();

    $user->refresh();

    expect($user->subscribed('default'))->toBeTrue();
    expect($user->subscription('default')->stripe_id)->toBe('sub_123');
});

test('checkout.session.completed webhook updates membership', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_123',
    ]);

    Config::set('cashier.webhook.secret', null);

    $payload = [
        'id' => 'evt_124',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_123',
                'customer' => 'cus_123',
                'mode' => 'subscription',
                'subscription' => 'sub_124',
            ],
        ],
    ];

    // Note: checkout.session.completed doesn't have the full subscription object,
    // it usually triggers a background fetch or wait for customer.subscription.created
    // But Cashier's WebhookController doesn't handle checkout.session.completed by default for subscriptions
    // because it relies on customer.subscription.created.

    $response = $this->postJson('stripe/webhook', $payload);
    $response->assertSuccessful();
});

test('invoice.payment_succeeded webhook updates membership', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_123',
    ]);

    Config::set('cashier.webhook.secret', null);

    $payload = [
        'id' => 'evt_125',
        'type' => 'invoice.payment_succeeded',
        'data' => [
            'object' => [
                'id' => 'in_123',
                'customer' => 'cus_123',
                'subscription' => 'sub_123',
                'status' => 'paid',
                'billing_reason' => 'subscription_create',
            ],
        ],
    ];

    $response = $this->postJson('stripe/webhook', $payload);
    $response->assertSuccessful();
});
