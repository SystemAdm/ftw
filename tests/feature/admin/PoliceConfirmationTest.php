<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\TeamSeeder::class);
    setPermissionsTeamId(0);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'team_id' => 0]);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(RolesEnum::ADMIN->value);
    $this->user = User::factory()->create();
});

test('admin can confirm police report', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.police-confirm', $this->user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->user->refresh()->police_confirmed_at)->not->toBeNull();
});

test('admin can unconfirm police report', function () {
    $this->user->update(['police_confirmed_at' => now()]);

    $this->actingAs($this->admin)
        ->post(route('admin.users.police-unconfirm', $this->user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->user->refresh()->police_confirmed_at)->toBeNull();
});

test('non-admin cannot confirm police report', function () {
    $regularUser = User::factory()->create();

    $this->actingAs($regularUser)
        ->post(route('admin.users.police-confirm', $this->user))
        ->assertForbidden();

    expect($this->user->refresh()->police_confirmed_at)->toBeNull();
});
