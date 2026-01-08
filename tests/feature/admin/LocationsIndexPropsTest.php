<?php

use App\Enums\RolesEnum;
use App\Models\Location;
use App\Models\PostalCode;
use App\Models\User;
use Spatie\Permission\Models\Role;

// Note: Using simple content assertions to avoid coupling to Inertia's test helpers

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $this->user = User::factory()->create(['email_verified_at' => now()]);
    $this->user->assignRole(RolesEnum::ADMIN->value);
});

it('shows postal_code as scalar and related city only on locations index', function (): void {
    $pc = PostalCode::factory()->create();
    $loc = Location::query()->create([
        'postal_code' => $pc->postal_code,
        'name' => 'Sample Location',
        'active' => true,
    ]);

    $response = $this->actingAs($this->user)->get('/admin/locations');
    $response->assertSuccessful();
    // Ensure the page output contains the scalar postal code and the related city string
    $response->assertSee((string) $pc->postal_code);
    $response->assertSee($pc->city, false);
});
