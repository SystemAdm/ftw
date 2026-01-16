<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::firstOrCreate(
            [
                'name' => 'Løkketangen'
            ], [
                'postal_code' => 1300,
                'active' => true,
                'description' => 'Sammen med UFB og Flisespikkeriet',
                'street_address' => 'Løkketangen',
                'street_number' => '4-12',
            ]
        );

        Location::firstOrCreate(
            [
                'name' => 'Helset'
            ], [
                'postal_code' => 1353,
                'active' => true,
                'description' => 'Sammen med UFB og Flisespikkeriet',
                'street_address' => 'Skollerudveien',
                'street_number' => '5',
                'latitude' => '59.9429798715757510.50802216101194',
                'longitude' => '10.50802216101194',
            ]
        );

        // Location::factory(6)->create();
    }
}
