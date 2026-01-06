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
}
