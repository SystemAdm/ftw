<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Weekday;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeekdayFactory extends Factory
{
    protected $model = Weekday::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->boolean(60) ? $this->faker->sentence(8) : null,
            'weekday' => $this->faker->numberBetween(0, 6),
            'start_time' => $this->faker->time('H:i:00'),
            'end_time' => $this->faker->time('H:i:00'),
            'active' => $this->faker->boolean(70),
            'team_id' => Team::factory(),
        ];
    }
}
