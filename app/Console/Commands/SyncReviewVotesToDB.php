<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SyncReviewVotesToDB extends Command
{
    protected $signature = 'app:sync-review-votes-to-db';

    protected $description = 'Syncs the vote counts from Redis hashes to the MongoDB reviews collection';

    public function handle()
    {
        $this->info('Starting review votes sync...');
        Log::channel('stack')->info('======= Starting SyncReviewVotesToDB =======');

        $prefix = config('database.redis.options.prefix', '');
        Log::channel('stack')->info("Using Redis prefix from config: '{$prefix}'");

        $pattern = $prefix . 'review:votes:*';
        Log::channel('stack')->info("Scanning for keys with pattern: '{$pattern}'");

        // **SỬA LỖI TẠI ĐÂY: Chuyển sang SCAN**
        $voteKeys = [];
        $cursor = "0";
        do {
            // Quét 100 key mỗi lần
            [$cursor, $keys] = Redis::scan($cursor, 'match', $pattern, 'count', 100);
            $voteKeys = array_merge($voteKeys, $keys);
        } while ($cursor !== "0");
        
        Log::channel('stack')->info(count($voteKeys) . " key(s) found after SCAN.");
        if (!empty($voteKeys)) {
            Log::channel('stack')->debug("Keys found: " . implode(', ', $voteKeys));
        }

        if (empty($voteKeys)) {
            $this->info('No review votes to sync. Exiting.');
            Log::channel('stack')->info('======= SyncReviewVotesToDB Finished (No Keys Found) =======');
            return 0;
        }

        $this->info(count($voteKeys) . ' review(s) found in Redis to sync.');

        foreach ($voteKeys as $redisKeyWithPrefix) {
            Log::channel('stack')->info("Processing Redis key: {$redisKeyWithPrefix}");

            $votes = Redis::hGetAll($redisKeyWithPrefix);
            
            $keyWithoutPrefix = Str::after($redisKeyWithPrefix, $prefix);
            $reviewId = str_replace('review:votes:', '', $keyWithoutPrefix);
            
            $upvotes = (int) ($votes['upvotes'] ?? 0);
            $downvotes = (int) ($votes['downvotes'] ?? 0);

            Log::channel('stack')->info("Extracted Review ID: '{$reviewId}', Upvotes: {$upvotes}, Downvotes: {$downvotes}");

            if ($upvotes === 0 && $downvotes === 0) {
                 Log::channel('stack')->warning("Skipping sync for Review ID: {$reviewId} as vote counts are zero.");
                 Redis::del($redisKeyWithPrefix);
                 continue;
            }

            $updatedCount = Review::where('_id', $reviewId)->incrementEach([
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
            ]);

            Log::channel('stack')->info("MongoDB update query executed for ID: {$reviewId}. Documents updated: {$updatedCount}");

            if ($updatedCount > 0) {
                Redis::del($redisKeyWithPrefix);
                Log::channel('stack')->info("Successfully synced and deleted Redis key: {$redisKeyWithPrefix}");
            } else {
                Log::channel('stack')->error("FAILED to find and update document in MongoDB for Review ID: {$reviewId}. Verify the ID exists in the 'reviews' collection.");
            }
        }

        Log::channel('stack')->info('======== SyncReviewVotesToDB Finished =======');
        $this->info("\nSync process finished. Check storage/logs/laravel.log for details.");
        return 0;
    }
}