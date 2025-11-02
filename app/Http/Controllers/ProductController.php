<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $startTime = microtime(true);
        
        // Lấy các giá trị filter từ request
        $category = $request->input('category');
        $priceMin = (float) $request->input('price_min');
        $priceMax = (float) $request->input('price_max');

        $query = Product::query();

        // Áp dụng các scope một cách có điều kiện
        $query->filterByCategory($category);
        $query->filterByPriceRange($priceMin, $priceMax);

        // Đo thời gian query MongoDB cho products
        $mongoQueryStart = microtime(true);
        $products = $query->paginate(12);
        $mongoQueryTime = (microtime(true) - $mongoQueryStart) * 1000;

        // Đo thời gian query categories
        $categoriesStart = microtime(true);
        $categoryArray = Product::distinct('category')->get()->toArray();
        $categoriesTime = (microtime(true) - $categoriesStart) * 1000;

        $categories = collect($categoryArray)
            ->flatten()
            ->filter()
            ->sort()
            ->values();
        
        // Log timing
        $totalTime = (microtime(true) - $startTime) * 1000;
        Log::info('Product Index Performance', [
            'total_time_ms' => round($totalTime, 2),
            'mongo_products_query_ms' => round($mongoQueryTime, 2),
            'mongo_categories_query_ms' => round($categoriesTime, 2),
            'view_render_ms' => round($totalTime - $mongoQueryTime - $categoriesTime, 2),
            'filters' => compact('category', 'priceMin', 'priceMax'),
        ]);

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function show(Product $product)
    {
        $startTime = microtime(true);
        
        // Key để lưu cache trong Redis
        $cacheKey = "product:{$product->id}";

        // Đo thời gian cache/query
        $cacheStart = microtime(true);
        $productData = Cache::remember($cacheKey, 3600, function () use ($product) {
            return $product->load(['reviews', 'reviews.user']);
        });
        $cacheTime = (microtime(true) - $cacheStart) * 1000;

        // Log timing
        $totalTime = (microtime(true) - $startTime) * 1000;
        Log::info('Product Show Performance', [
            'product_id' => $product->id,
            'total_time_ms' => round($totalTime, 2),
            'cache_or_mongo_ms' => round($cacheTime, 2),
            'view_render_ms' => round($totalTime - $cacheTime, 2),
            'reviews_count' => $productData->reviews->count(),
        ]);

        return view('products.show', ['product' => $productData]);
    }
}

// <?php


// namespace App\Http\Controllers;

// use App\Models\Product;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Cache;

// class ProductController extends Controller
// {
//     public function index(Request $request)
//     {
//         // Lấy các giá trị filter từ request
//         $category = $request->input('category');
//         $priceMin = (float) $request->input('price_min');
//         $priceMax = (float) $request->input('price_max');

//         $cacheKey = "products:list:{$category}:{$priceMin}:{$priceMax}:" . $request->input('page', 1);

//         $products = Cache::remember($cacheKey, 300, function () use ($category, $priceMin, $priceMax) {
//             $query = Product::query();
            
//             // Eager load nếu cần (reviews count)
//             // $query->withCount('reviews');
            
//             $query->filterByCategory($category);
//             $query->filterByPriceRange($priceMin, $priceMax);
            
//             return $query->paginate(12);
//         });

//         // Cache categories
//         $categories = Cache::remember('products:categories', 3600, function () {
//             return Product::raw(function ($collection) {
//                 return $collection->distinct('category');
//             });
//         });

//         return view('products.index', [
//             'products' => $products,
//             'categories' => collect($categories)->sort()->values(),
//         ]);
//     }

//     public function show(Product $product)
//     {
//         $cacheKey = "product:{$product->id}";

//         $productData = Cache::remember($cacheKey, 3600, function () use ($product) {
//             return $product->load(['reviews' => function ($query) {
//                 $query->latest()->limit(50); // Giới hạn số reviews load
//             }, 'reviews.user']);
//         });

//         return view('products.show', ['product' => $productData]);
//     }
// }
// ?>