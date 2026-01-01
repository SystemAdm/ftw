<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires auth to view billing settings', function (): void {
    $this->get('/settings/billing')->assertRedirect('/login');

    $user = User::factory()->create();
    $this->actingAs($user);
    $this->get('/settings/billing')->assertSuccessful();
});

it('validates price is required when starting checkout', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/settings/billing/checkout', []);
    $response->assertSessionHasErrors(['price']);
});

it('requires auth for billing portal', function (): void {
    $this->post('/settings/billing/portal')->assertRedirect('/login');
});

it('rejects unsigned webhook calls', function (): void {
    // No signature header → should be rejected by Cashier controller
    $this->post('/stripe/webhook', [])
        ->assertForbidden();
});
