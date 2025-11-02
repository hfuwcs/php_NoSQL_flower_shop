<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Jobs\UpdateProductStatsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product)
    {
        $product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $request->validated('rating'),
            'title' => $request->validated('title'),
            'content' => $request->validated('content'),
        ]);

        // Invalidate product cache
        Cache::forget("product:{$product->id}");
        
        // Clear cache list
        Cache::tags(['products'])->flush();

        UpdateProductStatsJob::dispatch($product);

        return back()->with('success', 'Thank you for your review!');
    }

    public function vote(Request $request, Review $review)
    {
        $startTime = microtime(true);
        
        $request->validate(['vote_type' => ['required', 'string', 'in:up,down']]);

        $userId = Auth::id();
        $newVoteType = $request->input('vote_type');
        $newVoteValue = ($newVoteType === 'up') ? 1 : -1;

        $voteCountsKey = "review:votes:{$review->id}";
        $userVotesKey = "review:user_votes:{$review->id}";

        // Đo thời gian Redis READ
        $redisReadStart = microtime(true);
        $results = Redis::pipeline(function ($pipe) use ($userVotesKey, $userId, $voteCountsKey) {
            $pipe->hget($userVotesKey, $userId);
            $pipe->hmget($voteCountsKey, ['upvotes', 'downvotes']);
        });
        $redisReadTime = (microtime(true) - $redisReadStart) * 1000;

        // Xử lý kết quả với error handling
        $currentVoteValue = isset($results[0]) ? (int) $results[0] : 0;
        $pendingUpvotes = isset($results[1][0]) ? $results[1][0] : null;
        $pendingDownvotes = isset($results[1][1]) ? $results[1][1] : null;

        // Đo thời gian Redis WRITE
        $redisWriteStart = microtime(true);
        Redis::pipeline(function ($pipe) use ($currentVoteValue, $newVoteValue, $userVotesKey, $userId, $voteCountsKey, $newVoteType) {
            if ($currentVoteValue === $newVoteValue) {
                // Thu hồi vote
                $pipe->hdel($userVotesKey, $userId);
                $pipe->hincrby($voteCountsKey, $newVoteType . 'votes', -1);
            } elseif ($currentVoteValue !== 0) {
                // Thay đổi vote
                $pipe->hset($userVotesKey, $userId, $newVoteValue);
                $pipe->hincrby($voteCountsKey, $newVoteType . 'votes', 1);
                $oldVoteType = ($currentVoteValue === 1) ? 'up' : 'down';
                $pipe->hincrby($voteCountsKey, $oldVoteType . 'votes', -1);
            } else {
                // Vote mới
                $pipe->hset($userVotesKey, $userId, $newVoteValue);
                $pipe->hincrby($voteCountsKey, $newVoteType . 'votes', 1);
            }
        });
        $redisWriteTime = (microtime(true) - $redisWriteStart) * 1000;

        if ($request->wantsJson()) {
            // Tính toán từ kết quả đã có, không query lại
            $deltaUp = 0;
            $deltaDown = 0;

            if ($currentVoteValue === $newVoteValue) {
                // Thu hồi
                $newVoteType === 'up' ? $deltaUp = -1 : $deltaDown = -1;
            } elseif ($currentVoteValue !== 0) {
                // Thay đổi
                $newVoteType === 'up' ? ($deltaUp = 1) && ($deltaDown = -1) : ($deltaDown = 1) && ($deltaUp = -1);
            } else {
                // Mới
                $newVoteType === 'up' ? $deltaUp = 1 : $deltaDown = 1;
            }

            $totalUpvotes = $review->upvotes + (int)($pendingUpvotes ?? 0) + $deltaUp;
            $totalDownvotes = $review->downvotes + (int)($pendingDownvotes ?? 0) + $deltaDown;

            // Log timing
            $totalTime = (microtime(true) - $startTime) * 1000;
            Log::info('Vote Performance', [
                'review_id' => $review->id,
                'total_time_ms' => round($totalTime, 2),
                'redis_read_ms' => round($redisReadTime, 2),
                'redis_write_ms' => round($redisWriteTime, 2),
                'code_processing_ms' => round($totalTime - $redisReadTime - $redisWriteTime, 2),
            ]);

            return response()->json([
                'success' => true,
                'upvotes' => max(0, $totalUpvotes),
                'downvotes' => max(0, $totalDownvotes),
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }
}
