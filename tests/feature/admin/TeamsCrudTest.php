<?php

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(RolesEnum::ADMIN->value);
});

test('admin teams index page is displayed', function () {
    $response = $this
        ->actingAs($this->admin)
        ->get('/admin/teams');

    $response->assertOk();
    $response->assertSee(trans('pages.settings.teams.title'));
});

test('can create a team', function () {
    $response = $this
        ->actingAs($this->admin)
        ->post('/admin/teams', [
            'name' => 'New Team',
            'slug' => 'new-team',
            'description' => 'Team description',
            'active' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('teams', ['name' => 'New Team']);
});

test('can update a team', function () {
    $team = Team::factory()->create();

    $response = $this
        ->actingAs($this->admin)
        ->put("/admin/teams/{$team->id}", [
            'name' => 'Updated Team Name',
            'slug' => 'updated-team-name',
            'active' => false,
        ]);

    $response->assertRedirect();
    expect($team->refresh()->name)->toBe('Updated Team Name');
    expect((bool) $team->active)->toBeFalse();
});

test('can soft delete, restore, and force delete a team', function () {
    $team = Team::factory()->create();

    // Soft delete
    $response = $this
        ->actingAs($this->admin)
        ->delete("/admin/teams/{$team->id}");

    $response->assertRedirect('/admin/teams');
    $this->assertSoftDeleted('teams', ['id' => $team->id]);

    // Restore
    $response = $this
        ->actingAs($this->admin)
        ->post("/admin/teams/{$team->id}/restore");

    $response->assertRedirect('/admin/teams');
    $this->assertDatabaseHas('teams', ['id' => $team->id, 'deleted_at' => null]);

    // Force delete
    $this->delete("/admin/teams/{$team->id}"); // soft delete again
    $response = $this
        ->actingAs($this->admin)
        ->delete("/admin/teams/{$team->id}/force");

    $response->assertRedirect('/admin/teams');
    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('admin can manage team members', function () {
    $team = Team::factory()->create();
    $member = User::factory()->create();

    // Create team roles
    setPermissionsTeamId($team->id);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id]);
    Role::firstOrCreate(['name' => RolesEnum::BOARD_CHAIRMAN->value, 'team_id' => $team->id]);

    // Attach as pending member
    $team->users()->attach($member, ['role' => RolesEnum::CREW->value, 'status' => 'pending']);

    // Admin approves member
    setPermissionsTeamId(0);
    $response = $this
        ->actingAs($this->admin)
        ->post("/admin/teams/{$team->id}/members/{$member->id}", [
            'role' => RolesEnum::CREW->value,
            'status' => 'approved',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'status' => 'approved',
        'role' => RolesEnum::CREW->value,
    ]);

    // Admin changes role to Board Chairman
    setPermissionsTeamId(0);
    $response = $this
        ->actingAs($this->admin)
        ->post("/admin/teams/{$team->id}/members/{$member->id}", [
            'role' => RolesEnum::BOARD_CHAIRMAN->value,
            'status' => 'approved',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'role' => RolesEnum::BOARD_CHAIRMAN->value,
    ]);

    // Admin removes member
    setPermissionsTeamId(0);
    $response = $this
        ->actingAs($this->admin)
        ->delete("/admin/teams/{$team->id}/members/{$member->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
    ]);
});
