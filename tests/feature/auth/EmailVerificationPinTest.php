<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unverified user can see verification screen', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'email_verification_code' => '123456',
    ]);

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertStatus(200);
});

test('user can verify email with correct pin', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'email_verification_code' => '123456',
    ]);

    $response = $this->actingAs($user)->post(route('verification.verify-pin'), [
        'pin' => '123456',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    expect($user->fresh()->email_verification_code)->toBeNull();
});

test('user cannot verify email with incorrect pin', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'email_verification_code' => '123456',
    ]);

    $response = $this->actingAs($user)->post(route('verification.verify-pin'), [
        'pin' => '654321',
    ]);

    $response->assertSessionHasErrors('pin');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verified user is redirected to dashboard when trying to verify again', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('verification.verify-pin'), [
        'pin' => '123456',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});
