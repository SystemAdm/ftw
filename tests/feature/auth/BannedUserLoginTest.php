<?php

use App\models\User;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('banned user cannot log in', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'banned_at' => now(),
        'ban_reason' => 'Test ban reason',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('ban_message');
    $this->assertGuest();
});

test('banned user receives correct reason in flash message', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'banned_at' => now(),
        'ban_reason' => 'Cheating',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('ban_message', function ($message) {
        return str_contains($message, 'Cheating');
    });
});

test('banned user receives duration in flash message if applicable', function () {
    app()->setLocale('en');
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'banned_at' => now(),
        'banned_to' => now()->addDays(7),
        'ban_reason' => 'Temporary suspension',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('ban_message', function ($message) {
        return str_contains($message, 'Temporary suspension') &&
               (str_contains($message, 'day') || str_contains($message, 'week') || str_contains($message, 'from now'));
    });
});

test('expired ban allows login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'banned_at' => now()->subDays(2),
        'banned_to' => now()->subDay(),
        'ban_reason' => 'Expired ban',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('normal user can log in', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('banned user cannot log in via socialite', function () {
    $user = User::factory()->create([
        'google_id' => '123456789',
        'banned_at' => now(),
        'ban_reason' => 'Social ban',
    ]);

    // Mock Socialite
    $socialiteUser = Mockery::mock('Laravel\Socialite\Two\User');
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
    $socialiteUser->shouldReceive('getName')->andReturn($user->name);
    $socialiteUser->shouldReceive('getAvatar')->andReturn('avatar.jpg');
    $socialiteUser->avatar = 'avatar.jpg';

    $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $provider->shouldReceive('stateless->user')->andReturn($socialiteUser);

    \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect('/');
    $response->assertSessionHas('ban_message', function ($message) {
        return str_contains($message, 'Social ban');
    });
    $this->assertGuest();
});
