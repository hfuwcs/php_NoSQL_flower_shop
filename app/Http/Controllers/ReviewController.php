<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product)
    {
        $validatedData = $request->validated();

        $review = $product->reviews()->create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'rating' => $validatedData['rating'],
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
        ]);


        Cache::tags(['products'])->flush();
        return back()->with('success', 'Thank you for your review!');
    }
    public function vote(Request $request, Review $review)
    {
        // 1. Validate input
        $request->validate([
            'vote_type' => ['required', 'string', 'in:up,down'],
        ]);

        // 2. Xác định key trong Redis và field cần tăng
        $redisKey = "review:votes:{$review->id}";
        $field = $request->input('vote_type') === 'up' ? 'upvotes' : 'downvotes';

        // 3. Tăng giá trị trong Redis Hash
        Redis::hIncrBy($redisKey, $field, 1);

        return back()->with('success', 'Thank you for your feedback!');
    }
}