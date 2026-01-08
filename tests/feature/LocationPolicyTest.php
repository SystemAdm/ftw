<?php

use App\Enums\RolesEnum;
use App\Models\Location;
use App\Models\PostalCode;
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

    $location = Location::factory()->create();

    expect($admin->can('viewAny', Location::class))->toBeTrue()
        ->and($admin->can('view', $location))->toBeTrue()
        ->and($admin->can('create', Location::class))->toBeTrue()
        ->and($admin->can('update', $location))->toBeTrue()
        ->and($admin->can('delete', $location))->toBeTrue()
        ->and($admin->can('restore', $location))->toBeTrue()
        ->and($admin->can('forceDelete', $location))->toBeTrue();
});

it('denies regular users from any management', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    expect($user->can('viewAny', Location::class))->toBeFalse()
        ->and($user->can('view', $location))->toBeFalse()
        ->and($user->can('create', Location::class))->toBeFalse()
        ->and($user->can('update', $location))->toBeFalse()
        ->and($user->can('delete', $location))->toBeFalse()
        ->and($user->can('restore', $location))->toBeFalse()
        ->and($user->can('forceDelete', $location))->toBeFalse();
});
