<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::firstOrCreate(['slug' => 'SH'], ['name' => 'Spillhuset', 'active' => true]);
        Team::firstOrCreate(['slug' => 'FS'], ['name' => 'Flisespikkeriet', 'active' => true]);
        Team::firstOrCreate(['slug' => 'UFB'], ['name' => 'Ungdom og Fritid Bærum Kommune', 'active' => true]);
    }
}
