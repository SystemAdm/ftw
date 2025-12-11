<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Weekday;
use App\Models\WeekdayExcluded;

it('shows weekdays index', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get('/admin/weekdays');
    $response->assertSuccessful();
});

it('creates, updates, and deletes a weekday and manages exclusions', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $team = Team::factory()->create();

    // Create
    $this->withSession(['_token' => 'test']);
    $store = $this->post('/admin/weekdays', [
        'weekday' => 1,
        'team_id' => $team->id,
        'active' => true,
        'start_time' => '18:00',
        'end_time' => '21:00',
        '_token' => 'test',
    ]);
    $store->assertRedirect();

    $weekday = Weekday::query()->latest('id')->first();
    expect($weekday)->not->toBeNull();
    expect($weekday->weekday)->toBe(1);
    expect($weekday->team_id)->toBe($team->id);

    // Update
    $this->withSession(['_token' => 'test']);
    $update = $this->put("/admin/weekdays/{$weekday->id}", [
        'weekday' => 5,
        'team_id' => null,
        'active' => false,
        'start_time' => '17:00',
        'end_time' => '20:00',
        '_token' => 'test',
    ]);
    $update->assertRedirect();

    $weekday->refresh();
    expect($weekday->weekday)->toBe(5);
    expect($weekday->team_id)->toBeNull();
    expect($weekday->active)->toBeFalse();

    // Add exclusion
    $this->withSession(['_token' => 'test']);
    $exDate = now()->toDateString();
    $addEx = $this->post("/admin/weekdays/{$weekday->id}/exclusions", [
        'excluded_date' => $exDate,
        '_token' => 'test',
    ]);
    $addEx->assertRedirect();

    $this->assertDatabaseHas('weekday_excluded', [
        'weekday_id' => $weekday->id,
        'excluded_date' => $exDate,
    ]);

    $exclusion = WeekdayExcluded::where('weekday_id', $weekday->id)->where('excluded_date', $exDate)->firstOrFail();

    // Remove exclusion
    $this->withSession(['_token' => 'test']);
    $remEx = $this->delete("/admin/weekdays/{$weekday->id}/exclusions/{$exclusion->id}", ['_token' => 'test']);
    $remEx->assertRedirect();
    $this->assertDatabaseMissing('weekday_excluded', ['id' => $exclusion->id]);

    // Delete weekday
    $this->withSession(['_token' => 'test']);
    $del = $this->delete("/admin/weekdays/{$weekday->id}", ['_token' => 'test']);
    $del->assertRedirect('/admin/weekdays');
    $this->assertDatabaseMissing('weekdays', ['id' => $weekday->id]);
});
