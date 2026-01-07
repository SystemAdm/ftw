<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(RolesEnum::ADMIN->value);
});

test('admin can access dashboard', function () {
    $response = actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $response->assertStatus(200);
});

test('owner can access dashboard', function () {
    $owner = User::factory()->create();
    Role::firstOrCreate(['name' => RolesEnum::OWNER->value, 'team_id' => 0]);
    $owner->assignRole(RolesEnum::OWNER->value);

    $response = actingAs($owner)
        ->get(route('admin.dashboard'));

    $response->assertStatus(200);
});

test('guest cannot access admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('non-admin cannot access admin dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertStatus(403);
});
