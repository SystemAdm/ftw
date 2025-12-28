<?php

use App\Models\Event;
use App\Models\PostalCode;
use App\Models\User;

beforeEach(function () {
    PostalCode::firstOrCreate(['postal_code' => 1353], ['city' => 'Bærums Verk']);
    PostalCode::firstOrCreate(['postal_code' => 1300], ['city' => 'Sandvika']);
    PostalCode::firstOrCreate(['postal_code' => 1350], ['city' => 'Lommedalen']);
    PostalCode::firstOrCreate(['postal_code' => 3512], ['city' => 'Hønefoss']);

    $this->user = User::factory()->create(['birthday' => now()->subYears(20)]);
});

it('can list published events', function () {
    Event::factory()->create(['status' => 'published', 'event_start' => now()->addDay()]);
    Event::factory()->create(['status' => 'draft']);

    $response = $this->get(route('events.index'));

    $response->assertOk();
});

it('can show published event', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $response = $this->get(route('events.show', $event));
    $response->assertOk();
});

it('cannot show draft event for guest', function () {
    $event = Event::factory()->create(['status' => 'draft']);

    $this->get(route('events.show', $event))
        ->assertNotFound();
});

it('can signup for an event', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->subDay(),
        'signup_end' => now()->addDay(),
        'number_of_seats' => 10,
    ]);

    $this->actingAs($this->user)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($event->reservations()->where('user_id', $this->user->id)->exists())->toBeTrue();
});

it('can cancel signup for an event', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
    ]);

    $event->reservations()->attach($this->user->id);

    $this->actingAs($this->user)
        ->delete(route('events.cancelSignup', $event))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($event->reservations()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('marks signed up events on index', function () {
    $event = Event::factory()->create(['status' => 'published', 'event_start' => now()->addDay()]);
    $event->reservations()->attach($this->user->id);

    $this->actingAs($this->user)
        ->get(route('events.index'))
        ->assertOk();
});

it('cannot signup if event is full', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->subDay(),
        'signup_end' => now()->addDay(),
        'number_of_seats' => 1,
    ]);

    $anotherUser = User::factory()->create();
    $event->reservations()->attach($anotherUser->id);

    $this->actingAs($this->user)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('error', trans('pages.events.signup.messages.full'));

    expect($event->reservations()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('can signup if seats is unlimited (-1)', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->subDay(),
        'signup_end' => now()->addDay(),
        'number_of_seats' => -1,
    ]);

    $this->actingAs($this->user)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($event->reservations()->where('user_id', $this->user->id)->exists())->toBeTrue();
});

it('cannot signup if seats is 0 (not required)', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->subDay(),
        'signup_end' => now()->addDay(),
        'number_of_seats' => 0,
    ]);

    $this->actingAs($this->user)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('error', trans('pages.events.signup.messages.signup_not_required'));

    expect($event->reservations()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('cannot signup if signup has not started', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->addDay(),
    ]);

    $this->actingAs($this->user)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('error', trans('pages.events.signup.messages.not_started'));
});

it('cannot signup if signup has ended', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->subDays(2),
        'signup_end' => now()->subDay(),
    ]);

    $this->actingAs($this->user)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('error', trans('pages.events.signup.messages.ended'));
});

it('cannot signup if user is too young', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'signup_needed' => true,
        'signup_start' => now()->subDay(),
        'age_min' => 18,
    ]);

    $youngUser = User::factory()->create(['birthday' => now()->subYears(15)]);

    $this->actingAs($youngUser)
        ->post(route('events.signup', $event))
        ->assertRedirect()
        ->assertSessionHas('error', trans('pages.events.signup.messages.too_young', ['age' => 18]));
});
