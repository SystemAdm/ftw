<?php

use App\Enums\RolesEnum;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailWithPin;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\TeamSeeder::class);
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('user can complete multi-step registration', function () {
    Notification::fake();

    // Step 1: Send OTP
    $response = $this->post(route('register.otp.send'), [
        'email' => 'newuser@example.com',
        'phone' => '99887766',
    ]);

    $response->assertSuccessful();
    $this->assertEquals('newuser@example.com', session('registration_email'));
    $pin = session('registration_otp');
    expect($pin)->not->toBeNull();

    Notification::assertSentTo(
        new \Illuminate\Notifications\AnonymousNotifiable,
        VerifyEmailWithPin::class,
        function (VerifyEmailWithPin $notification, $channels, $notifiable) use ($pin) {
            return $notification->pin === $pin && $notifiable->routeNotificationFor('mail') === 'newuser@example.com';
        }
    );

    // Step 2: Verify OTP
    $response = $this->post(route('register.otp.verify'), [
        'pin' => $pin,
    ]);

    $response->assertSuccessful();
    expect(session('registration_otp_verified'))->toBeTrue();

    // Step 3-6: Final Submit (all data collected on frontend)
    $response = $this->post(route('register.store'), [
        'given_name' => 'New',
        'family_name' => 'User',
        'email' => 'newuser@example.com',
        'phone' => '99887766',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => '2000-01-01',
        'postal_code' => '1234',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New User');
    expect($user->birthday->toDateString())->toBe('2000-01-01');
    expect($user->postal_code)->toEqual(1234);
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->hasRole(RolesEnum::GUEST->value))->toBeTrue();
});

test('minor registration requires guardian', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'minor@example.com']);

    $response = $this->post(route('register.store'), [
        'given_name' => 'Minor',
        'family_name' => 'User',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '1234',
    ]);

    $response->assertSessionHasErrors(['guardian_contact']);
});

test('minor registration with existing guardian', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $guardian = User::factory()->create([
        'email' => 'parent@example.com',
        'birthday' => now()->subYears(30)->toDateString(),
    ]);
    setPermissionsTeamId(0);
    $guardian->assignRole(RolesEnum::GUARDIAN->value);

    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'minor@example.com']);

    $response = $this->post(route('register.store'), [
        'given_name' => 'Minor',
        'family_name' => 'User',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '1234',
        'guardian_contact' => 'parent@example.com',
    ]);

    $response->assertRedirect(route('dashboard'));
    $minor = User::where('email', 'minor@example.com')->first();
    expect($minor->guardians)->toHaveCount(1);
    expect($minor->guardians->first()->id)->toBe($guardian->id);

    \Illuminate\Support\Facades\Notification::assertSentTo($guardian, \App\Notifications\Auth\GuardianRelationConfirmation::class);
});

test('crew email gets crew role', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'crew@spillhuset.com']);

    $response = $this->post(route('register.store'), [
        'given_name' => 'Crew',
        'family_name' => 'User',
        'email' => 'crew@spillhuset.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => '1990-01-01',
        'postal_code' => '1234',
    ]);

    $response->assertRedirect(route('dashboard'));
    $user = User::where('email', 'crew@spillhuset.com')->first();
    setPermissionsTeamId(\App\Models\Team::where('slug', 'SH')->first()->id);
    expect($user->refresh()->hasRole(RolesEnum::CREW->value))->toBeTrue();
    setPermissionsTeamId(0);
    expect($user->refresh()->hasRole(RolesEnum::GUEST->value))->toBeTrue();
});

test('registration fails if OTP is not verified', function () {
    $response = $this->post(route('register.store'), [
        'given_name' => 'New',
        'family_name' => 'User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => '2000-01-01',
        'postal_code' => '1234',
    ]);

    $response->assertSessionHasErrors(['pin']);
});

test('registration fails if email mismatches verified OTP', function () {
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'verified@example.com']);

    $response = $this->post(route('register.store'), [
        'given_name' => 'New',
        'family_name' => 'User',
        'email' => 'wrong@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => '2000-01-01',
        'postal_code' => '1234',
    ]);

    $response->assertSessionHasErrors(['email']);
});
