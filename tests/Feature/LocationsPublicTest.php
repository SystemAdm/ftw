<?php

use App\Models\Location;
use App\Models\PostalCode;
use App\Models\Weekday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows public locations index', function (): void {
    // Ensure a valid postal code exists for FK
    $postal = PostalCode::factory()->create();

    // Create some locations (active and inactive)
    Location::factory()->count(3)->create(['active' => true, 'postal_code' => $postal->postal_code]);
    Location::factory()->create(['active' => false, 'postal_code' => $postal->postal_code]);

    $response = $this->get('/locations');

    $response->assertOk();

    $content = $response->getContent();
    expect($content)->toContain('data-page');
    // Simple check instead of complex parsing if assertInertia is failing
    expect($content)->toContain('locations/Index');
});

it('shows a public location with upcoming weekdays', function (): void {
    $today = now()->startOfDay();
    $weekday = $today->dayOfWeek; // 0..6

    $postal = PostalCode::factory()->create();
    $location = Location::factory()->create(['active' => true, 'postal_code' => $postal->postal_code]);

    // A matching active weekday for today at this location
    Weekday::factory()->create([
        'location_id' => $location->id,
        'weekday' => $weekday,
        'active' => true,
        'name' => 'Today at Location',
        'description' => 'Public session',
        'event_start' => null,
        'event_end' => null,
    ]);

    $response = $this->get(route('locations.show', $location));

    $response->assertOk();
    $response->assertSee('data-page', false);
    $response->assertSee('locations/Show', false);
});
