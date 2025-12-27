<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Stripe\Subscription;

uses(RefreshDatabase::class);

it('syncs subscription immediately on success redirect', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test']);

    // Mock Stripe Session
    $stripeMock = Mockery::mock(StripeClient::class);
    // Bind to container because Cashier::stripe() uses app(StripeClient::class)
    app()->bind(StripeClient::class, fn () => $stripeMock);

    $session = new Session('cs_test');
    $session->customer = 'cus_test';
    $session->subscription = 'sub_test';

    $stripeMock->checkout = (object) [
        'sessions' => Mockery::mock(),
    ];
    $stripeMock->checkout->sessions->shouldReceive('retrieve')
        ->with('cs_test')
        ->andReturn($session);

    $subscription = new Subscription('sub_test');
    $subscription->status = 'active';
    $subscription->customer = 'cus_test';
    $subscription->items = (object) ['data' => [
        (object) [
            'id' => 'si_test',
            'price' => (object) ['id' => 'price_test', 'product' => 'prod_test'],
            'quantity' => 1,
        ],
    ]];
    $subscription->current_period_end = now()->addMonth()->timestamp;
    $subscription->trial_end = null;
    $subscription->cancel_at_period_end = false;
    $subscription->metadata = [];

    $stripeMock->subscriptions = Mockery::mock();
    $stripeMock->subscriptions->shouldReceive('retrieve')
        ->with('sub_test')
        ->andReturn($subscription);

    $response = $this->actingAs($user)
        ->get(route('settings.billing.success', ['session_id' => 'cs_test']));

    $response->assertRedirect(route('settings.billing'));

    $user->refresh();

    expect($user->subscribed('default'))->toBeTrue();
});
