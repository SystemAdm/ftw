<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $i = User::factory()->create([
            'name' => 'Odd-Erik Jovang',
            'email' => 'jovang@gmail.com',
            'password' => Hash::make('password'),
            'email_normalized' => 'jovang@gmail.com',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $j = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@ftw.test',
            'password' => Hash::make('password'),
            'email_normalized' => 'admin@ftw.test',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $k = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@ftw.test',
            'email_normalized' => 'test@ftw.test',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $l = User::factory()->create([
            'name' => 'Guardian User',
            'email' => 'guardian@ftw.test',
            'password' => Hash::make('password'),
            'email_normalized' => 'guardian@ftw.test',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $m = User::factory()->create([
            'name' => 'Member User',
            'email' => 'member@ftw.test',
            'password' => Hash::make('password'),
            'email_normalized' => 'member@ftw.test',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $n = User::factory()->create([
            'name' => 'Crew User',
            'email' => 'crew@ftw.test',
            'password' => Hash::make('password'),
            'email_normalized' => 'crew@ftw.test',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $o = User::factory()->create([
            'name' => 'Crew Admin',
            'email' => 'crew_admin@ftw.test',
            'password' => Hash::make('password'),
            'email_normalized' => 'crew_admin@ftw.test',
            'email_verified_at' => now(),
            'verified_at' => now(),
        ]);

        $i->assignRole(['owner','admin','guest']);
        $j->assignRole(['admin','guest']);
        $k->assignRole(['guest','guest']);
        $l->assignRole(['guardian','guest']);
        $m->assignRole(['member','guest']);
        $n->assignRole(['crew','member','guest']);
        $o->assignRole(['admin','crew','member','guest']);
    }
}
