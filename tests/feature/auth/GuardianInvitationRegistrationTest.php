<?php

use App\Models\User;
use App\notifications\auth\GuardianInvitation;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\seeders\RoleSeeder::class);
});

test('invited guardian registers and gets guardian role and is linked to minor', function () {
    Notification::fake();

    // 1. Minor registers and invites guardian
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'minor@example.com']);

    $this->post(route('register.store'), [
        'name' => 'Minor User',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '1234',
        'guardian_contact' => 'parent@example.com',
    ]);

    Auth::logout();

    $minor = User::where('email', 'minor@example.com')->first();
    expect($minor)->not->toBeNull();

    // Check invitation was stored
    $this->assertDatabaseHas('guardian_user', [
        'minor_id' => $minor->id,
        'guardian_id' => null,
        'pending_contact' => 'parent@example.com',
    ]);

    Notification::assertSentTo(
        new \Illuminate\Notifications\AnonymousNotifiable,
        GuardianInvitation::class
    );

    // 2. Guardian registers
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'parent@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Guardian User',
        'email' => 'parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(30)->toDateString(),
        'postal_code' => '5678',
    ]);

    if (! $response->isRedirect(route('dashboard'))) {
        if ($response->getSession()) {
            dump('Errors:', $response->getSession()->get('errors')?->all());
        } else {
            dump('Content:', $response->getContent());
        }
    }

    $response->assertRedirect(route('dashboard'));

    $guardian = User::withTrashed()->where('email', 'parent@example.com')->first();
    expect($guardian)->not->toBeNull();
    if ($guardian->deleted_at) {
        dump('User was deleted at: '.$guardian->deleted_at);
        dump('Age: '.$guardian->birthday->age);
    }
    expect($guardian->hasRole('guardian'))->toBeTrue();
    expect($guardian->hasRole('guest'))->toBeTrue();

    // Check link was completed
    $this->assertDatabaseHas('guardian_user', [
        'minor_id' => $minor->id,
        'guardian_id' => $guardian->id,
        'pending_contact' => null,
    ]);
});

test('invited guardian registration fails if too young', function () {
    // 1. Minor registers and invites guardian
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'minor@example.com']);

    $this->post(route('register.store'), [
        'name' => 'Minor User',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '1234',
        'guardian_contact' => 'young-parent@example.com',
    ]);

    Auth::logout();

    // 2. Young Guardian registers (e.g. 20 years old, while 25 is required)
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'young-parent@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Young Guardian',
        'email' => 'young-parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '5678',
    ]);

    $response->assertSessionHasErrors(['birthday']);
    $this->assertDatabaseMissing('users', ['email' => 'young-parent@example.com']);
});

test('invited guardian via phone registers and gets guardian role', function () {
    // 1. Minor registers and invites guardian via phone
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'minor2@example.com']);

    $this->post(route('register.store'), [
        'name' => 'Minor User 2',
        'email' => 'minor2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '1234',
        'guardian_contact' => '99887766', // Falling back to NO in config.
    ]);

    Auth::logout();

    $minor = User::where('email', 'minor2@example.com')->first();
    $e164 = '+4799887766'; // NO fallback

    // Check invitation was stored with E.164
    $this->assertDatabaseHas('guardian_user', [
        'minor_id' => $minor->id,
        'guardian_id' => null,
        'pending_contact' => $e164,
    ]);

    // 2. Guardian registers with that phone
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'parent2@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Guardian User 2',
        'email' => 'parent2@example.com',
        'phone' => '99887766',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(30)->toDateString(),
        'postal_code' => '5678',
    ]);

    $response->assertRedirect(route('dashboard'));

    $guardian = User::where('email', 'parent2@example.com')->first();
    expect($guardian->hasRole('guardian'))->toBeTrue();

    // Check link was completed
    $this->assertDatabaseHas('guardian_user', [
        'minor_id' => $minor->id,
        'guardian_id' => $guardian->id,
        'pending_contact' => null,
    ]);
});
