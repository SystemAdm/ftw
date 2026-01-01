<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;

it('shares isProduction flag with Inertia and is false in test env', function (): void {
    /** @var HandleInertiaRequests $middleware */
    $middleware = app(HandleInertiaRequests::class);

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    expect($shared)
        ->toHaveKey('isProduction')
        ->and($shared['isProduction'])->toBeFalse();
});
