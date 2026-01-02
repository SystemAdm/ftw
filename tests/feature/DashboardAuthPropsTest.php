<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;

it('includes authenticated user in Inertia shared props on dashboard', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    // Ensure Inertia version header matches the server-computed version
    Config::set('app.asset_url', 'http://static-assets.test/build');
    $version = hash('xxh128', config('app.asset_url'));

    $response = $this->actingAs($user)->get(route('dashboard'), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => $version,
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('auth.user.id', $user->id)
        ->where('auth.user.email', $user->email)
        ->where('auth.user.name', $user->name)
    );
})->skip('Pending: Align Inertia testing approach (first-visit HTML vs JSON) with project conventions before enabling.');
