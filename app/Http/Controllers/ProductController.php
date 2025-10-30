<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        // Lấy danh sách sản phẩm có phân trang
        $products = Product::paginate(12);
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        // Key để lưu cache trong Redis
        $cacheKey = "product:{$product->id}";

        $productData = Cache::tags(['products'])->remember($cacheKey, 3600, function () use ($product) {
            return $product->load(['reviews', 'reviews.user']);
        });

        return view('products.show', ['product' => $productData]);
    }
}
