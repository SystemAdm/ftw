<?php

use App\enums\RolesEnum;
use App\models\BuildingInside;
use App\models\User;
use Database\seeders\RoleSeeder;
use Illuminate\Support\Facades\Crypt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('guests cannot access open page', function () {
    $this->get('/admin/open')->assertRedirect(route('login'));
});

test('admin can access open page', function () {
    $user = User::factory()->create();
    $user->assignRole(RolesEnum::ADMIN->value); // Assuming spatie/laravel-permission is used

    $this->actingAs($user)->get('/admin/open')->assertOk();
});

test('it can check in a user with valid QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RolesEnum::ADMIN->value);

    $user = User::factory()->create(['name' => 'John Doe']);
    $encryptedId = Crypt::encryptString((string) $user->id);

    $this->actingAs($admin)
        ->from('/admin/open')
        ->post('/admin/open', ['code' => $encryptedId])
        ->assertRedirect('/admin/open')
        ->assertSessionHas('status', 'John Doe has entered the building.');

    $this->assertDatabaseHas('building_inside', [
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('building_logs', [
        'user_id' => $user->id,
        'action' => 'in',
    ]);
});

test('it can check out a user with valid QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RolesEnum::ADMIN->value);

    $user = User::factory()->create(['name' => 'Jane Doe']);
    BuildingInside::create(['user_id' => $user->id, 'entered_at' => now()]);

    $encryptedId = Crypt::encryptString((string) $user->id);

    $this->actingAs($admin)
        ->from('/admin/open')
        ->post('/admin/open', ['code' => $encryptedId])
        ->assertRedirect('/admin/open')
        ->assertSessionHas('status', 'Jane Doe has left the building.');

    $this->assertDatabaseMissing('building_inside', [
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('building_logs', [
        'user_id' => $user->id,
        'action' => 'out',
    ]);
});

test('it returns error for invalid QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RolesEnum::ADMIN->value);

    $this->actingAs($admin)
        ->from('/admin/open')
        ->post('/admin/open', ['code' => 'invalid-code'])
        ->assertRedirect('/admin/open')
        ->assertSessionHasErrors(['code' => 'Invalid QR code.']);
});
