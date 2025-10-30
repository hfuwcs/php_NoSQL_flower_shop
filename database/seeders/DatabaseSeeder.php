<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Review;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::truncate();
        Product::truncate();
        Review::truncate();

        $users = User::factory(10)->create();

        Product::factory(50)->create()->each(function ($product) use ($users) {
            $reviews = Review::factory(rand(5, 20))->make()->each(function ($review) use ($users) {
                $review->user_id = $users->random()->id;
            });

            $product->reviews()->saveMany($reviews);
        });
    }
}