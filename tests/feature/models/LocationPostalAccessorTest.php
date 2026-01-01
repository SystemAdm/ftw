<?php

use App\Models\Location;
use App\Models\PostalCode;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('computes postal accessor with code and city when relation exists', function (): void {
    // Given an authenticated user (admin area guarded by auth)
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $pc = PostalCode::factory()->create([
        'postal_code' => 22222,
        'city' => 'Accessor City',
    ]);

    /** @var Location $loc */
    $loc = Location::query()->create([
        'postal_code' => $pc->postal_code,
        'name' => 'Loc A',
        'active' => true,
    ]);

    expect($loc->postal)->toBe('22222 Accessor City');
});

it('computes postal accessor with only code when city is empty', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $pc = PostalCode::factory()->create([
        'postal_code' => 33333,
        'city' => '', // empty string allowed by schema (not null)
    ]);

    /** @var Location $loc */
    $loc = Location::query()->create([
        'postal_code' => $pc->postal_code,
        'name' => 'Loc B',
        'active' => false,
    ]);

    expect($loc->postal)->toBe('33333');
});
