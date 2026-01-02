<?php

namespace Database\factories;

use App\Models\Event;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        $eventStart = fake()->dateTimeBetween('+1 days', '+3 months');

        // Ensure event_end is after start or null sometimes
        $eventEnd = fake()->boolean(80)
            ? fake()->dateTimeBetween($eventStart->format('Y-m-d H:i:s'), '+6 months')
            : null;

        $signupNeeded = fake()->boolean(50);

        // When signup is needed, create a window before the event start
        $signupStart = null;
        $signupEnd = null;
        if ($signupNeeded) {
            $signupStart = fake()->dateTimeBetween('-10 days', $eventStart->format('Y-m-d H:i:s'));
            // Ensure signup_end is between signup_start and event_start
            $signupEnd = fake()->dateTimeBetween($signupStart->format('Y-m-d H:i:s'), $eventStart->format('Y-m-d H:i:s'));
        }

        // Optional age constraints
        $ageMin = fake()->boolean(50) ? fake()->numberBetween(0, 18) : null;
        $ageMax = null;
        if ($ageMin !== null) {
            $ageMax = fake()->boolean(80) ? fake()->numberBetween(max($ageMin, 18), 99) : null;
        } else {
            $ageMax = fake()->boolean(20) ? fake()->numberBetween(18, 99) : null;
        }

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 999999),
            'excerpt' => fake()->optional()->paragraph(),
            'description' => fake()->optional()->paragraphs(asText: true),
            'image_path' => fake()->optional()->imageUrl(1200, 630, 'events', true),

            // Relationship (nullable sometimes)
            'location_id' => fake()->boolean(75) ? Location::factory() : null,

            'event_start' => $eventStart,
            'event_end' => $eventEnd,

            'signup_needed' => $signupNeeded,
            'signup_start' => $signupStart,
            'signup_end' => $signupEnd,

            'age_min' => $ageMin,
            'age_max' => $ageMax,

            'number_of_seats' => fake()->optional()->numberBetween(10, 500),

            'status' => fake()->randomElement(['draft', 'published', 'cancelled', null]),
        ];
    }
}
