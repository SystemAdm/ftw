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
        Location::factory()->create([
            'postal_code' => 1300,
            'name' => 'Løkketangen',
            'active' => true,
            'description' => 'Sammen med UFB og Flisespikkeriet',
            'street_address' => 'Løkketangen',
            'street_number' => '4-12',
        ]);

        Location::factory()->create([
            'postal_code' => 1353,
            'name' => 'Helset',
            'active' => true,
            'description' => 'Sammen med UFB og Flisespikkeriet',
            'street_address' => 'Skollerudveien',
            'street_number' => '5',
        ]);

        //Location::factory(6)->create();
    }
}
