<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Weekday;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PostalCodeSeeder::class,
            LocationSeeder::class,
        ]);

        // Blog::factory(30)->create();

        Team::create(['name' => 'Spillhuset', 'slug' => 'SH']);
        Team::create(['name' => 'Flisespikkeriet', 'slug' => 'FS']);
        Team::create(['name' => 'Ungdom og Fritid Bærum Kommune', 'slug' => 'UFB']);
        Weekday::create([
            'name' => 'Fredagsåpent',
            'description' => 'Åpent for alle over 13 år',
            'weekday' => 5,
            'team_id' => 1,
            'location_id' => 2,
            'active' => true,
            'start_time' => '18:00:00',
            'end_time' => '22:00:00',
        ]);
        Weekday::create([
            'name' => 'Torsdagsåpent',
            'description' => 'Åpent for alle over 13 år',
            'weekday' => 4,
            'team_id' => 3,
            'location_id' => 2,
            'active' => true,
            'start_time' => '15:00:00',
            'end_time' => '21:00:00',
        ]);
    }
}
