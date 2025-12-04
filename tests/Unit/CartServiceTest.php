<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Carbon\Carbon;
use Exception;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshMongoDB;

    protected CartService $cartService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
        $this->cartService = app(CartService::class);
        $this->user = User::factory()->create();
    }

    // ==========================================
    // TEST SUITE: Add Product to Cart
    // ==========================================

    public function test_add_product_creates_cart_if_not_exists(): void
    {
        $product = Product::create([
            'name' => 'Rose Bouquet',
            'price' => 50,
            'stock_quantity' => 100,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);

        $cart = Cart::where('user_id', $this->user->id)->first();

        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);
        $this->assertEquals($product->id, $cart->items[0]['product_id']);
        $this->assertEquals(2, $cart->items[0]['quantity']);
    }

    public function test_add_product_increases_quantity_if_product_exists(): void
    {
        $product = Product::create([
            'name' => 'Tulip Arrangement',
            'price' => 75,
            'stock_quantity' => 50,
        ]);

        $this->cartService->addProduct($this->user, $product, 2);
        $this->cartService->addProduct($this->user, $product, 3);

        $cart = Cart::where('user_id', $this->user->id)->first();

        $this->assertCount(1, $cart->items);
        $this->assertEquals(5, $cart->items[0]['quantity']);
    }

    public function test_add_multiple_different_products(): void
    {
        $product1 = Product::create(['name' => 'Product 1', 'price' => 10, 'stock_quantity' => 100]);
        $product2 = Product::create(['name' => 'Product 2', 'price' => 20, 'stock_quantity' => 100]);

        $this->cartService->addProduct($this->user, $product1, 1);
        $this->cartService->addProduct($this->user, $product2, 2);

        $cart = Cart::where('user_id', $this->user->id)->first();

        $this->assertCount(2, $cart->items);
    }

    // ==========================================
    // TEST SUITE: Get Cart Content
    // ==========================================

    public function test_get_cart_content_returns_empty_when_no_cart(): void
    {
        $content = $this->cartService->getCartContent($this->user);

        $this->assertEmpty($content['items']);
        $this->assertEquals(0, $content['subtotal']);
        $this->assertEquals(0, $content['final_total']);
    }

    public function test_get_cart_content_calculates_subtotal_correctly(): void
    {
        $product = Product::create(['name' => 'Test Product', 'price' => 25.50, 'stock_quantity' => 100]);

        $this->cartService->addProduct($this->user, $product, 4);

        $content = $this->cartService->getCartContent($this->user);

        $this->assertEquals(102, $content['subtotal']); // 25.50 * 4 = 102
        $this->assertEquals(102, $content['final_total']);
    }

    // ==========================================
    // TEST SUITE: Apply Coupon
    // ==========================================

    public function test_apply_valid_percent_coupon(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 100, 'stock_quantity' => 10]);
        $this->cartService->addProduct($this->user, $product, 2);

        $coupon = Coupon::create([
            'code' => 'SALE20',
            'type' => 'percent',
            'value' => 20,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $this->cartService->applyCoupon($this->user, 'SALE20');

        $content = $this->cartService->getCartContent($this->user);

        $this->assertEquals(200, $content['subtotal']); // 100 * 2
        $this->assertEquals(40, $content['discount_amount']); // 200 * 20%
        $this->assertEquals(160, $content['final_total']); // 200 - 40
        $this->assertEquals('SALE20', $content['applied_coupon']['code']);
    }

    public function test_apply_valid_fixed_coupon(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 100, 'stock_quantity' => 10]);
        $this->cartService->addProduct($this->user, $product, 2);

        Coupon::create([
            'code' => 'FIXED50',
            'type' => 'fixed',
            'value' => 50,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $this->cartService->applyCoupon($this->user, 'FIXED50');

        $content = $this->cartService->getCartContent($this->user);

        $this->assertEquals(200, $content['subtotal']);
        $this->assertEquals(50, $content['discount_amount']);
        $this->assertEquals(150, $content['final_total']);
    }

    public function test_apply_coupon_to_empty_cart_throws_exception(): void
    {
        Coupon::create([
            'code' => 'EMPTY_CART',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot apply coupon to an empty cart.');

        $this->cartService->applyCoupon($this->user, 'EMPTY_CART');
    }

    public function test_apply_invalid_coupon_code_throws_exception(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 50, 'stock_quantity' => 10]);
        $this->cartService->addProduct($this->user, $product, 1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Coupon code is invalid.');

        $this->cartService->applyCoupon($this->user, 'NONEXISTENT');
    }

    public function test_apply_expired_coupon_throws_exception(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 50, 'stock_quantity' => 10]);
        $this->cartService->addProduct($this->user, $product, 1);

        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->subDays(1),
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This coupon is expired or has reached its usage limit.');

        $this->cartService->applyCoupon($this->user, 'EXPIRED');
    }

    public function test_apply_coupon_with_exceeded_usage_limit_throws_exception(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 50, 'stock_quantity' => 10]);
        $this->cartService->addProduct($this->user, $product, 1);

        Coupon::create([
            'code' => 'MAXED_OUT',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 5,
            'usage_count' => 5,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This coupon is expired or has reached its usage limit.');

        $this->cartService->applyCoupon($this->user, 'MAXED_OUT');
    }

    // ==========================================
    // TEST SUITE: Update Item Quantity
    // ==========================================

    public function test_update_item_quantity(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 50, 'stock_quantity' => 100]);
        $this->cartService->addProduct($this->user, $product, 2);

        $this->cartService->updateItemQuantity($this->user, $product->id, 5);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(5, $cart->items[0]['quantity']);
    }

    public function test_update_item_quantity_to_zero_removes_item(): void
    {
        $product = Product::create(['name' => 'Test', 'price' => 50, 'stock_quantity' => 100]);
        $this->cartService->addProduct($this->user, $product, 2);

        $this->cartService->updateItemQuantity($this->user, $product->id, 0);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(0, $cart->items);
    }

    // ==========================================
    // TEST SUITE: Remove Item
    // ==========================================

    public function test_remove_item_from_cart(): void
    {
        $product1 = Product::create(['name' => 'Product 1', 'price' => 10, 'stock_quantity' => 100]);
        $product2 = Product::create(['name' => 'Product 2', 'price' => 20, 'stock_quantity' => 100]);

        $this->cartService->addProduct($this->user, $product1, 1);
        $this->cartService->addProduct($this->user, $product2, 2);

        $this->cartService->removeItem($this->user, $product1->id);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $cart->items);
        $this->assertEquals($product2->id, $cart->items[0]['product_id']);
    }

    // ==========================================
    // TEST SUITE: Discount Calculation Edge Cases
    // ==========================================

    public function test_fixed_discount_does_not_go_below_zero(): void
    {
        $product = Product::create(['name' => 'Cheap Item', 'price' => 10, 'stock_quantity' => 100]);
        $this->cartService->addProduct($this->user, $product, 1);

        Coupon::create([
            'code' => 'BIG_DISCOUNT',
            'type' => 'fixed',
            'value' => 50, // Discount lớn hơn subtotal
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->cartService->applyCoupon($this->user, 'BIG_DISCOUNT');

        $content = $this->cartService->getCartContent($this->user);

        $this->assertEquals(10, $content['subtotal']);
        $this->assertEquals(50, $content['discount_amount']);
        $this->assertEquals(0, $content['final_total']); // max(0, 10-50) = 0
    }
}
