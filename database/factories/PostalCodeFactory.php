<?php

namespace Database\factories;

use App\models\PostalCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostalCodeFactory extends Factory
{
    protected $model = PostalCode::class;

    public function definition(): array
    {
        return [
            'postal_code' => fake()->randomNumber(4, true),
            'city' => fake()->city(),
            'state' => fake()->firstName(),
            'country' => fake()->country(),
            'municipality' => fake()->firstName(),
        ];
    }
}
