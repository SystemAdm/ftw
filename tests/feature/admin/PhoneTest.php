<?php

use App\Enums\RolesEnum;
use App\Models\PhoneNumber;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => RolesEnum::ADMIN->value, 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(RolesEnum::ADMIN->value);
});

it('can list phone numbers', function () {
    PhoneNumber::factory()->count(3)->create();

    actingAs($this->admin)
        ->get(route('admin.phone.index'))
        ->assertOk();
});

it('can create a phone number', function () {
    actingAs($this->admin)
        ->post(route('admin.phone.store'), [
            'e164' => '+4711111111',
            'raw' => '111 11 111',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('phone_numbers', [
        'e164' => '+4711111111',
    ]);
});

it('can associate a user with a phone number', function () {
    $phone = PhoneNumber::factory()->create();
    $user = User::factory()->create();

    actingAs($this->admin)
        ->post(route('admin.phone.users.associate', $phone), [
            'user_id' => $user->id,
            'primary' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('phone_number_user', [
        'phone_number_id' => $phone->id,
        'user_id' => $user->id,
        'primary' => true,
    ]);
});

it('can toggle primary status', function () {
    $phone = PhoneNumber::factory()->create();
    $user = User::factory()->create();
    $phone->users()->attach($user->id, ['primary' => false]);

    actingAs($this->admin)
        ->post(route('admin.phone.users.toggle-primary', [$phone, $user]))
        ->assertRedirect();

    $this->assertDatabaseHas('phone_number_user', [
        'phone_number_id' => $phone->id,
        'user_id' => $user->id,
        'primary' => true,
    ]);
});

it('can disassociate a user', function () {
    $phone = PhoneNumber::factory()->create();
    $user = User::factory()->create();
    $phone->users()->attach($user->id);

    actingAs($this->admin)
        ->delete(route('admin.phone.users.disassociate', [$phone, $user]))
        ->assertRedirect();

    $this->assertDatabaseMissing('phone_number_user', [
        'phone_number_id' => $phone->id,
        'user_id' => $user->id,
    ]);
});

it('can delete a phone number', function () {
    $phone = PhoneNumber::factory()->create();

    actingAs($this->admin)
        ->delete(route('admin.phone.destroy', $phone))
        ->assertRedirect();

    $this->assertDatabaseMissing('phone_numbers', [
        'id' => $phone->id,
    ]);
});
