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

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('data-page', false);
    $response->assertSee('Dashboard', false);
});
