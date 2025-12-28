<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register with email', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'test@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '12345',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
});

test('new users can register with phone', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'phone@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Phone User',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '12345',
        'phone' => '99999999',
        'email' => 'phone@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $user = \App\Models\User::where('name', 'Phone User')->first();
    expect($user->phoneNumbers)->toHaveCount(1);
    expect($user->phoneNumbers->first()->e164)->toBe('+4799999999');
    expect($user->email)->toBe('phone@example.com');
});

test('new users cannot register if allow_new_users is false', function () {
    config(['custom.allow_new_users' => false]);

    $response = $this->post(route('register.store'), [
        'name' => 'Blocked User',
        'email' => 'blocked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertForbidden();
    $this->assertGuest();
});
