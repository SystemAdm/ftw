<?php

namespace Database\factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->optional()->paragraph(),
            'active' => $this->faker->boolean(70),
            'logo' => $this->faker->imageUrl,
        ];
    }
}
