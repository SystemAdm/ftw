<?php

use App\Models\PostalCode;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('settings.profile'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    $postalCode = PostalCode::factory()->create(['postal_code' => 1234]);

    $response = $this
        ->actingAs($user)
        ->patch(route('settings.profile.update'), [
            'birthday' => '1990-01-01',
            'postal_code' => $postalCode->postal_code,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();

    expect($user->birthday->toDateString())->toBe('1990-01-01');
    expect($user->postal_code)->toBe(1234);
});

test('profile page includes subscription information', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('settings.profile'));

    $response->assertOk();
    $response->assertSee('subscription');
});

test('profile appearance can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('settings.appearance.update'), [
            'appearance' => 'dark',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($user->refresh()->appearance)->toBe('dark');
});
