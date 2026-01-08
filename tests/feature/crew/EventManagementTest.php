<?php

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\PostalCode;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    PostalCode::factory()->create(['postal_code' => 1300]);
    PostalCode::factory()->create(['postal_code' => 1353]);
    PostalCode::factory()->create(['postal_code' => 1350]);
    PostalCode::factory()->create(['postal_code' => 3512]);

    setPermissionsTeamId(0);
    Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0, 'guard_name' => 'web']);

    $this->team = Team::factory()->create();
    setPermissionsTeamId($this->team->id);
    Role::firstOrCreate(['name' => RolesEnum::CREW->value, 'team_id' => $this->team->id, 'guard_name' => 'web']);

    $this->crewUser = User::factory()->create();
    $this->crewUser->assignRole(RolesEnum::CREW->value);
    $this->team->users()->attach($this->crewUser, ['role' => RolesEnum::CREW->value]);

    $this->adminUser = User::factory()->create();
    setPermissionsTeamId(0);
    $this->adminUser->assignRole(RolesEnum::ADMIN->value);

    $this->regularUser = User::factory()->create();
});

it('allows crew members to view the events list', function () {
    Event::factory()->count(3)->create(['team_id' => $this->team->id]);

    $response = $this->actingAs($this->crewUser)
        ->get(route('crew.events.index'));

    $response->assertOk();
    $response->assertSee('events');
});

it('allows crew members to view a specific event with details', function () {
    $event = Event::factory()->create(['team_id' => $this->team->id]);
    $attendee = User::factory()->create();
    $event->attendees()->attach($attendee);

    $response = $this->actingAs($this->crewUser)
        ->get(route('crew.events.show', $event));

    $response->assertOk();
});

it('denies access to regular users', function () {
    $response = $this->actingAs($this->regularUser)
        ->get(route('crew.events.index'));

    $response->assertForbidden();
});

it('allows admins to access crew events', function () {
    $response = $this->actingAs($this->adminUser)
        ->get(route('crew.events.index'));

    $response->assertOk();
});
