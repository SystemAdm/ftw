<?php

use Laravel\Socialite\Facades\Socialite;

use function Pest\Laravel\get;

it('uses configured Google redirect URI', function () {
    $driver = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);
    $driver->shouldReceive('stateless')->once()->andReturnSelf();

    $dummyRedirect = 'https://example.com/oauth/google';
    $driver->shouldReceive('redirect')->once()->andReturn(redirect($dummyRedirect));

    $response = get(route('social.google'));

    $response->assertRedirect($dummyRedirect);
});
