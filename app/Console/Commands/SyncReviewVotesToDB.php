<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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

        // Lấy tất cả keys và filter theo pattern
        $allKeys = Redis::keys('*');
        $voteKeys = array_filter($allKeys, function($key) use ($prefix) {
            return str_starts_with($key, $prefix . 'review:votes:');
        });
        
        Log::channel('stack')->info(count($voteKeys) . " key(s) found.");
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

            // Lấy key không có prefix để dùng với hGetAll
            $keyWithoutPrefix = Str::after($redisKeyWithPrefix, $prefix);
            $votes = Redis::hGetAll($keyWithoutPrefix);
            
            $reviewId = str_replace('review:votes:', '', $keyWithoutPrefix);
            
            $upvotes = (int) ($votes['upvotes'] ?? 0);
            $downvotes = (int) ($votes['downvotes'] ?? 0);

            Log::channel('stack')->info("Extracted Review ID: '{$reviewId}', Upvotes: {$upvotes}, Downvotes: {$downvotes}");

            // Skip chỉ khi hash rỗng hoàn toàn (key không có field nào)
            // Lưu ý: Không skip khi upvotes=0 và downvotes=0 vì:
            // - Có thể do user vote up rồi vote down cân bằng nhau
            // - Hoặc user vote rồi thu hồi vote
            // - Các trường hợp này vẫn cần sync để đảm bảo consistency
            if (empty($votes)) {
                Log::channel('stack')->warning("Skipping sync for Review ID: {$reviewId} as vote hash is empty (no fields).");
                Redis::del($keyWithoutPrefix);
                continue;
            }

            // Kiểm tra review có tồn tại không
            $review = Review::where('_id', $reviewId)->first();
            
            if ($review) {
                // Increment votes
                $review->increment('upvotes', $upvotes);
                $review->increment('downvotes', $downvotes);
                
                //Xóa cache của product cha
                $productCacheKey = "product:{$review->product_id}";
                Cache::forget($productCacheKey);
                Log::channel('stack')->info("Invalidated product cache: {$productCacheKey}");


                // Xóa key trong Redis sau khi sync thành công
                Redis::del($keyWithoutPrefix);
                Log::channel('stack')->info("Successfully synced and deleted Redis key: {$redisKeyWithPrefix}");
                Log::channel('stack')->info("Updated Review ID: {$reviewId} - New upvotes: {$review->upvotes}, New downvotes: {$review->downvotes}");
            } else {
                Log::channel('stack')->error("FAILED to find document in MongoDB for Review ID: {$reviewId}. Verify the ID exists in the 'reviews' collection.");
            }
        }

        Log::channel('stack')->info('======== SyncReviewVotesToDB Finished =======');
        $this->info("\nSync process finished. Check storage/logs/laravel.log for details.");
        return 0;
    }
}