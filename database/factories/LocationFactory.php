<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'postal_code' => (int) fake()->randomElement([1300, 1353, 1350, 3512]),
            'name' => fake()->company(),
            'active' => fake()->boolean(70),
            'description' => fake()->optional()->paragraph(),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'google_maps_url' => fake()->optional()->url(),
            'images' => null,
            'street_address' => fake()->optional()->streetName(),
            'street_number' => fake()->optional()->buildingNumber(),
            'link' => fake()->optional()->url(),
        ];
    }
}
