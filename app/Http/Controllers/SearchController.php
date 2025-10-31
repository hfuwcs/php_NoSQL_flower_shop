<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        $products = collect();

        if ($query) {

            $products = Product::search($query)->paginate(12);
        }

        return view('search.index', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}