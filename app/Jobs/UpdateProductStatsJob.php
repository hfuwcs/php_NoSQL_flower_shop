<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Review;
use App\Traits\ManagesCacheKeys;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateProductStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Product $product)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('stack')->info("Processing UpdateProductStatsJob for Product ID: {$this->product->id}");

        $stats = Review::where('product_id', $this->product->id)
            ->raw(function ($collection) {
                return $collection->aggregate([
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
            Product::where('_id', $this->product->id)->update([
                'average_rating' => $stats->average_rating,
                'review_count' => $stats->review_count,
            ]);

            $cacheKey = "product:{$this->product->id}";
            Cache::forget($cacheKey);
            Log::channel('stack')->info("Invalidated product cache: {$cacheKey}");
        }
    }
}
