<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: Basic Search
    // ==========================================

    public function test_guest_can_access_search_page(): void
    {
        $response = $this->get(route('search.index'));

        $response->assertOk()
            ->assertViewIs('search.index');
    }

    public function test_search_page_accepts_query_parameter(): void
    {
        Product::create([
            'name' => 'Red Rose',
            'description' => 'Beautiful red roses',
            'category' => 'roses',
            'price' => 30,
            'stock_quantity' => 50,
        ]);

        $response = $this->get(route('search.index', ['q' => 'rose']));

        $response->assertOk();
    }

    public function test_search_with_empty_query_shows_all_products(): void
    {
        Product::create([
            'name' => 'Product 1',
            'price' => 20,
            'stock_quantity' => 30,
            'category' => 'test',
        ]);

        Product::create([
            'name' => 'Product 2',
            'price' => 40,
            'stock_quantity' => 25,
            'category' => 'test',
        ]);

        $response = $this->get(route('search.index'));

        $response->assertOk();
    }

    // ==========================================
    // TEST SUITE: Search with Filters
    // ==========================================

    public function test_search_can_filter_by_category(): void
    {
        Product::create([
            'name' => 'Search Rose',
            'category' => 'roses',
            'price' => 35,
            'stock_quantity' => 40,
        ]);

        Product::create([
            'name' => 'Search Tulip',
            'category' => 'tulips',
            'price' => 25,
            'stock_quantity' => 60,
        ]);

        $response = $this->get(route('search.index', [
            'q' => 'Search',
            'category' => 'roses'
        ]));

        $response->assertOk();
    }

    public function test_search_can_filter_by_price_range(): void
    {
        Product::create([
            'name' => 'Budget Flower',
            'price' => 15,
            'stock_quantity' => 100,
            'category' => 'budget',
        ]);

        Product::create([
            'name' => 'Premium Flower',
            'price' => 150,
            'stock_quantity' => 20,
            'category' => 'premium',
        ]);

        $response = $this->get(route('search.index', [
            'price_min' => 100,
            'price_max' => 200
        ]));

        $response->assertOk();
    }
}
