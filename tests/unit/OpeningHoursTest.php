<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Models\Weekday;
use App\Support\OpeningHours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningHoursTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_multiple_entries_for_the_same_day(): void
    {
        $sh = Team::factory()->create(['slug' => 'SH', 'name' => 'Spillhuset']);
        $fs = Team::factory()->create(['slug' => 'FS', 'name' => 'Flisespikkeriet']);

        $todayWeekday = now()->dayOfWeek;

        Weekday::factory()->create([
            'weekday' => $todayWeekday,
            'team_id' => $sh->id,
            'name' => 'SH Shift',
            'active' => true,
        ]);

        Weekday::factory()->create([
            'weekday' => $todayWeekday,
            'team_id' => $fs->id,
            'name' => 'FS Shift',
            'active' => true,
        ]);

        $service = new OpeningHours;
        $days = $service->getForWeek(0);

        $this->assertCount(7, $days);
        $this->assertTrue($days[0]['has_weekday']);
        $this->assertCount(2, $days[0]['entries']);
        $this->assertEquals('SH Shift', $days[0]['entries'][0]['name']);
        $this->assertEquals('FS Shift', $days[0]['entries'][1]['name']);
        $this->assertEquals('SH', $days[0]['entries'][0]['team']['slug']);
        $this->assertEquals('FS', $days[0]['entries'][1]['team']['slug']);
    }

    public function test_it_filters_by_week_type(): void
    {
        // Force today to be a known Monday in an ODD week (e.g., 2026-01-19 is Monday, week 4)
        // Wait, 2026-01-19 is Monday. Week 4 is EVEN.
        \Carbon\Carbon::setTestNow('2026-01-19'); // Monday, Week 4 (Even)

        $sh = Team::factory()->create(['name' => 'Spillhuset']);

        // This should show up (Even week)
        Weekday::factory()->create([
            'weekday' => 1, // Monday
            'team_id' => $sh->id,
            'name' => 'Even Monday',
            'week_type' => 'even',
            'active' => true,
        ]);

        // This should NOT show up (Odd week)
        Weekday::factory()->create([
            'weekday' => 1, // Monday
            'team_id' => $sh->id,
            'name' => 'Odd Monday',
            'week_type' => 'odd',
            'active' => true,
        ]);

        $service = new OpeningHours;
        $days = $service->getForWeek(0);

        $this->assertCount(1, $days[0]['entries']);
        $this->assertEquals('Even Monday', $days[0]['entries'][0]['name']);

        // Move to next week (Odd week)
        \Carbon\Carbon::setTestNow('2026-01-26'); // Monday, Week 5 (Odd)
        $days = $service->getForWeek(0);
        $this->assertCount(1, $days[0]['entries']);
        $this->assertEquals('Odd Monday', $days[0]['entries'][0]['name']);

        \Carbon\Carbon::setTestNow(); // Reset
    }

    public function test_it_filters_by_month_occurrence(): void
    {
        \Carbon\Carbon::setTestNow('2026-01-05'); // First Monday of Jan 2026

        $sh = Team::factory()->create(['name' => 'Spillhuset']);

        Weekday::factory()->create([
            'weekday' => 1, // Monday
            'team_id' => $sh->id,
            'name' => 'First Monday Only',
            'month_occurrence' => 'first',
            'active' => true,
        ]);

        Weekday::factory()->create([
            'weekday' => 1, // Monday
            'team_id' => $sh->id,
            'name' => 'Last Monday Only',
            'month_occurrence' => 'last',
            'active' => true,
        ]);

        $service = new OpeningHours;
        $days = $service->getForWeek(0);

        $this->assertCount(1, $days[0]['entries']);
        $this->assertEquals('First Monday Only', $days[0]['entries'][0]['name']);

        // Move to last Monday of Jan 2026 (Jan 26)
        \Carbon\Carbon::setTestNow('2026-01-26');
        $days = $service->getForWeek(0);
        $this->assertCount(1, $days[0]['entries']);
        $this->assertEquals('Last Monday Only', $days[0]['entries'][0]['name']);

        \Carbon\Carbon::setTestNow();
    }
}
