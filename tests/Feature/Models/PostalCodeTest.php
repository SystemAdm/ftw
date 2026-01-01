<?php

use App\Models\PostalCode;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a postal code and relates to users via custom key', function (): void {
    $pc = PostalCode::create([
        'postal_code' => 12345,
        'city' => 'Test City',
        'state' => 'TS',
        'country' => 'TC',
        'municipality' => 'Test Municipality',
    ]);

    $user = User::factory()->create([
        'postal_code' => 12345,
    ]);

    expect($user->postalCode)->not->toBeNull();
    expect($user->postalCode->postal_code)->toBe(12345);
    expect($pc->users()->count())->toBe(1);

    assertDatabaseHas('postal_codes', [
        'postal_code' => 12345,
        'city' => 'Test City',
    ]);
});
