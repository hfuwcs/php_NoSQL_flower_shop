<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use MongoDB\BSON\ObjectId;

class UpdateProductStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Product $product)
    {
    }

    public function handle(): void
    {
        Log::channel('stack')->info("Processing UpdateProductStatsJob for Product ID: {$this->product->id}");

        $productId = (string) $this->product->id;
        $productObjectId = new ObjectId($productId);

        $stats = Review::raw(function ($collection) use ($productObjectId) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'product_id' => $productObjectId
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$product_id',
                        'average_rating' => ['$avg' => '$rating'],
                        'review_count' => ['$sum' => 1],
                    ],
                ],
            ]);
        })->first();

        if ($stats) {
            $this->product->refresh();
            $this->product->update([
                'average_rating' => $stats->average_rating,
                'review_count' => $stats->review_count,
            ]);

            $leaderboardKey = 'leaderboard:products:top_rated';
            $newAverageRating = (float) ($stats->average_rating ?? 0);

            Redis::zadd($leaderboardKey, $newAverageRating, $productId);
            Log::channel('stack')->info("Updated leaderboard '{$leaderboardKey}' for Product ID: {$productId} with score: {$newAverageRating}");

            Cache::forget("product:{$productId}");
            Cache::forget("product:basic:{$productId}");
            Log::channel('stack')->info("Invalidated product cache: {$productId}");
        } else {
            $this->product->refresh();
            $this->product->update([
                'average_rating' => 0,
                'review_count' => 0,
            ]);
            
            Log::channel('stack')->info("No reviews found for Product ID: {$productId}, reset stats to 0");
        }
    }
}
