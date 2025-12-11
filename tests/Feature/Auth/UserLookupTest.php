<?php

use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a single user for exact email match with flags', function () {
    $u = User::factory()->create([
        'email' => 'person@example.com',
        'google_id' => 'google-123',
    ]);

    $res = $this->getJson(route('auth.users.lookup', ['q' => 'person@example.com']));

    $res->assertOk()
        ->assertJson(fn ($json) => $json
            ->has('users', 1)
            ->has('users.0', fn ($user) => $user
                ->where('id', $u->id)
                ->where('name', $u->name)
                ->where('email', $u->email)
                ->where('hasGoogle', true)
                ->where('hasPassword', true)
            )
        );
});

it('returns multiple users for matching phone and sets flags correctly', function () {
    $withPassword = User::factory()->create();
    $withoutPassword = User::factory()->create(['password' => null, 'google_id' => null]);

    $phone = PhoneNumber::create([
        'e164' => '+491234567890',
        'raw' => '01234 567 890',
    ]);

    $phone->users()->attach([$withPassword->id, $withoutPassword->id]);

    // Query using partial digits
    $res = $this->getJson(route('auth.users.lookup', ['q' => '1234567']));

    $res->assertOk()
        ->assertJson(fn ($json) => $json
            ->has('users', 2)
            ->has('users', fn ($users) => $users
                ->each(fn ($user) => $user->hasAll(['id', 'name', 'email', 'hasGoogle', 'hasPassword']))
            )
        );

    $data = $res->json('users');
    $byId = collect($data)->keyBy('id');
    expect($byId[$withPassword->id]['hasPassword'])->toBeTrue();
    expect($byId[$withoutPassword->id]['hasPassword'])->toBeFalse();
});
