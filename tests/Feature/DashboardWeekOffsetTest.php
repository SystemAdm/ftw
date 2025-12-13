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

    $response = $this->get(route('dashboard', ['week' => 1]), [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertJson(fn ($json) => $json
        ->where('component', 'Dashboard')
        ->where('props.week', 1)
        ->where('props.days.0.date', $nextWeekStart->toDateString())
        ->where('props.days.0.weekday', $nextWeekStart->dayOfWeek)
        ->etc()
    );
});

it('coerces negative week values to zero', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = now()->startOfDay();

    $response = $this->get(route('dashboard', ['week' => -3]), [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertJson(fn ($json) => $json
        ->where('component', 'Dashboard')
        ->where('props.week', 0)
        ->where('props.days.0.date', $today->toDateString())
        ->etc()
    );
});
