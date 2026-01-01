<?php

use App\http\controllers\settings\BillingController;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes app locale "no" to Stripe locale "nb"', function (): void {
    // Ensure app locale is set to Norwegian generic
    config(['app.locale' => 'no']);

    $controller = new BillingController;

    $ref = new ReflectionClass($controller);
    $method = $ref->getMethod('normalizeStripeLocale');
    $method->setAccessible(true);

    $result = $method->invoke($controller);

    expect($result)->toBe('nb');
});

it('falls back to auto for unsupported locales', function (): void {
    config(['app.locale' => 'xx-YY']);

    $controller = new BillingController;

    $ref = new ReflectionClass($controller);
    $method = $ref->getMethod('normalizeStripeLocale');
    $method->setAccessible(true);

    $result = $method->invoke($controller);

    expect($result)->toBe('auto');
});
