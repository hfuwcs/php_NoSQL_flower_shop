<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class LeaderboardController extends Controller
{
    public function index()
    {
        $leaderboardKey = 'leaderboard:products:top_rated';
        $topProductIds = [];

        try {
            // Lấy 10 ID sản phẩm có điểm cao nhất từ Redis.
            // ZREVRANGE
            $topProductIds = Redis::zrevrange($leaderboardKey, 0, 9);
        } catch (\Exception $e) {
            report($e);
        }

        $topProducts = collect();
        if (!empty($topProductIds)) {
            //Lấy thông tin chi tiết của các sản phẩm này từ MongoDB.
            //whereIn
            $products = Product::whereIn('_id', $topProductIds)->get();

            $topProducts = $products->sortBy(function ($product) use ($topProductIds) {
                return array_search($product->id, $topProductIds);
            });
        }

        return view('leaderboard.index', ['products' => $topProducts]);
    }
}