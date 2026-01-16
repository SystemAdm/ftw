<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\PostalCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');

        $this->postalCode = PostalCode::factory()->create(['postal_code' => 1353, 'city' => 'Bærums Verk']);
    }

    public function test_it_stores_latitude_and_longitude_with_reasonable_precision()
    {
        $latitude = 59.94848712;
        $longitude = 10.50534245;

        $response = $this->actingAs($this->user)
            ->post(route('admin.locations.store'), [
                'postal_code' => $this->postalCode->postal_code,
                'name' => 'Reasonable Precision Location',
                'active' => true,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

        $response->assertRedirect();

        $location = Location::where('name', 'Reasonable Precision Location')->first();

        $this->assertEquals($latitude, $location->latitude);
        $this->assertEquals($longitude, $location->longitude);
    }
}
