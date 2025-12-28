<?php

namespace Database\Seeders;

use App\Models\PostalCode;
use Illuminate\Database\Seeder;

class PostalCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PostalCode::factory()->create([
            'postal_code' => 1300,
            'city' => 'Sandvika',
            'state' => 'Akershus',
            'country' => 'Norge',
            'county' => 'Bærum',
        ]);
        PostalCode::factory()->create([
            'postal_code' => 1353,
            'city' => 'Bærums Verk',
            'state' => 'Akershus',
            'country' => 'Norge',
            'county' => 'Bærum',
        ]);
        PostalCode::factory()->create([
            'postal_code' => 1350,
            'city' => 'Lommedalen',
            'state' => 'Akershus',
            'country' => 'Norge',
            'county' => 'Bærum',
        ]);
        PostalCode::factory()->create([
            'postal_code' => 3512,
            'city' => 'Hønefoss',
            'state' => 'Buskerud',
            'country' => 'Norge',
            'county' => 'Hole',
        ]);
    }
}
