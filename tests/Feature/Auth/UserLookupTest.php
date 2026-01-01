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
            ->where('matchType', 'email')
            ->where('formattedPhone', null)
            ->where('invitedBy', null)
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
        'e164' => '+4799999999',
        'raw' => '99999999',
    ]);

    $phone->users()->attach([$withPassword->id, $withoutPassword->id]);

    // Query using a valid number that normalizes to the same E.164
    $res = $this->getJson(route('auth.users.lookup', ['q' => '999 99 999']));

    $res->assertOk()
        ->assertJson(fn ($json) => $json
            ->has('users', 2)
            ->where('matchType', 'phone')
            ->where('formattedPhone', '+4799999999')
            ->where('invitedBy', null)
            ->has('users', fn ($users) => $users
                ->each(fn ($user) => $user->hasAll(['id', 'name', 'email', 'hasGoogle', 'hasPassword']))
            )
        );

    $data = $res->json('users');
    $byId = collect($data)->keyBy('id');
    expect($byId[$withPassword->id]['hasPassword'])->toBeTrue();
    expect($byId[$withoutPassword->id]['hasPassword'])->toBeFalse();
});

it('finds users by local norwegian phone number format', function () {
    $u = User::factory()->create();
    $phone = PhoneNumber::create([
        'e164' => '+4799999999',
        'raw' => '999 99 999',
    ]);
    $phone->users()->attach($u->id);

    // Search with local format
    $res = $this->getJson(route('auth.users.lookup', ['q' => '99999999']));

    $res->assertOk()
        ->assertJson(fn ($json) => $json
            ->has('users', 1)
            ->where('matchType', 'phone')
            ->where('formattedPhone', '+4799999999')
            ->where('invitedBy', null)
            ->where('users.0.id', $u->id)
        );
});

it('finds users by international phone number format', function () {
    $u = User::factory()->create();
    $phone = PhoneNumber::create([
        'e164' => '+491701234567',
        'raw' => '0170 1234567',
    ]);
    $phone->users()->attach($u->id);

    // Search with international format
    $res = $this->getJson(route('auth.users.lookup', ['q' => '+49 170 1234567']));

    $res->assertOk()
        ->assertJson(fn ($json) => $json
            ->has('users', 1)
            ->where('matchType', 'phone')
            ->where('formattedPhone', '+491701234567')
            ->where('invitedBy', null)
            ->where('users.0.id', $u->id)
        );
});

it('falls back to username if phone number is invalid', function () {
    $u = User::factory()->create(['username' => 'testuser123']);

    // '123' is not a valid phone number for NO or AUTO
    $res = $this->getJson(route('auth.users.lookup', ['q' => 'testuser']));

    $res->assertOk()
        ->assertJson(fn ($json) => $json
            ->has('users', 1)
            ->where('matchType', 'username')
            ->where('formattedPhone', null)
            ->where('invitedBy', null)
            ->where('users.0.id', $u->id)
        );
});

it('returns an error if phone number is invalid and not a likely username', function () {
    // A number with too many digits should fail validation if it's clearly intended to be a phone number
    // or if we want to be strict about digits-only input
    $res = $this->getJson(route('auth.users.lookup', ['q' => '9999999999999999999']));

    $res->assertStatus(422)
        ->assertJsonValidationErrors(['q']);
});
