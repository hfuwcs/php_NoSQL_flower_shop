<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(),
            'content' => fake()->text(),
            'upvotes' => 0,
            'downvotes' => 0,
            'comments' => [],
        ];
    }
}