<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\seeders\RoleSeeder::class);
});

test('new users can register with foreign phone number with country code', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'us@example.com']);

    // US number
    $response = $this->post(route('register.store'), [
        'name' => 'US User',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '12345',
        'phone' => '+12025550123',
        'email' => 'us@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('name', 'US User')->first();
    expect($user->phoneNumbers)->toHaveCount(1);
    expect($user->phoneNumbers->first()->e164)->toBe('+12025550123');
});

test('new users can register with Swedish phone number with country code', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'se@example.com']);

    // SE number
    $response = $this->post(route('register.store'), [
        'name' => 'SE User',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '12345',
        'phone' => '+46701234567',
        'email' => 'se@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('name', 'SE User')->first();
    expect($user->phoneNumbers)->toHaveCount(1);
    expect($user->phoneNumbers->first()->e164)->toBe('+46701234567');
});

test('user lookup finds foreign number by E164', function () {
    $phone = \App\Models\PhoneNumber::create([
        'e164' => '+46701234567',
    ]);

    $user = \App\Models\User::factory()->create([
        'name' => 'Swedish User',
    ]);
    $user->phoneNumbers()->attach($phone->id, ['primary' => true]);

    $response = $this->getJson(route('auth.users.lookup', ['q' => '+46701234567']));

    $response->assertStatus(200);
    $response->assertJsonPath('users.0.name', 'Swedish User');
    $response->assertJsonPath('matchType', 'phone');
});

test('user lookup finds foreign number by local format if country is specified', function () {
    $phone = \App\Models\PhoneNumber::create([
        'e164' => '+46701234567',
    ]);

    $user = \App\Models\User::factory()->create([
        'name' => 'Swedish User',
    ]);
    $user->phoneNumbers()->attach($phone->id, ['primary' => true]);

    $response = $this->getJson(route('auth.users.lookup', ['q' => '0701234567']));

    $response->assertStatus(200);
    $response->assertJsonPath('users.0.name', 'Swedish User');
    $response->assertJsonPath('matchType', 'phone');
});

test('user lookup returns formatted E164 for foreign local format', function () {
    $response = $this->getJson(route('auth.users.lookup', ['q' => '0701234567']));

    $response->assertStatus(200);
    $response->assertJsonPath('formattedPhone', '+46701234567');
});

test('new users can register with Swedish local phone number', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'selocal@example.com']);

    // SE number in local format
    $response = $this->post(route('register.store'), [
        'name' => 'SE Local User',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '12345',
        'phone' => '0701234567',
        'email' => 'selocal@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('name', 'SE Local User')->first();
    expect($user->phoneNumbers)->toHaveCount(1);
    expect($user->phoneNumbers->first()->e164)->toBe('+46701234567');
});
