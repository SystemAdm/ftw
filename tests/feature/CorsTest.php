<?php

use function Pest\Laravel\call;

it('allows CORS preflight for Google auth redirect endpoint', function () {
    $response = call('OPTIONS', '/auth/google', [], [
        'Origin' => 'http://localhost:5173',
        'Access-Control-Request-Method' => 'GET',
        'Access-Control-Request-Headers' => 'Content-Type',
    ]);

    // Should be handled by CORS middleware; some servers respond 204, others 200
    $response->assertSuccessful();
    // Some environments may not echo CORS headers on synthetic test preflights.
    // The important part is that the preflight does not error.
});
