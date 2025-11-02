<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\QueryFilters\CategoryFilter;
use App\QueryFilters\PriceRangeFilter;
use App\QueryFilters\SortFilter;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;

class ProductController extends Controller
{
    public function index(Request $request, Pipeline $pipeline)
    {
        $startTime = microtime(true);
        
        $query = Product::query();

        $pipes = [
            CategoryFilter::class,
            PriceRangeFilter::class,
            SortFilter::class,
        ];

        $query = $pipeline
            ->send($query)
            ->through($pipes)
            ->thenReturn();

        $mongoQueryStart = microtime(true);
        $products = $query->paginate(12)->withQueryString(); 
        $mongoQueryTime = (microtime(true) - $mongoQueryStart) * 1000;

        $categoriesStart = microtime(true);
        $categories = Cache::remember('product_categories', 3600, function () {
            return collect(Product::distinct('category')->get()->toArray())
                ->flatten()
                ->filter()
                ->sort()
                ->values();
        });
        $categoriesTime = (microtime(true) - $categoriesStart) * 1000;
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        Log::info('Product Index Performance', [
            'total_time_ms' => round($totalTime, 2),
            'mongo_products_query_ms' => round($mongoQueryTime, 2),
            'mongo_categories_query_ms' => round($categoriesTime, 2),
            'filters_applied' => $request->all(),
        ]);

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $startTime = microtime(true);
        
        $cacheKey = "product:basic:{$product->id}";

        $cacheStart = microtime(true);
        $productData = Cache::remember($cacheKey, 3600, function () use ($product) {
            return $product;
        });
        $cacheTime = (microtime(true) - $cacheStart) * 1000;

        $reviewsStart = microtime(true);
        $page = $request->input('page', 1);
        $perPage = 10;
        
        $reviewsCacheKey = "product:{$product->id}:reviews:page:{$page}";
        $productObjectId = new ObjectId((string) $product->id);
        
        $reviews = Cache::remember($reviewsCacheKey, 300, function () use ($productObjectId, $perPage) {
            return Review::where('product_id', $productObjectId)
                ->with('user')
                ->orderBy('upvotes', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
        $reviewsTime = (microtime(true) - $reviewsStart) * 1000;

        $totalTime = (microtime(true) - $startTime) * 1000;
        Log::info('Product Show Performance', [
            'product_id' => $product->id,
            'total_time_ms' => round($totalTime, 2),
            'product_cache_ms' => round($cacheTime, 2),
            'reviews_query_ms' => round($reviewsTime, 2),
            'reviews_count' => $reviews->total(),
            'current_page' => $reviews->currentPage(),
        ]);

        return view('products.show', [
            'product' => $productData,
            'reviews' => $reviews,
        ]);
    }
}