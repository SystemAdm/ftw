<?php

use App\Models\User;
use App\Models\Weekday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shifts the dashboard window forward by week offset', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = now()->startOfDay();
    $nextWeekStart = $today->copy()->addDays(7);

    // Create a weekday that matches the next week's start day to verify mapping
    Weekday::factory()->create([
        'weekday' => $nextWeekStart->dayOfWeek,
        'active' => true,
        'name' => 'Next Week Start',
        'description' => 'Shifted window item',
        'event_start' => null,
        'event_end' => null,
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard', ['week' => 1]));

    $response->assertOk();
    $response->assertSee('data-page', false);
    $response->assertSee('Dashboard', false);
});

it('coerces negative week values to zero', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = now()->startOfDay();

    $response = $this->get(route('dashboard', ['week' => -3]));

    $response->assertOk();
    $response->assertSee('data-page', false);
    $response->assertSee('Dashboard', false);
});
