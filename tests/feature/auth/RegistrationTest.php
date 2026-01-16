<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\TeamSeeder::class);
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register with email', function () {
    $this->withoutMiddleware();

    $response = $this->withSession([
        'registration_otp_verified' => true,
        'registration_email' => 'test@example.com',
    ])->post(route('register.store'), [
        'given_name' => 'Test',
        'family_name' => 'User',
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
    expect($user->hasRole(\App\Enums\RolesEnum::GUEST->value))->toBeTrue();
});

test('new users can register with phone', function () {
    $response = $this->withSession([
        'registration_otp_verified' => true,
        'registration_email' => 'phone@example.com',
    ])->post(route('register.store'), [
        'given_name' => 'Phone',
        'family_name' => 'User',
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

    $user = \App\Models\User::where('email', 'phone@example.com')->first();
    expect($user->phoneNumbers)->toHaveCount(1);
    expect($user->phoneNumbers->first()->e164)->toBe('+4799999999');
    expect($user->email)->toBe('phone@example.com');
});

test('new users cannot register if allow_new_users is false', function () {
    config(['custom.allow_new_users' => false]);

    $response = $this->post(route('register.store'), [
        'given_name' => 'Blocked',
        'family_name' => 'User',
        'email' => 'blocked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '12345',
    ]);

    $response->assertForbidden();
    $this->assertGuest();
});

test('minor registration requires guardian', function () {
    $response = $this->withSession([
        'registration_otp_verified' => true,
        'registration_email' => 'minor@example.com',
    ])->post(route('register.store'), [
        'given_name' => 'Minor',
        'family_name' => 'User',
        'birthday' => now()->subYears(10)->toDateString(), // 10 years old
        'postal_code' => '12345',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        // 'guardian_contact' is missing
    ]);

    $response->assertSessionHasErrors(['guardian_contact']);

    $response = $this->post(route('register.store'), [
        'given_name' => 'Minor',
        'family_name' => 'User',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '12345',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'guardian_contact' => 'parent@example.com',
        'relationship' => 'Parent',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('email', 'minor@example.com')->first();
    // In handleGuardian, if guardian not found, it inserts into guardian_user table with pending_contact
    $pending = \DB::table('guardian_user')->where('minor_id', $user->id)->first();
    expect($pending)->not->toBeNull();
    expect($pending->pending_contact)->toBe('parent@example.com');
});

test('minor registration with existing guardian user', function () {
    $guardian = \App\Models\User::factory()->create([
        'email' => 'existing-parent@example.com',
        'birthday' => now()->subYears(30)->toDateString(),
    ]);

    $response = $this->withSession([
        'registration_otp_verified' => true,
        'registration_email' => 'minor-with-parent@example.com',
    ])->post(route('register.store'), [
        'given_name' => 'Minor',
        'family_name' => 'With Parent',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '12345',
        'email' => 'minor-with-parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'guardian_contact' => 'existing-parent@example.com',
        'relationship' => 'Parent',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('email', 'minor-with-parent@example.com')->first();
    expect($user->guardians)->toHaveCount(1);
    expect($user->guardians->first()->id)->toBe($guardian->id);
});

test('guardian registration fulfills pending invitation', function () {
    $minor = \App\Models\User::factory()->create([
        'given_name' => 'Minor',
        'family_name' => 'child',
        'name' => 'Minor child',
        'birthday' => now()->subYears(10)->toDateString(),
    ]);

    \DB::table('guardian_user')->insert([
        'minor_id' => $minor->id,
        'relationship' => 'Pending',
        'pending_contact' => 'guardian@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->withSession([
        'registration_otp_verified' => true,
        'registration_email' => 'guardian@example.com',
    ])->post(route('register.store'), [
        'given_name' => 'Guardian',
        'family_name' => 'User',
        'birthday' => now()->subYears(30)->toDateString(),
        'postal_code' => '12345',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'relationship' => 'Father',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('email', 'guardian@example.com')->first();
    expect($user->hasRole(\App\Enums\RolesEnum::GUARDIAN->value))->toBeTrue();
    expect($user->minors)->toHaveCount(1);
    expect($user->minors->first()->id)->toBe($minor->id);
});

test('new users with spillhuset email get crew role', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'crew@spillhuset.com']);

    $response = $this->post(route('register.store'), [
        'given_name' => 'Crew',
        'family_name' => 'User',
        'birthday' => now()->subYears(25)->toDateString(),
        'postal_code' => '12345',
        'email' => 'crew@spillhuset.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = \App\Models\User::where('email', 'crew@spillhuset.com')->first();
    setPermissionsTeamId(\App\Models\Team::where('slug', 'SH')->first()->id);
    expect($user->refresh()->hasRole(\App\Enums\RolesEnum::CREW->value))->toBeTrue();
    setPermissionsTeamId(0);
    expect($user->refresh()->hasRole(\App\Enums\RolesEnum::GUEST->value))->toBeTrue();
});
