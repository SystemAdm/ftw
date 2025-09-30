<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'guest']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'guardian']);
        Role::create(['name' => 'member']);
        Role::create(['name' => 'crew']);
    }
}
