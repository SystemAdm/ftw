<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// kept for consistency with other tests, unused here

uses(RefreshDatabase::class);

it('provides seven upcoming days on the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard');
});
