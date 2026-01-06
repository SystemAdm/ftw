<?php

use App\Enums\BirthdayVisibility;
use App\Enums\PostalCodeVisibility;
use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public profile page can be rendered', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    $response = $this->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertSee('John Doe');
});

test('public profile hides email and phone by default', function () {
    $user = User::factory()->create([
        'email' => 'private@example.com',
        'email_public' => false,
        'phone_public' => false,
    ]);

    $phone = PhoneNumber::create(['e164' => '+4799999999']);
    $user->phoneNumbers()->attach($phone->id, ['primary' => true]);

    $response = $this->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertDontSee('private@example.com');
    $response->assertDontSee('+4799999999');
});

test('public profile shows email and phone when public', function () {
    $user = User::factory()->create([
        'email' => 'public@example.com',
        'email_public' => true,
        'phone_public' => true,
    ]);

    $phone = PhoneNumber::create(['e164' => '+4799999999']);
    $user->phoneNumbers()->attach($phone->id, ['primary' => true]);

    $response = $this->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertSee('public@example.com');
    $response->assertSee('+4799999999');
});

test('user can see their own private info on their profile', function () {
    $user = User::factory()->create([
        'email' => 'own@example.com',
        'email_public' => false,
        'phone_public' => false,
    ]);

    $phone = PhoneNumber::create(['e164' => '+4799999999']);
    $user->phoneNumbers()->attach($phone->id, ['primary' => true]);

    $response = $this->actingAs($user)->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertSee('own@example.com');
    $response->assertSee('+4799999999');
});

test('user can update their profile visibility settings', function () {
    $postal = \App\Models\PostalCode::create([
        'postal_code' => 1337,
        'city' => 'Test City',
        'country' => 'NO',
    ]);
    $user = User::factory()->create([
        'email_public' => false,
        'phone_public' => false,
    ]);

    $response = $this->actingAs($user)->patch('/settings/profile', [
        'birthday' => '1990-01-01',
        'birthday_visibility' => BirthdayVisibility::Birthdate->value,
        'postal_code' => 1337,
        'postal_code_visibility' => PostalCodeVisibility::PostalCode->value,
        'email_public' => true,
        'phone_public' => true,
        'name_public' => false,
        'about' => 'My bio',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $user->refresh();

    expect($user->email_public)->toBeTrue();
    expect($user->phone_public)->toBeTrue();
    expect($user->name_public)->toBeFalse();
    expect($user->birthday_visibility)->toBe(BirthdayVisibility::Birthdate);
    expect($user->about)->toBe('My bio');
});

test('public profile respects name visibility', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'name_public' => false,
    ]);

    $response = $this->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertSee('John');
    $response->assertDontSee('Doe');
});

test('public profile respects birthday visibility', function () {
    $user = User::factory()->create([
        'birthday' => '1990-05-28', // Use 28 to avoid common numbers like 15 (found in icons)
        'birthday_visibility' => BirthdayVisibility::Birthyear,
    ]);

    $response = $this->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertSee('1990');
    $response->assertDontSee('28');

    $user->update(['birthday_visibility' => BirthdayVisibility::Age]);
    $response = $this->get(route('profile.show', $user));
    // The localized version of 'years' might be used
    $response->assertSee($user->birthday->age);

    $user->update(['birthday_visibility' => BirthdayVisibility::Off]);
    $response = $this->get(route('profile.show', $user));
    $response->assertDontSee('1990');
});

test('user can see their roles on their own profile', function () {
    $user = User::factory()->create();
    $team = \App\Models\Team::factory()->create(['name' => 'Alpha Team']);

    // Global role
    $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'Admin', 'guard_name' => 'web', 'team_id' => 0]);
    \DB::table('model_has_roles')->insert([
        'role_id' => $adminRole->id,
        'model_type' => User::class,
        'model_id' => $user->id,
        'team_id' => 0,
    ]);

    // Team role
    setPermissionsTeamId($team->id);
    $crewRole = \Spatie\Permission\Models\Role::create(['name' => 'Crew', 'guard_name' => 'web', 'team_id' => $team->id]);
    \DB::table('model_has_roles')->insert([
        'role_id' => $crewRole->id,
        'model_type' => User::class,
        'model_id' => $user->id,
        'team_id' => $team->id,
    ]);
    setPermissionsTeamId(0);

    $response = $this->actingAs($user)->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertSee('Admin');
    $response->assertSee('Crew (Alpha Team)');
});

test('other users cannot see roles on a public profile', function () {
    $user = User::factory()->create();
    $visitor = User::factory()->create();

    $uniqueRole = \Spatie\Permission\Models\Role::create(['name' => 'SuperUniqueRole', 'guard_name' => 'web', 'team_id' => 0]);
    \DB::table('model_has_roles')->insert([
        'role_id' => $uniqueRole->id,
        'model_type' => User::class,
        'model_id' => $user->id,
        'team_id' => 0,
    ]);

    $response = $this->actingAs($visitor)->get(route('profile.show', $user));

    $response->assertStatus(200);
    $response->assertDontSee('SuperUniqueRole');
});
