<?php

use App\Enums\RolesEnum;
use App\Models\Location;
use App\Models\PostalCode;
use App\Models\User;
use Database\Seeders\RoleSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    setPermissionsTeamId(0);
    $this->admin->assignRole(RolesEnum::ADMIN->value);
    $this->actingAs($this->admin);
});

it('shows admin locations index', function (): void {
    $response = $this->get('/admin/locations');
    $response->assertSuccessful();
});

it('can create, update, soft delete, restore and force delete a location', function (): void {
    $pc = PostalCode::factory()->create(['postal_code' => 11111]);

    // Create
    $this->withSession(['_token' => 'test']);
    $store = $this->post('/admin/locations', [
        'postal_code' => 11111,
        'name' => 'Test Location',
        'active' => true,
        'description' => 'Desc',
        'latitude' => 1.23,
        'longitude' => 4.56,
        'google_maps_url' => 'https://maps.example.com',
        'images' => '',
        'street_address' => 'Main St',
        'street_number' => '10',
        'link' => 'https://example.com',
        '_token' => 'test',
    ]);
    $store->assertRedirect();

    $loc = Location::query()->latest('id')->first();
    expect($loc)->not->toBeNull();
    expect($loc->name)->toBe('Test Location');

    // Update
    $this->withSession(['_token' => 'test']);
    $update = $this->put("/admin/locations/{$loc->id}", [
        'postal_code' => 11111,
        'name' => 'Updated Location',
        'active' => false,
        'description' => 'New Desc',
        'latitude' => 2.34,
        'longitude' => 5.67,
        'google_maps_url' => 'https://maps.example.com/2',
        'images' => '',
        'street_address' => 'Second St',
        'street_number' => '11',
        'link' => 'https://example.com/2',
        '_token' => 'test',
    ]);
    $update->assertRedirect();

    $loc->refresh();
    expect($loc->name)->toBe('Updated Location');
    expect($loc->active)->toBeFalse();

    // Soft delete
    $this->withSession(['_token' => 'test']);
    $del = $this->delete("/admin/locations/{$loc->id}", ['_token' => 'test']);
    $del->assertRedirect('/admin/locations');
    $this->assertSoftDeleted('locations', ['id' => $loc->id]);

    // Restore
    $this->withSession(['_token' => 'test']);
    $restore = $this->post("/admin/locations/{$loc->id}/restore", ['_token' => 'test']);
    $restore->assertRedirect('/admin/locations');
    $this->assertDatabaseHas('locations', ['id' => $loc->id, 'deleted_at' => null]);

    // Force delete
    $this->withSession(['_token' => 'test']);
    // delete first then force
    $this->delete("/admin/locations/{$loc->id}", ['_token' => 'test'])->assertRedirect();
    $force = $this->delete("/admin/locations/{$loc->id}/force", ['_token' => 'test']);
    $force->assertRedirect('/admin/locations');
    $this->assertDatabaseMissing('locations', ['id' => $loc->id]);
});
