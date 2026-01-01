<?php

use App\Models\Event;
use App\Models\EventLog;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates an event and relates users via attendees and logs', function (): void {
    $user = User::factory()->create();

    $event = Event::create([
        'title' => 'My Event',
        'event_start' => now()->addDay(),
    ]);

    // Attach attendee through relation
    $event->attendees()->attach($user->id);

    // Create a related log
    $log = EventLog::create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'action' => 'in',
    ]);

    expect($event->attendees()->count())->toBe(1);
    expect($event->logs()->count())->toBe(1);
    expect($log->event->is($event))->toBeTrue();

    assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'My Event',
    ]);
});
