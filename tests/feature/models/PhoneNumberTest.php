<?php

use App\Models\PhoneNumber;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a phone number and attaches users via pivot with flags', function (): void {
    $user = User::factory()->create();

    $phone = PhoneNumber::create([
        'e164' => '+491234567890',
        'raw' => '01234 567 890',
    ]);

    $phone->users()->attach($user->id, [
        'primary' => true,
        'verified_at' => now(),
        'verified_by' => $user->id,
    ]);

    expect($phone->users()->count())->toBe(1);
    expect($user->phoneNumbers()->count())->toBe(1);

    assertDatabaseHas('phone_numbers', [
        'id' => $phone->id,
        'e164' => '+491234567890',
    ]);

    assertDatabaseHas('phone_number_user', [
        'phone_number_id' => $phone->id,
        'user_id' => $user->id,
        'primary' => true,
    ]);
});
