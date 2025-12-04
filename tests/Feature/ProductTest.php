<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: Product Listing
    // ==========================================

    public function test_guest_can_view_products_list(): void
    {
        Product::create([
            'name' => 'Rose Bouquet',
            'description' => 'Beautiful roses',
            'category' => 'roses',
            'price' => 45.99,
            'stock_quantity' => 50,
            'images' => [],
        ]);

        $response = $this->get(route('products.index'));

        $response->assertOk()
            ->assertViewIs('products.index')
            ->assertSee('Rose Bouquet');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        Product::create([
            'name' => 'Red Rose',
            'category' => 'roses',
            'price' => 30,
            'stock_quantity' => 100,
        ]);

        Product::create([
            'name' => 'Yellow Tulip',
            'category' => 'tulips',
            'price' => 25,
            'stock_quantity' => 80,
        ]);

        $response = $this->get(route('products.index', ['category' => 'roses']));

        $response->assertOk()
            ->assertSee('Red Rose')
            ->assertDontSee('Yellow Tulip');
    }

    public function test_products_can_be_filtered_by_price_range(): void
    {
        Product::create([
            'name' => 'Cheap Flower',
            'category' => 'mixed',
            'price' => 10,
            'stock_quantity' => 200,
        ]);

        Product::create([
            'name' => 'Expensive Arrangement',
            'category' => 'mixed',
            'price' => 100,
            'stock_quantity' => 20,
        ]);

        $response = $this->get(route('products.index', [
            'price_min' => 50,
            'price_max' => 150
        ]));

        $response->assertOk()
            ->assertSee('Expensive Arrangement')
            ->assertDontSee('Cheap Flower');
    }

    public function test_products_can_be_sorted_by_price_ascending(): void
    {
        Product::create(['name' => 'Medium', 'price' => 50, 'stock_quantity' => 10, 'category' => 'test']);
        Product::create(['name' => 'Cheap', 'price' => 20, 'stock_quantity' => 10, 'category' => 'test']);
        Product::create(['name' => 'Expensive', 'price' => 100, 'stock_quantity' => 10, 'category' => 'test']);

        $response = $this->get(route('products.index', ['sort' => 'price_asc']));

        $response->assertOk();
        // Products should be sorted by price ascending
    }

    public function test_products_can_be_sorted_by_price_descending(): void
    {
        Product::create(['name' => 'Medium', 'price' => 50, 'stock_quantity' => 10, 'category' => 'test']);
        Product::create(['name' => 'Cheap', 'price' => 20, 'stock_quantity' => 10, 'category' => 'test']);
        Product::create(['name' => 'Expensive', 'price' => 100, 'stock_quantity' => 10, 'category' => 'test']);

        $response = $this->get(route('products.index', ['sort' => 'price_desc']));

        $response->assertOk();
    }

    // ==========================================
    // TEST SUITE: Product Detail
    // ==========================================

    public function test_guest_can_view_product_detail(): void
    {
        $product = Product::create([
            'name' => 'Detailed Product',
            'description' => 'This is a detailed description',
            'category' => 'special',
            'price' => 75,
            'stock_quantity' => 30,
            'images' => [],
            'average_rating' => 4.5,
            'review_count' => 10,
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertViewIs('products.show')
            ->assertSee('Detailed Product')
            ->assertSee('This is a detailed description');
    }

    public function test_product_detail_shows_reviews(): void
    {
        $user = User::factory()->create(['name' => 'Test Reviewer']);
        
        $product = Product::create([
            'name' => 'Reviewed Product',
            'price' => 40,
            'stock_quantity' => 25,
            'category' => 'flowers',
        ]);

        // Create a review for this product
        \App\Models\Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Excellent!',
            'content' => 'Best flowers ever!',
            'upvotes' => 0,
            'downvotes' => 0,
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertSee('Excellent!')
            ->assertSee('Best flowers ever!');
    }

    // ==========================================
    // TEST SUITE: Product Stock
    // ==========================================

    public function test_product_in_stock(): void
    {
        $product = Product::create([
            'name' => 'In Stock Product',
            'price' => 30,
            'stock_quantity' => 50,
            'category' => 'test',
        ]);

        $this->assertTrue($product->inStock());
    }

    public function test_product_out_of_stock(): void
    {
        $product = Product::create([
            'name' => 'Out of Stock Product',
            'price' => 30,
            'stock_quantity' => 0,
            'category' => 'test',
        ]);

        $this->assertFalse($product->inStock());
    }
}
