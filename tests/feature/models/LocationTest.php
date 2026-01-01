<?php

use App\Models\Event;
use App\Models\Location;
use App\Models\PostalCode;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a location and relates to postal code and events', function (): void {
    $pc = PostalCode::create([
        'postal_code' => 90210,
        'city' => 'Beverly Hills',
        'state' => 'CA',
        'country' => 'US',
        'county' => 'Los Angeles',
    ]);

    $loc = Location::create([
        'postal_code' => $pc->postal_code,
        'name' => 'Community Center',
        'active' => true,
        'street_address' => 'Main St',
        'street_number' => '123',
    ]);

    // Event referencing the location
    $event = Event::create([
        'title' => 'Town Hall',
        'location_id' => $loc->id,
        'event_start' => now()->addWeek(),
    ]);

    expect($loc->postalCode)->not->toBeNull();
    expect($loc->postalCode->postal_code)->toBe($pc->postal_code);
    expect($loc->events()->count())->toBe(1);
    expect($event->location->is($loc))->toBeTrue();

    assertDatabaseHas('locations', [
        'id' => $loc->id,
        'name' => 'Community Center',
        'postal_code' => 90210,
        'active' => true,
    ]);
});
