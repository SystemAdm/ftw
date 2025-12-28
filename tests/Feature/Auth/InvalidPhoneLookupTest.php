<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lookup with invalid phone number returns validation error', function () {
    // 15 digits is generally invalid for the configured countries
    $response = $this->getJson(route('auth.users.lookup', ['q' => '123456789012345']));

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['q']);
});
