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

        // Invalidate product cache để hiển thị review mới ngay lập tức
        $productCacheKey = "product:{$product->id}";
        Cache::forget($productCacheKey);

        // Dispatch the job with the Product model
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

        $currentVoteValue = (int) Redis::hget($userVotesKey, $userId);


        if ($currentVoteValue === $newVoteValue) {
            // **Trường hợp 1: Thu hồi vote**
            // Xóa phiếu bầu của user
            Redis::hdel($userVotesKey, $userId);
            // Giảm số đếm tương ứng đi 1
            Redis::hincrby($voteCountsKey, $newVoteType . 's', -1);
        } elseif ($currentVoteValue !== 0) {
            // **Trường hợp 2: Thay đổi vote (từ up sang down hoặc ngược lại)**
            // Cập nhật phiếu bầu của user
            Redis::hset($userVotesKey, $userId, $newVoteValue);
            // Tăng số đếm mới lên 1
            Redis::hincrby($voteCountsKey, $newVoteType . 's', 1);
            // Giảm số đếm cũ đi 1
            $oldVoteType = ($currentVoteValue === 1) ? 'up' : 'down';
            Redis::hincrby($voteCountsKey, $oldVoteType . 's', -1);
        } else {
            // **Trường hợp 3: Vote mới (chưa từng vote)**
            // Ghi lại phiếu bầu của user
            Redis::hset($userVotesKey, $userId, $newVoteValue);
            Redis::hincrby($voteCountsKey, $newVoteType . 's', 1);
        }


        if ($request->wantsJson()) {
            $pendingVotes = Redis::hGetAll($voteCountsKey);
            $totalUpvotes = $review->upvotes + (int)($pendingVotes['upvotes'] ?? 0);
            $totalDownvotes = $review->downvotes + (int)($pendingVotes['downvotes'] ?? 0);
            
            return response()->json([
                'success' => true,
                'upvotes' => $totalUpvotes,
                'downvotes' => $totalDownvotes,
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }
}
