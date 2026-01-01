<?php

use App\Models\Event;
use App\Models\PostalCode;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    PostalCode::factory()->create(['postal_code' => 1300]);
    PostalCode::factory()->create(['postal_code' => 1353]);
    PostalCode::factory()->create(['postal_code' => 1350]);
    PostalCode::factory()->create(['postal_code' => 3512]);

    Role::firstOrCreate(['name' => 'crew']);
    Role::firstOrCreate(['name' => 'admin']);
    $this->crewUser = User::factory()->create();
    $this->crewUser->assignRole('crew');

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('admin');

    $this->regularUser = User::factory()->create();
});

it('allows crew members to view the events list', function () {
    Event::factory()->count(3)->create();

    $response = $this->actingAs($this->crewUser)
        ->get(route('crew.events.index'));

    $response->assertOk();
    $response->assertSee('events');
});

it('allows crew members to view a specific event with details', function () {
    $event = Event::factory()->create();
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
