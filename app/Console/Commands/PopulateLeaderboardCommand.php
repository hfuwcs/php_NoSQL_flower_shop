<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class PopulateLeaderboardCommand extends Command
{
    protected $signature = 'app:populate-leaderboard';
    protected $description = 'Populates the product leaderboard with data from all existing products.';

    public function handle()
    {
        $this->info('Starting to populate the product leaderboard...');
        $leaderboardKey = 'leaderboard:products:top_rated';

        Redis::del($leaderboardKey);

        Product::select(['_id', 'average_rating'])
            ->where('review_count', '>', 0)
            ->chunk(200, function ($products) use ($leaderboardKey) {
                $payload = [];
                foreach ($products as $product) {
                    $payload[$product->id] = (float) $product->average_rating;
                }

                if (!empty($payload)) {
                    Redis::zadd($leaderboardKey, $payload);
                }
                $this->output->write('.');
            });

        $this->info("\nLeaderboard population complete.");
        return 0;
    }
}