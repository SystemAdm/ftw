<?php

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\User;
use App\Models\Weekday;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => 0]);
});

it('scopes weekdays to user teams', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    $user = User::factory()->create();
    $user->teams()->attach($team1, ['role' => RolesEnum::CREW->value, 'status' => 'approved']);

    // Give global crew role so middleware passes
    setPermissionsTeamId(0);
    $user->assignRole(RolesEnum::CREW->value);

    Weekday::factory()->create(['team_id' => $team1->id, 'name' => 'Team 1 Weekday']);
    Weekday::factory()->create(['team_id' => $team2->id, 'name' => 'Team 2 Weekday']);

    $this->actingAs($user)
        ->get(route('crew.weekdays.index'))
        ->assertOk()
        ->assertSee('Team 1 Weekday')
        ->assertDontSee('Team 2 Weekday');
});

it('allows global admin to see all weekdays in crew section', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    $admin = User::factory()->create();
    setPermissionsTeamId(0);
    $admin->assignRole(RolesEnum::ADMIN->value);

    Weekday::factory()->create(['team_id' => $team1->id, 'name' => 'Team 1 Weekday']);
    Weekday::factory()->create(['team_id' => $team2->id, 'name' => 'Team 2 Weekday']);

    $this->actingAs($admin)
        ->get(route('crew.weekdays.index'))
        ->assertOk()
        ->assertSee('Team 1 Weekday')
        ->assertSee('Team 2 Weekday');
});

it('prevents managing weekdays of other teams', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    $user = User::factory()->create();
    $user->teams()->attach($team1, ['role' => RolesEnum::CREW->value, 'status' => 'approved']);

    setPermissionsTeamId(0);
    $user->assignRole(RolesEnum::CREW->value);

    $weekday = Weekday::factory()->create(['team_id' => $team2->id]);

    $this->actingAs($user)
        ->get(route('crew.weekdays.show', $weekday))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('crew.weekdays.update', $weekday), [
            'name' => 'Hack',
            'weekday' => 1,
            'week_type' => 'all',
            'month_occurrence' => 'all',
            'team_id' => $team1->id,
            'start_time' => '18:00',
            'end_time' => '21:00',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('crew.weekdays.destroy', $weekday))
        ->assertForbidden();
});

it('allows creating, updating and deleting weekdays for own teams', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $user->teams()->attach($team, ['role' => RolesEnum::CREW->value, 'status' => 'approved']);

    setPermissionsTeamId(0);
    $user->assignRole(RolesEnum::CREW->value);

    // Create
    $this->actingAs($user)
        ->post(route('crew.weekdays.store'), [
            'name' => 'My Team Weekday',
            'weekday' => 1,
            'week_type' => 'odd',
            'month_occurrence' => 'first',
            'team_id' => $team->id,
            'start_time' => '18:00',
            'end_time' => '21:00',
            'active' => true,
        ])
        ->assertRedirect();

    $weekday = Weekday::where('name', 'My Team Weekday')->firstOrFail();
    expect($weekday->team_id)->toBe($team->id);
    expect($weekday->week_type)->toBe('odd');
    expect($weekday->month_occurrence)->toBe('first');

    // Update
    $this->actingAs($user)
        ->patch(route('crew.weekdays.update', $weekday), [
            'name' => 'Updated Name',
            'weekday' => 1,
            'week_type' => 'even',
            'month_occurrence' => 'last',
            'team_id' => $team->id,
            'start_time' => '18:00',
            'end_time' => '21:00',
            'active' => true,
        ])
        ->assertRedirect();

    expect($weekday->fresh()->name)->toBe('Updated Name');
    expect($weekday->fresh()->week_type)->toBe('even');
    expect($weekday->fresh()->month_occurrence)->toBe('last');

    // Delete
    $this->actingAs($user)
        ->delete(route('crew.weekdays.destroy', $weekday))
        ->assertRedirect();

    $this->assertDatabaseMissing('weekdays', ['id' => $weekday->id]);
});
