<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'category' => fake()->word(),
            'images' => [fake()->imageUrl(), fake()->imageUrl()],
            'average_rating' => 0,
            'review_count' => 0,
        ];
    }
}