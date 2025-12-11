<?php

use App\Models\EventLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates an event log for a user and relates correctly', function (): void {
    $user = User::factory()->create();

    // Minimal event row to satisfy FK
    $eventId = DB::table('events')->insertGetId([
        'title' => 'Test Event',
        'event_start' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $log = EventLog::create([
        'event_id' => $eventId,
        'user_id' => $user->id,
        'action' => 'in',
    ]);

    expect($log->user->is($user))->toBeTrue();
    expect($user->logs()->count())->toBe(1);

    assertDatabaseHas('event_logs', [
        'id' => $log->id,
        'event_id' => $eventId,
        'user_id' => $user->id,
        'action' => 'in',
    ]);
});
