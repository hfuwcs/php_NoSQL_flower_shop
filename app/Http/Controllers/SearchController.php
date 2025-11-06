<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

use function GuzzleHttp\json_encode;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Lấy query tìm kiếm
        $query = $request->input('query');

        // Lấy các tham số lọc
        $category = $request->input('category');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        $products = collect();

        if ($query) {
            $search = Product::search($query);

            // Build filter string manually for MeiliSearch
            $filters = [];

            // Áp dụng filter category (nếu có)
            if ($category) {
                $filters[] = "category = " . json_encode($category);
            }

            // Áp dụng filter giá tối thiểu
            if ($priceMin && is_numeric($priceMin)) {
                $filters[] = "price >= {$priceMin}";
            }

            // Áp dụng filter giá tối đa
            if ($priceMax && is_numeric($priceMax)) {
                $filters[] = "price <= {$priceMax}";
            }

            // Combine filters với AND và áp dụng qua options
            if (!empty($filters)) {
                $filterString = implode(' AND ', $filters);
                // Use Scout's options method to pass raw MeiliSearch filters
                $search->options(['filter' => $filterString]);
            }

            $products = $search->paginate(12);
        }

        // Lấy danh sách categories (distinct)
        $categories = Product::distinct('category')->get()->flatten()->filter()->sort()->values();

        return view('search.index', [
            'products' => $products,
            'query' => $query,
            'categories' => $categories,
            'selectedCategory' => $category,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
        ]);
    }
}