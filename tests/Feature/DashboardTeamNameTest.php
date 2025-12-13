<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Weekday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes team name for the preferred weekday on the dashboard days payload', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = now()->startOfDay();
    $todayWeekday = $today->dayOfWeek; // 0..6

    $team = Team::factory()->create(['name' => 'First Team']);

    // Create a Weekday for today with this team, active and within range
    $weekday = Weekday::factory()->create([
        'weekday' => $todayWeekday,
        'active' => true,
        'team_id' => $team->id,
        'name' => 'Today schedule',
        'description' => 'Training session',
        'event_start' => null,
        'event_end' => null,
    ]);

    $response = $this->get(route('dashboard'), [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertJson(fn ($json) => $json
        ->where('component', 'Dashboard')
        ->where('props.days.0.team.id', $team->id)
        ->where('props.days.0.team.name', 'First Team')
        ->etc()
    );
});
