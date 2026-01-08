<?php

namespace Tests\Feature\Crew;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\PostalCode;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Ensure roles exist
    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => 0, 'guard_name' => 'web']);

    // Ensure postal codes exist for locations
    PostalCode::firstOrCreate(['postal_code' => 1300], ['city' => 'Sandvika', 'municipality' => 'Bærum', 'state' => 'Akershus', 'country' => 'Norway']);
    PostalCode::firstOrCreate(['postal_code' => 1353], ['city' => 'Bærums Verk', 'municipality' => 'Bærum', 'state' => 'Akershus', 'country' => 'Norway']);
    PostalCode::firstOrCreate(['postal_code' => 1350], ['city' => 'Lommedalen', 'municipality' => 'Bærum', 'state' => 'Akershus', 'country' => 'Norway']);
    PostalCode::firstOrCreate(['postal_code' => 3512], ['city' => 'Hønefoss', 'municipality' => 'Ringerike', 'state' => 'Buskerud', 'country' => 'Norway']);
});

test('crew members can see events for their own team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    setPermissionsTeamId($team->id);
    $crewRole = Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id, 'guard_name' => 'web']);
    $user->assignRole($crewRole);
    $team->users()->attach($user, ['role' => RolesEnum::CREW->value]);

    $event = Event::factory()->create(['team_id' => $team->id, 'location_id' => null]);
    $otherEvent = Event::factory()->create(['team_id' => Team::factory()->create()->id, 'location_id' => null]);

    actingAs($user)
        ->get(route('crew.events.index'))
        ->assertOk();
});

test('crew members can create events for their own team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    setPermissionsTeamId($team->id);
    $crewRole = Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id, 'guard_name' => 'web']);
    $user->assignRole($crewRole);
    $team->users()->attach($user, ['role' => RolesEnum::CREW->value]);

    $eventData = [
        'team_id' => $team->id,
        'title' => 'New Team Event',
        'event_start' => now()->addDay()->toDateTimeString(),
        'event_end' => now()->addDay()->addHours(2)->toDateTimeString(),
        'status' => 'published',
    ];

    actingAs($user)
        ->post(route('crew.events.store'), $eventData)
        ->assertRedirect();

    $this->assertDatabaseHas('events', [
        'title' => 'New Team Event',
        'team_id' => $team->id,
    ]);
});

test('crew members cannot create events for teams they are not in', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();

    setPermissionsTeamId($team->id);
    $crewRole = Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id, 'guard_name' => 'web']);
    $user->assignRole($crewRole);
    $team->users()->attach($user, ['role' => RolesEnum::CREW->value]);

    $eventData = [
        'team_id' => $otherTeam->id,
        'title' => 'Illegal Event',
        'event_start' => now()->addDay()->toDateTimeString(),
        'event_end' => now()->addDay()->addHours(2)->toDateTimeString(),
        'status' => 'published',
    ];

    actingAs($user)
        ->post(route('crew.events.store'), $eventData)
        ->assertForbidden();
});

test('crew members can update events for their own team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    setPermissionsTeamId($team->id);
    $crewRole = Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id, 'guard_name' => 'web']);
    $user->assignRole($crewRole);
    $team->users()->attach($user, ['role' => RolesEnum::CREW->value]);

    $event = Event::factory()->create([
        'team_id' => $team->id,
        'location_id' => null,
        'event_start' => now()->addDay(),
        'event_end' => now()->addDay()->addHour(),
    ]);

    actingAs($user)
        ->patch(route('crew.events.update', $event), [
            'team_id' => $team->id,
            'title' => 'Updated Title',
            'event_start' => $event->event_start->toDateTimeString(),
            'event_end' => $event->event_end->toDateTimeString(),
            'status' => 'published',
        ])
        ->assertRedirect();

    expect($event->fresh()->title)->toBe('Updated Title');
});

test('crew members cannot update events of other teams', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();

    setPermissionsTeamId($team->id);
    $crewRole = Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $team->id, 'guard_name' => 'web']);
    $user->assignRole($crewRole);
    $team->users()->attach($user, ['role' => RolesEnum::CREW->value]);

    $event = Event::factory()->create([
        'team_id' => $otherTeam->id,
        'location_id' => null,
        'event_start' => now()->addDay(),
        'event_end' => now()->addDay()->addHour(),
    ]);

    actingAs($user)
        ->patch(route('crew.events.update', $event), [
            'team_id' => $otherTeam->id,
            'title' => 'Updated Title',
            'event_start' => $event->event_start->toDateTimeString(),
            'event_end' => $event->event_end->toDateTimeString(),
            'status' => 'published',
        ])
        ->assertForbidden();
});

test('global admins can see all events', function () {
    setPermissionsTeamId(0);
    $user = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0, 'guard_name' => 'web']);
    $user->assignRole($adminRole);

    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    Event::factory()->create(['team_id' => $team1->id, 'location_id' => null]);
    Event::factory()->create(['team_id' => $team2->id, 'location_id' => null]);

    actingAs($user)
        ->get(route('crew.events.index'))
        ->assertOk();
});
