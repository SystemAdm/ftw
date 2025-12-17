<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;

it('shares i18n payload with ui translations and nb locale', function (): void {
    /** @var HandleInertiaRequests $middleware */
    $middleware = app(HandleInertiaRequests::class);

    $request = Request::create('/', 'GET');
    $shared = $middleware->share($request);

    expect($shared)
        ->toHaveKey('i18n')
        ->and($shared['i18n'])
        ->toHaveKeys(['locale', 'fallback', 'trans'])
        ->and($shared['i18n']['trans'])
        ->toHaveKey('ui')
        ->and($shared['i18n']['trans']['ui']['brand'] ?? null)->toBeString();

    // Locale should be nb per .env APP_LOCALE=nb
    expect($shared['i18n']['locale'])->toBe('nb');
});
