<?php

use App\Models\Location;
use App\Models\PostalCode;
use App\Models\Team;
use App\Models\Weekday;
use App\Models\WeekdayExcluded;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows public teams index', function (): void {
    // Create teams
    Team::factory()->count(3)->create(['active' => true]);
    Team::factory()->create(['active' => false]);

    $response = $this->get('/teams', [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertJson(fn ($json) => $json
        ->where('component', 'teams/index')
        ->has('props.teams.data', 3)
        ->etc()
    );
});

it('shows a public team with upcoming weekdays including location', function (): void {
    // Ensure a postal code exists for location factory FK
    PostalCode::factory()->create(['postal_code' => 1300]);

    $team = Team::factory()->create(['active' => true]);
    $location = Location::factory()->create(['active' => true, 'postal_code' => 1300]);

    $today = now()->startOfDay();
    $tomorrow = $today->copy()->addDay();

    // Weekday for today (active) but excluded today -> should not appear
    $todayW = Weekday::factory()->create([
        'weekday' => $today->dayOfWeek,
        'team_id' => $team->id,
        'location_id' => $location->id,
        'active' => true,
        'name' => 'Today Team Practice',
        'description' => 'Excluded today',
        'event_start' => null,
        'event_end' => null,
        'start_time' => '18:00:00',
        'end_time' => '20:00:00',
    ]);

    WeekdayExcluded::query()->create([
        'weekday_id' => $todayW->id,
        'excluded_date' => $today->toDateString(),
    ]);

    // Weekday for tomorrow (active) -> should appear
    Weekday::factory()->create([
        'weekday' => $tomorrow->dayOfWeek,
        'team_id' => $team->id,
        'location_id' => $location->id,
        'active' => true,
        'name' => 'Tomorrow Team Practice',
        'description' => 'Regular session',
        'event_start' => null,
        'event_end' => null,
        'start_time' => '19:00:00',
        'end_time' => '21:00:00',
    ]);

    $response = $this->get(route('teams.show', $team), [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertJson(fn ($json) => $json
        ->where('component', 'teams/show')
        ->where('props.team.id', $team->id)
        ->whereType('props.upcoming', 'array')
        ->where('props.upcoming.0.date', $tomorrow->toDateString())
        ->where('props.upcoming.0.weekday', $tomorrow->dayOfWeek)
        ->where('props.upcoming.0.name', 'Tomorrow Team Practice')
        ->where('props.upcoming.0.description', 'Regular session')
        ->where('props.upcoming.0.location.id', $location->id)
        ->where('props.upcoming.0.location.name', $location->name)
        ->etc()
    );
});
