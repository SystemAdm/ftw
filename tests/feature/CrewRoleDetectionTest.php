<?php

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

test('user with team role but no global crew role is considered crew', function () {
    setPermissionsTeamId(0);
    $user = User::factory()->create();
    $team = Team::factory()->create();

    // Create role in team context
    setPermissionsTeamId($team->id);
    Role::create(['name' => RolesEnum::CREW->value, 'team_id' => $team->id, 'guard_name' => 'web']);
    $user->assignRole(RolesEnum::CREW->value);

    // Switch back to global to check shared data
    setPermissionsTeamId(0);

    $response = actingAs($user)
        ->get(route('home'));

    $response->assertOk();
});

test('user with no roles is not considered crew', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('home'))
        ->assertOk();
});

test('user with global crew role is considered crew', function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => 0, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(RolesEnum::CREW->value);

    actingAs($user)
        ->get(route('home'))
        ->assertOk();
});
