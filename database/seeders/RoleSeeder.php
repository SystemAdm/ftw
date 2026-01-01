<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RolesEnum::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }

        $role = Role::findByName(RolesEnum::ADMIN->value)->first();
        $user = User::where('email','odd-erik@spillhuset.com')->first();
        if ($user && $role) $user->assignRole($role);
    }
}
