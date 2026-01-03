<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\seeders\RoleSeeder::class);
});

test('minor registration includes relationship', function () {
    Notification::fake();

    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'minor@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Minor User',
        'email' => 'minor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(10)->toDateString(),
        'postal_code' => '1234',
        'guardian_contact' => 'parent@example.com',
        'relationship' => 'Son',
    ]);

    $response->assertRedirect(route('dashboard'));

    $minor = User::where('email', 'minor@example.com')->first();
    expect($minor->guardians)->toHaveCount(0); // parent@example.com doesn't exist yet, so it's a pending invitation

    $this->assertDatabaseHas('guardian_user', [
        'minor_id' => $minor->id,
        'pending_contact' => 'parent@example.com',
        'relationship' => 'Son',
    ]);
});

test('invited guardian registers and confirms relationship', function () {
    Notification::fake();

    // 1. Setup a minor with a pending invitation
    $minor = User::factory()->create([
        'birthday' => now()->subYears(10)->toDateString(),
    ]);

    \DB::table('guardian_user')->insert([
        'minor_id' => $minor->id,
        'relationship' => 'Invitation',
        'pending_contact' => 'parent@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

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
        'relationship' => 'Father',
    ]);

    $response->assertRedirect(route('dashboard'));

    $guardian = User::where('email', 'parent@example.com')->first();
    expect($guardian->hasRole('guardian'))->toBeTrue();

    // Check link was completed with confirmed relationship
    $this->assertDatabaseHas('guardian_user', [
        'minor_id' => $minor->id,
        'guardian_id' => $guardian->id,
        'relationship' => 'Father',
        'pending_contact' => null,
    ]);
});

test('guardian must be older than 25', function () {
    // Setup a minor with a pending invitation
    $minor = User::factory()->create([
        'birthday' => now()->subYears(10)->toDateString(),
    ]);

    \DB::table('guardian_user')->insert([
        'minor_id' => $minor->id,
        'relationship' => 'Invitation',
        'pending_contact' => 'young-parent@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Guardian is 20 years old
    session(['registration_otp_verified' => true]);
    session(['registration_email' => 'young-parent@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Young Guardian',
        'email' => 'young-parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birthday' => now()->subYears(20)->toDateString(),
        'postal_code' => '5678',
        'relationship' => 'Brother',
    ]);

    $response->assertSessionHasErrors(['birthday']);
    $this->assertDatabaseMissing('users', ['email' => 'young-parent@example.com']);
});
