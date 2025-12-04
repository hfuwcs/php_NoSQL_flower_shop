<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class CouponApplicationTest extends TestCase
{
    use RefreshMongoDB;
    use WithoutMiddleware;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
        $this->user = User::factory()->create();
    }

    // ==========================================
    // TEST SUITE: Apply Coupon via API
    // ==========================================

    public function test_authenticated_user_can_apply_valid_coupon(): void
    {
        // Setup: Tạo product và thêm vào giỏ hàng
        $product = Product::create([
            'name' => 'Test Flower',
            'price' => 100,
            'stock_quantity' => 50,
        ]);

        // Thêm sản phẩm vào giỏ hàng
        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 2]);

        // Tạo coupon
        Coupon::create([
            'code' => 'TESTCOUPON',
            'type' => 'percent',
            'value' => 15,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        // Apply coupon
        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), [
                'coupon_code' => 'TESTCOUPON'
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Coupon applied successfully!',
            ]);

        // Kiểm tra cart data trả về
        $this->assertEquals('TESTCOUPON', $response->json('cart.applied_coupon.code'));
        $this->assertEquals(30, $response->json('cart.discount_amount')); // 200 * 15%
        $this->assertEquals(170, $response->json('cart.final_total')); // 200 - 30
    }

    public function test_cannot_apply_invalid_coupon_code(): void
    {
        $product = Product::create([
            'name' => 'Test Flower',
            'price' => 50,
            'stock_quantity' => 50,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 1]);

        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), [
                'coupon_code' => 'INVALID_CODE'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Coupon code is invalid.',
            ]);
    }

    public function test_cannot_apply_expired_coupon(): void
    {
        $product = Product::create([
            'name' => 'Test Flower',
            'price' => 50,
            'stock_quantity' => 50,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 1]);

        Coupon::create([
            'code' => 'EXPIRED_COUPON',
            'type' => 'percent',
            'value' => 20,
            'expires_at' => Carbon::now()->subDays(1),
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), [
                'coupon_code' => 'EXPIRED_COUPON'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'This coupon is expired or has reached its usage limit.',
            ]);
    }

    public function test_cannot_apply_coupon_to_empty_cart(): void
    {
        Coupon::create([
            'code' => 'EMPTY_CART_TEST',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), [
                'coupon_code' => 'EMPTY_CART_TEST'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot apply coupon to an empty cart.',
            ]);
    }

    public function test_cannot_apply_coupon_without_authentication(): void
    {
        $response = $this->postJson(route('cart.applyCoupon'), [
            'coupon_code' => 'SOMECODE'
        ]);

        $response->assertStatus(401);
    }

    public function test_coupon_code_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['coupon_code']);
    }

    // ==========================================
    // TEST SUITE: Fixed Amount Coupon
    // ==========================================

    public function test_fixed_amount_coupon_applies_correctly(): void
    {
        $product = Product::create([
            'name' => 'Premium Bouquet',
            'price' => 200,
            'stock_quantity' => 20,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 1]);

        Coupon::create([
            'code' => 'FIXED50K',
            'type' => 'fixed',
            'value' => 50,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), [
                'coupon_code' => 'FIXED50K'
            ]);

        $response->assertOk();
        $this->assertEquals(200, $response->json('cart.subtotal'));
        $this->assertEquals(50, $response->json('cart.discount_amount'));
        $this->assertEquals(150, $response->json('cart.final_total'));
    }

    // ==========================================
    // TEST SUITE: Coupon Usage Limit
    // ==========================================

    public function test_cannot_apply_coupon_when_usage_limit_reached(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 100,
            'stock_quantity' => 50,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 1]);

        Coupon::create([
            'code' => 'LIMITED_USE',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 5,
            'usage_count' => 5, // Đã dùng hết
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('cart.applyCoupon'), [
                'coupon_code' => 'LIMITED_USE'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'This coupon is expired or has reached its usage limit.',
            ]);
    }
}
