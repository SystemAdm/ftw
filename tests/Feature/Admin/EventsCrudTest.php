<?php

use App\Models\Event;
use App\Models\Location;
use App\Models\PostalCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create();
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole('admin');

    // Create default postal codes for locations
    PostalCode::firstOrCreate(['postal_code' => 1353], ['city' => 'Bærums Verk']);
    PostalCode::firstOrCreate(['postal_code' => 1300], ['city' => 'Sandvika']);
    PostalCode::firstOrCreate(['postal_code' => 1350], ['city' => 'Lommedalen']);
    PostalCode::firstOrCreate(['postal_code' => 3512], ['city' => 'Hønefoss']);
});

it('can list events', function () {
    Event::factory()->count(3)->create();

    actingAs($this->admin)
        ->get(route('admin.events.index'))
        ->assertOk();
});

it('can list event images', function () {
    Storage::disk('public')->put('events/test1.jpg', 'content');
    Storage::disk('public')->put('events/test2.jpg', 'content');

    actingAs($this->admin)
        ->get(route('admin.events.images'))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['path' => 'events/test1.jpg']);
});

it('can upload event image via manager', function () {
    $file = UploadedFile::fake()->image('new_event.jpg');

    actingAs($this->admin)
        ->post(route('admin.events.images.upload'), [
            'image' => $file,
        ])
        ->assertOk()
        ->assertJsonStructure(['path', 'url']);

    Storage::disk('public')->assertExists('events/'.$file->hashName());
});

it('can create event with uploaded image', function () {
    $location = Location::factory()->create();
    $image = UploadedFile::fake()->image('event.jpg');

    $data = [
        'title' => 'New Event',
        'event_start' => now()->addDay()->format('Y-m-d H:i'),
        'event_end' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
        'status' => 'published',
        'location_id' => $location->id,
        'image' => $image,
    ];

    actingAs($this->admin)
        ->post(route('admin.events.store'), $data)
        ->assertRedirect();

    $event = Event::where('title', 'New Event')->first();
    expect($event->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($event->image_path);
});

it('can create event with existing image path', function () {
    Storage::disk('public')->put('events/existing.jpg', 'content');

    $data = [
        'title' => 'Existing Image Event',
        'event_start' => now()->addDay()->format('Y-m-d H:i'),
        'event_end' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
        'status' => 'published',
        'image_path' => 'events/existing.jpg',
    ];

    actingAs($this->admin)
        ->post(route('admin.events.store'), $data)
        ->assertRedirect();

    $event = Event::where('title', 'Existing Image Event')->first();
    expect($event->image_path)->toBe('events/existing.jpg');
});

it('can update event image', function () {
    $event = Event::factory()->create(['image_path' => 'events/old.jpg']);
    $newImage = UploadedFile::fake()->image('updated.jpg');

    actingAs($this->admin)
        ->post(route('admin.events.update', $event), [
            '_method' => 'patch',
            'title' => 'Updated Title',
            'event_start' => $event->event_start->format('Y-m-d H:i'),
            'event_end' => $event->event_end ? $event->event_end->format('Y-m-d H:i') : now()->addDay()->addHours(2)->format('Y-m-d H:i'),
            'status' => 'published',
            'image' => $newImage,
        ])
        ->assertRedirect();

    $event->refresh();
    expect($event->image_path)->not->toBe('events/old.jpg');
    Storage::disk('public')->assertExists($event->image_path);
});

it('can show create event page', function () {
    Location::factory()->count(2)->create();

    actingAs($this->admin)
        ->get(route('admin.events.create'))
        ->assertOk();
});

it('can store a new event', function () {
    $location = Location::factory()->create();

    $data = [
        'title' => 'Test Event',
        'excerpt' => 'Short desc',
        'description' => 'Longer desc',
        'location_id' => $location->id,
        'event_start' => now()->addDay()->format('Y-m-d H:i'),
        'event_end' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
        'signup_needed' => true,
        'signup_start' => now()->addHours(2)->format('Y-m-d H:i'),
        'signup_end' => now()->addDay()->subHour()->format('Y-m-d H:i'),
        'status' => 'draft',
        'number_of_seats' => 50,
    ];

    actingAs($this->admin)
        ->post(route('admin.events.store'), $data)
        ->assertRedirect();

    $this->assertDatabaseHas('events', [
        'title' => 'Test Event',
        'location_id' => $location->id,
    ]);
});

it('can show an event', function () {
    $event = Event::factory()->create();

    actingAs($this->admin)
        ->get(route('admin.events.show', $event))
        ->assertOk();
});

it('can show edit event page', function () {
    $event = Event::factory()->create();
    Location::factory()->count(2)->create();

    actingAs($this->admin)
        ->get(route('admin.events.edit', $event))
        ->assertOk();
});

it('can update an event', function () {
    $event = Event::factory()->create(['title' => 'Old Title']);

    $data = [
        'title' => 'New Title',
        'event_start' => now()->addDay()->format('Y-m-d H:i'),
        'event_end' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
        'status' => 'published',
    ];

    actingAs($this->admin)
        ->patch(route('admin.events.update', $event), $data)
        ->assertRedirect();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'New Title',
        'status' => 'published',
    ]);
});

it('can soft delete an event', function () {
    $event = Event::factory()->create();

    actingAs($this->admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect();

    $this->assertSoftDeleted($event);
});

it('can restore a deleted event', function () {
    $event = Event::factory()->create(['deleted_at' => now()]);

    actingAs($this->admin)
        ->post(route('admin.events.restore', $event->id))
        ->assertRedirect();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'deleted_at' => null,
    ]);
});

it('can permanently delete an event', function () {
    $event = Event::factory()->create(['deleted_at' => now()]);

    actingAs($this->admin)
        ->delete(route('admin.events.force-destroy', $event->id))
        ->assertRedirect();

    $this->assertDatabaseEmpty('events');
});
