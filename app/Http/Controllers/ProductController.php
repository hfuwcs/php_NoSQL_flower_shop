<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Lấy các giá trị filter từ request
        $category = $request->input('category');
        $priceMin = (float) $request->input('price_min');
        $priceMax = (float) $request->input('price_max');

        $query = Product::query();

        // Áp dụng các scope một cách có điều kiện
        $query->filterByCategory($category);
        $query->filterByPriceRange($priceMin, $priceMax);

        // Thực hiện phân trang sau khi đã áp dụng tất cả các filter
        $products = $query->paginate(12);

        $categoryArray = Product::distinct('category')->get()->toArray();

        $categories = collect($categoryArray)
            ->flatten()
            ->filter()
            ->sort()
            ->values();
        
        //dd($categories);

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function show(Product $product)
    {
        // Key để lưu cache trong Redis
        $cacheKey = "product:{$product->id}";

        // phpredis không hỗ trợ tags, nên dùng remember() trực tiếp (hoặc chuyển sang dùng tag cũng được, có predis trong composer.json rồi đấy)
        $productData = Cache::remember($cacheKey, 3600, function () use ($product) {
            return $product->load(['reviews', 'reviews.user']);
        });

        return view('products.show', ['product' => $productData]);
    }
}
