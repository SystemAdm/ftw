<?php

use Laravel\Socialite\Facades\Socialite;

use function Pest\Laravel\get;

it('forces Google callback to named route via redirectUrl', function () {
    $expectedCallback = route('social.google.callback');

    $driver = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);
    $driver->shouldReceive('stateless')->once()->andReturnSelf();
    $driver->shouldReceive('redirectUrl')->once()->with($expectedCallback)->andReturnSelf();

    $dummyRedirect = 'https://example.com/oauth/google';
    $driver->shouldReceive('redirect')->once()->andReturn(redirect($dummyRedirect));

    $response = get(route('social.google'));

    $response->assertRedirect($dummyRedirect);
});
