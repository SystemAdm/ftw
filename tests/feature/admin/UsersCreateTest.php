<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can access create user page', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(RolesEnum::ADMIN->value);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.create'));

    $response->assertOk();
});

test('admin can create a new user', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(RolesEnum::ADMIN->value);

    $userData = [
        'given_name' => 'John',
        'middle_name' => 'D.',
        'family_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'birthday' => '1990-01-01',
        'postal_code' => '1234',
    ];

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), $userData);

    $user = User::where('email', 'john.doe@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->given_name)->toBe('John');
    expect($user->family_name)->toBe('Doe');
    expect($user->name)->toBe('John D. Doe');
    expect($user->email_verified_at)->not->toBeNull();

    $response->assertRedirect(route('admin.users.show', $user));
});

test('non-admin cannot create a new user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    // No admin role

    $userData = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'birthday' => '1990-01-01',
        'postal_code' => '1234',
    ];

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), $userData);

    $response->assertForbidden();
});
