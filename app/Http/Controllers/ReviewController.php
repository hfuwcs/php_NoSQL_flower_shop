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
        $request->validate(['vote_type' => ['required', 'string', 'in:up,down']]);

        $userId = Auth::id();
        $newVoteType = $request->input('vote_type');
        $newVoteValue = ($newVoteType === 'up') ? 1 : -1;

        $voteCountsKey = "review:votes:{$review->id}";
        $userVotesKey = "review:user_votes:{$review->id}";

        //
        $results = Redis::pipeline(function ($pipe) use ($userVotesKey, $userId, $voteCountsKey) {
            $pipe->hget($userVotesKey, $userId);
            $pipe->hmget($voteCountsKey, ['upvotes', 'downvotes']);
        });

        // Xử lý kết quả với error handling
        $currentVoteValue = isset($results[0]) ? (int) $results[0] : 0;
        $pendingUpvotes = isset($results[1][0]) ? $results[1][0] : null;
        $pendingDownvotes = isset($results[1][1]) ? $results[1][1] : null;

        //Xử lý logic vote
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

            return response()->json([
                'success' => true,
                'upvotes' => max(0, $totalUpvotes),
                'downvotes' => max(0, $totalDownvotes),
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }
}
