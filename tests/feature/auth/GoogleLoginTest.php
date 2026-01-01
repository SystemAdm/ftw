<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

it('logs in existing user via Google and redirects to dashboard', function () {
    $email = 'john.doe@example.com';
    $user = User::factory()->create(['email' => $email]);

    // Mock Socialite driver chain
    $driver = Mockery::mock();
    $googleUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
    $googleUser->shouldReceive('getEmail')->andReturn($email);
    $googleUser->shouldReceive('getId')->andReturn('google-123');

    Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    $driver->shouldReceive('stateless')->andReturnSelf();
    $driver->shouldReceive('user')->andReturn($googleUser);

    $response = get(route('social.google.callback'));

    $response->assertRedirect(route('dashboard'));
    assertAuthenticatedAs($user);
});

it('redirects back to login with error when no local account matches Google email', function () {
    $email = 'no-account@example.com';

    $driver = Mockery::mock();
    $googleUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
    $googleUser->shouldReceive('getEmail')->andReturn($email);
    $googleUser->shouldReceive('getId')->andReturn('google-456');

    Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    $driver->shouldReceive('stateless')->andReturnSelf();
    $driver->shouldReceive('user')->andReturn($googleUser);

    $response = get(route('social.google.callback'));

    $response->assertRedirect(route('login'));
    assertGuest();
});
