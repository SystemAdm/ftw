<?php

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => 0]);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);

    $this->crew = User::factory()->create();
    $this->crew->assignRole(RolesEnum::CREW->value);
});

test('crew can access dashboard', function () {
    actingAs($this->crew)
        ->get(route('crew.dashboard'))
        ->assertStatus(200);
});

test('crew can access teams index', function () {
    actingAs($this->crew)
        ->get(route('crew.teams.index'))
        ->assertStatus(200);
});

test('crew can apply for a team', function () {
    $team = Team::factory()->create(['active' => true, 'applications_enabled' => true]);

    actingAs($this->crew)
        ->post(route('crew.teams.apply', $team), [
            'application' => 'I want to join this team because I love gaming.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_user', [
        'user_id' => $this->crew->id,
        'team_id' => $team->id,
        'status' => 'pending',
        'application' => 'I want to join this team because I love gaming.',
    ]);
});

test('crew cannot apply for a team with applications disabled', function () {
    $team = Team::factory()->create(['active' => true, 'applications_enabled' => false]);

    actingAs($this->crew)
        ->post(route('crew.teams.apply', $team), [
            'application' => 'I want to join this team.',
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('team_user', [
        'user_id' => $this->crew->id,
        'team_id' => $team->id,
    ]);
});

test('crew application requires minimum length', function () {
    $team = Team::factory()->create(['active' => true, 'applications_enabled' => true]);

    actingAs($this->crew)
        ->post(route('crew.teams.apply', $team), [
            'application' => 'Short',
        ])
        ->assertSessionHasErrors(['application']);
});

test('crew can leave a team', function () {
    $team = Team::factory()->create();
    $this->crew->teams()->attach($team->id, ['status' => 'approved', 'role' => RolesEnum::CREW->value]);

    actingAs($this->crew)
        ->delete(route('crew.teams.leave', $team))
        ->assertRedirect();

    $this->assertDatabaseMissing('team_user', [
        'user_id' => $this->crew->id,
        'team_id' => $team->id,
    ]);
});

test('crew can view team details', function () {
    $team = Team::factory()->create(['name' => 'Test Team']);
    $this->crew->teams()->attach($team->id, ['status' => 'approved', 'role' => RolesEnum::CREW->value]);

    actingAs($this->crew)
        ->get(route('crew.teams.show', $team))
        ->assertStatus(200)
        ->assertSee($team->name);
});

test('non-member cannot view team details', function () {
    $team = Team::factory()->create();
    // Not a member

    actingAs($this->crew)
        ->get(route('crew.teams.show', $team))
        ->assertStatus(403);
});

test('admin can view any team details', function () {
    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole(RolesEnum::ADMIN->value);

    actingAs($admin)
        ->get(route('crew.teams.show', $team))
        ->assertStatus(200);
});

test('privileged user can manage team members', function () {
    $team = Team::factory()->create();
    $leader = User::factory()->create();
    $leader->assignRole(RolesEnum::CREW->value);
    $leader->teams()->attach($team->id, ['status' => 'approved', 'role' => RolesEnum::BOARD_CHAIRMAN->value]);

    $member = User::factory()->create();
    $member->teams()->attach($team->id, ['status' => 'approved', 'role' => RolesEnum::CREW->value]);

    // Create roles for team context
    setPermissionsTeamId($team->id);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id]);
    Role::firstOrCreate(['name' => RolesEnum::BOARD_CHAIRMAN->value, 'team_id' => $team->id]);
    setPermissionsTeamId(0);

    actingAs($leader)
        ->post(route('crew.teams.members.update', [$team, $member]), [
            'role' => RolesEnum::BOARD_CHAIRMAN->value,
            'status' => 'approved',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'role' => RolesEnum::BOARD_CHAIRMAN->value,
    ]);

    actingAs($leader)
        ->delete(route('crew.teams.members.destroy', [$team, $member]))
        ->assertRedirect();

    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
    ]);
});

test('non-privileged user cannot manage team members', function () {
    $team = Team::factory()->create();
    $this->crew->teams()->attach($team->id, ['status' => 'approved', 'role' => RolesEnum::CREW->value]);

    $member = User::factory()->create();
    $member->teams()->attach($team->id, ['status' => 'approved', 'role' => RolesEnum::CREW->value]);

    actingAs($this->crew)
        ->post(route('crew.teams.members.update', [$team, $member]), [
            'role' => RolesEnum::BOARD_CHAIRMAN->value,
            'status' => 'approved',
        ])
        ->assertStatus(403);

    actingAs($this->crew)
        ->delete(route('crew.teams.members.destroy', [$team, $member]))
        ->assertStatus(403);
});

test('guest cannot access crew dashboard', function () {
    $this->get(route('crew.dashboard'))
        ->assertRedirect(route('login'));
});

test('non-crew cannot access crew dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('crew.dashboard'))
        ->assertStatus(403);
});
