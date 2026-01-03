<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Global Roles
        setPermissionsTeamId(0);
        $globalRoles = [
            RolesEnum::OWNER,
            RolesEnum::ADMIN,
            RolesEnum::MODERATOR,
            RolesEnum::GUEST,
            RolesEnum::GUARDIAN,
        ];

        foreach ($globalRoles as $role) {
            Role::firstOrCreate(['name' => $role->value, 'team_id' => 0]);
        }

        // Team 1 (Spillhuset) Roles
        $sh = Team::where('slug', 'SH')->first();
        if ($sh) {
            setPermissionsTeamId($sh->id);
            $shRoles = [
                RolesEnum::BOARD_CHAIRMAN,
                RolesEnum::BOARD_MEMBER,
                RolesEnum::CREW,
            ];

            foreach ($shRoles as $role) {
                Role::firstOrCreate(['name' => $role->value, 'team_id' => $sh->id]);
            }
        }

        // Team 2 (Flisespikkeriet) Roles
        $fs = Team::where('slug', 'FS')->first();
        if ($fs) {
            setPermissionsTeamId($fs->id);
            $fsRoles = [
                RolesEnum::CREW,
                RolesEnum::INSTRUCTOR,
            ];

            foreach ($fsRoles as $role) {
                Role::firstOrCreate(['name' => $role->value, 'team_id' => $fs->id]);
            }
        }

        $role = Role::where('name', RolesEnum::ADMIN->value)->where('team_id', 0)->first();
        $user = User::where('email', 'odd-erik@spillhuset.com')->first();
        if ($user && $role) {
            setPermissionsTeamId(0);
            $user->assignRole($role);
        }
    }
}
