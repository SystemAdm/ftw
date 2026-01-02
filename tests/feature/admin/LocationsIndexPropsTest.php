<?php

use App\Models\Location;
use App\Models\PostalCode;
use App\Models\User;

// Note: Using simple content assertions to avoid coupling to Inertia's test helpers

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows postal_code as scalar and related city only on locations index', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $pc = PostalCode::factory()->create();
    $loc = Location::query()->create([
        'postal_code' => $pc->postal_code,
        'name' => 'Sample Location',
        'active' => true,
    ]);

    $response = $this->get('/admin/locations');
    $response->assertSuccessful();
    // Ensure the page output contains the scalar postal code and the related city string
    $response->assertSee((string) $pc->postal_code);
    $response->assertSee(e($pc->city));
});
