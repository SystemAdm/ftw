<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;

uses(Tests\TestCase::class);

it('shares isProduction flag with Inertia and is false in test env', function (): void {
    $middleware = new HandleInertiaRequests;

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    expect($shared)
        ->toHaveKey('isProduction')
        ->and($shared['isProduction'])->toBeFalse();
});
