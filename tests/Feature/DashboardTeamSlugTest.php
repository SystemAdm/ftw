<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Weekday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes team slug for today in dashboard payload', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = now()->startOfDay();
    $todayWeekday = $today->dayOfWeek; // 0..6

    $team = Team::factory()->create([
        'slug' => 'first-team',
        'name' => 'First Team',
    ]);

    Weekday::factory()->create([
        'weekday' => $todayWeekday,
        'active' => true,
        'team_id' => $team->id,
        'name' => 'Today Assignment',
        'description' => null,
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
        ->where('props.days.0.date', $today->toDateString())
        ->where('props.days.0.weekday', $todayWeekday)
        ->where('props.days.0.team.slug', 'first-team')
        ->etc()
    );
});
