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

        // Dispatch the job with the Product model
        UpdateProductStatsJob::dispatch($product);

        return back()->with('success', 'Thank you for your review!');
    }
    public function vote(Request $request, Review $review)
    {
        $request->validate(['vote_type' => ['required', 'string', 'in:up,down']]);

        $redisKey = "review:votes:{$review->id}";
        $field = $request->input('vote_type') === 'up' ? 'upvotes' : 'downvotes';

        $newVoteCountInRedis = Redis::hIncrBy($redisKey, $field, 1);

        if ($request->wantsJson()) {
            $pendingVotes = Redis::hGetAll($redisKey);
            $pendingUpvotes = (int) ($pendingVotes['upvotes'] ?? 0);
            $pendingDownvotes = (int) ($pendingVotes['downvotes'] ?? 0);

            $totalUpvotes = $review->upvotes + $pendingUpvotes;
            $totalDownvotes = $review->downvotes + $pendingDownvotes;

            return response()->json([
                'success' => true,
                'upvotes' => $totalUpvotes,
                'downvotes' => $totalDownvotes,
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }
}
