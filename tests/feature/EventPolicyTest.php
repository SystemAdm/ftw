<?php

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\PostalCode;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed some postal codes that are used by LocationFactory
    PostalCode::factory()->createMany([
        ['postal_code' => 1300],
        ['postal_code' => 1353],
        ['postal_code' => 1350],
        ['postal_code' => 3512],
    ]);
});

it('allows global admins to do everything', function () {
    $admin = User::factory()->create();

    // Assign global admin role (Owner or Admin with team_id 0)
    $role = Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $admin->assignRole($role);

    $event = Event::factory()->create();

    expect($admin->can('viewAny', Event::class))->toBeTrue()
        ->and($admin->can('view', $event))->toBeTrue()
        ->and($admin->can('create', Event::class))->toBeTrue()
        ->and($admin->can('update', $event))->toBeTrue()
        ->and($admin->can('delete', $event))->toBeTrue()
        ->and($admin->can('restore', $event))->toBeTrue()
        ->and($admin->can('forceDelete', $event))->toBeTrue();
});

it('allows crew members to manage events of their team', function () {
    $team = Team::factory()->create();
    $crew = User::factory()->create();

    // Join team
    $crew->teams()->attach($team->id, ['role' => RolesEnum::CREW->value, 'status' => 'approved']);

    $event = Event::factory()->create(['team_id' => $team->id]);

    expect($crew->can('viewAny', Event::class))->toBeTrue()
        ->and($crew->can('view', $event))->toBeTrue()
        ->and($crew->can('update', $event))->toBeTrue()
        ->and($crew->can('delete', $event))->toBeTrue();
});

it('denies crew members from managing events of other teams', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();
    $crew = User::factory()->create();

    $crew->teams()->attach($team1->id, ['role' => RolesEnum::CREW->value, 'status' => 'approved']);

    $eventOfTeam2 = Event::factory()->create(['team_id' => $team2->id]);

    expect($crew->can('view', $eventOfTeam2))->toBeFalse()
        ->and($crew->can('update', $eventOfTeam2))->toBeFalse()
        ->and($crew->can('delete', $eventOfTeam2))->toBeFalse();
});

it('denies crew members from restoring or force deleting', function () {
    $team = Team::factory()->create();
    $crew = User::factory()->create();
    $crew->teams()->attach($team->id, ['role' => RolesEnum::CREW->value, 'status' => 'approved']);

    $event = Event::factory()->create(['team_id' => $team->id]);

    expect($crew->can('restore', $event))->toBeFalse()
        ->and($crew->can('forceDelete', $event))->toBeFalse();
});

it('denies regular users from any management', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    expect($user->can('viewAny', Event::class))->toBeFalse()
        ->and($user->can('view', $event))->toBeFalse()
        ->and($user->can('create', Event::class))->toBeFalse()
        ->and($user->can('update', $event))->toBeFalse()
        ->and($user->can('delete', $event))->toBeFalse();
});

it('allows signup for published events', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'published']);

    expect($user->can('signup', $event))->toBeTrue();
});

it('denies signup for unpublished events', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['status' => 'draft']);

    expect($user->can('signup', $event))->toBeFalse();
});
