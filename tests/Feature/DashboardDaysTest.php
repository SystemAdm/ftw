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

    $response = $this->get(route('dashboard'), [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertOk();

    $response->assertJson(fn ($json) => $json
        ->where('component', 'Dashboard')
        ->whereType('props.days', 'array')
        ->has('props.days', 7)
        ->where('props.days.0.date', $today->toDateString())
        ->where('props.days.0.weekday', $todayWeekday)
        ->where('props.days.0.has_weekday', false)
        ->where('props.days.0.is_excluded', true)
        ->where('props.days.0.weekday_label', $today->translatedFormat('l'))
        ->where('props.days.0.name', 'Today shift')
        ->where('props.days.0.description', 'But excluded today')
        ->where('props.days.0.start_time', '10:00')
        ->where('props.days.0.end_time', '11:00')
        ->where('props.days.1.date', $tomorrow->toDateString())
        ->where('props.days.1.weekday', $tomorrowWeekday)
        ->where('props.days.1.has_weekday', true)
        ->where('props.days.1.is_excluded', false)
        ->where('props.days.1.weekday_label', $tomorrow->translatedFormat('l'))
        ->where('props.days.1.name', 'Tomorrow shift')
        ->where('props.days.1.description', 'Regular workday')
        ->where('props.days.1.start_time', '18:00')
        ->where('props.days.1.end_time', '21:00')
        ->etc()
    );
});
