<?php

namespace Database\Seeders;

use App\Models\Weekday;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
            TeamSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            PostalCodeSeeder::class,
            LocationSeeder::class,
        ]);

        // Blog::factory(30)->create();

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
        /*$ph = PhoneNumber::create([
            'raw' => '98218519','e164' => '+4798218519',
        ]);

        $user = User::where('email', 'jovang@gmail.com')->first();
        $user->phoneNumbers()->attach($ph, ['primary' => true,'verified_at' => now(), 'verified_by' => 1]);*/
    }
}
