<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Jobs\UpdateProductStatsJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function __construct(protected PointService $pointService) {}
    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();

        $orderItem = OrderItem::find($validated['order_item_id']);
        $product = $orderItem->product;
        $user = $request->user();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        $orderItem->review_id = $review->id;
        $orderItem->save();

        Cache::forget("product:{$product->id}");
        Cache::forget("product:basic:{$product->id}");
        $this->clearReviewsCache($product->id);
        Cache::tags(['products'])->flush();

        UpdateProductStatsJob::dispatch($product);

        //Cộng điểm
        $this->pointService->addPointsForAction(
            $request->user(),
            'review_created',
            $review
        );

        return redirect()->route('orders.history')->with('success', 'Thank you for your review!');
    }

    private function clearReviewsCache($productId)
    {
        for ($page = 1; $page <= 10; $page++) {
            Cache::forget("product:{$productId}:reviews:page:{$page}");
        }
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

            $this->clearReviewsCache($review->product_id);

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

    public function create(OrderItem $orderItem)
    {
        if ((string) $orderItem->order->user_id !== (string) Auth::id()) {
            abort(403);
        }

        // Kiểm tra điều kiện review
        $isDelivered = $orderItem->delivery_status === 'delivered';
        $isNotReviewedYet = is_null($orderItem->review_id);
        $isWithinReviewPeriod = is_null($orderItem->review_deadline_at) 
            || now()->lte($orderItem->review_deadline_at);

        if (!$isDelivered || !$isNotReviewedYet || !$isWithinReviewPeriod) {
            return redirect()->route('orders.history')->with('error', 'You are not eligible to review this item at this time.');
        }

        $orderItem->load('product');

        return view('reviews.create', ['orderItem' => $orderItem]);
    }
}
