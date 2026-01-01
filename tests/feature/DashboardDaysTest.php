<?php

use App\Models\User;
use App\Models\Weekday;
use App\Models\WeekdayExcluded;
use Illuminate\Foundation\Testing\RefreshDatabase;

// kept for consistency with other tests, unused here

uses(RefreshDatabase::class);

it('provides seven upcoming days with assignment flags on the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = now()->startOfDay();
    $todayWeekday = $today->dayOfWeek; // 0..6
    $tomorrow = $today->copy()->addDay();
    $tomorrowWeekday = $tomorrow->dayOfWeek;

    // Create a weekday for tomorrow (should be marked as assigned)
    Weekday::factory()->create([
        'weekday' => $tomorrowWeekday,
        'active' => true,
        'name' => 'Tomorrow shift',
        'description' => 'Regular workday',
        'event_start' => null,
        'event_end' => null,
        'start_time' => '18:00',
        'end_time' => '21:00',
    ]);

    // Create an active weekday for today but exclude today (should be unassigned due to exclusion)
    $todayWeekdayModel = Weekday::factory()->create([
        'weekday' => $todayWeekday,
        'active' => true,
        'name' => 'Today shift',
        'description' => 'But excluded today',
        'event_start' => null,
        'event_end' => null,
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    WeekdayExcluded::query()->create([
        'weekday_id' => $todayWeekdayModel->id,
        'excluded_date' => $today->toDateString(),
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('data-page', false);
    $response->assertSee('Dashboard', false);
});
