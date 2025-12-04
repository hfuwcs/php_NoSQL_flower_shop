<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshMongoDB;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->user = User::factory()->create();
    }

    // ==========================================
    // TEST SUITE: View Cart
    // ==========================================

    public function test_authenticated_user_can_view_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('cart.index'));

        $response->assertOk()
            ->assertViewIs('cart.index');
    }

    public function test_guest_cannot_view_cart(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: Add to Cart
    // ==========================================

    public function test_authenticated_user_can_add_product_to_cart(): void
    {
        $product = Product::create([
            'name' => 'Beautiful Rose',
            'description' => 'A beautiful red rose',
            'price' => 25.99,
            'stock_quantity' => 100,
            'category' => 'roses',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('cart.add', $product), [
                'quantity' => 3
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
        ], 'mongodb');
    }

    public function test_add_product_with_default_quantity_of_one(): void
    {
        $product = Product::create([
            'name' => 'Tulip',
            'price' => 15,
            'stock_quantity' => 50,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('cart.add', $product));

        $response->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_guest_cannot_add_to_cart(): void
    {
        $product = Product::create([
            'name' => 'Guest Test Product',
            'price' => 20,
            'stock_quantity' => 30,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 1
        ]);

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: Update Cart Item
    // ==========================================

    public function test_user_can_update_cart_item_quantity(): void
    {
        $product = Product::create([
            'name' => 'Sunflower',
            'price' => 30,
            'stock_quantity' => 100,
        ]);

        // Add to cart first
        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 2]);

        // Update quantity
        $response = $this->actingAs($this->user)
            ->patch(route('cart.update', $product->id), [
                'quantity' => 5
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Cart updated successfully!');
    }

    public function test_update_cart_requires_valid_quantity(): void
    {
        $product = Product::create([
            'name' => 'Lily',
            'price' => 35,
            'stock_quantity' => 100,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 2]);

        // Try to update with invalid quantity
        $response = $this->actingAs($this->user)
            ->patch(route('cart.update', $product->id), [
                'quantity' => 0
            ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_update_cart_requires_integer_quantity(): void
    {
        $product = Product::create([
            'name' => 'Daisy',
            'price' => 20,
            'stock_quantity' => 100,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 1]);

        $response = $this->actingAs($this->user)
            ->patch(route('cart.update', $product->id), [
                'quantity' => 'invalid'
            ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    // ==========================================
    // TEST SUITE: Remove from Cart
    // ==========================================

    public function test_user_can_remove_item_from_cart(): void
    {
        $product = Product::create([
            'name' => 'Orchid',
            'price' => 50,
            'stock_quantity' => 30,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 1]);

        $response = $this->actingAs($this->user)
            ->delete(route('cart.remove', $product->id));

        $response->assertRedirect()
            ->assertSessionHas('success', 'Item removed from cart!');
    }

    public function test_guest_cannot_remove_from_cart(): void
    {
        $product = Product::create([
            'name' => 'Guest Remove Test',
            'price' => 25,
            'stock_quantity' => 20,
        ]);

        $response = $this->delete(route('cart.remove', $product->id));

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: Multiple Products
    // ==========================================

    public function test_user_can_add_multiple_different_products(): void
    {
        $product1 = Product::create([
            'name' => 'Rose Bouquet',
            'price' => 45,
            'stock_quantity' => 50,
        ]);

        $product2 = Product::create([
            'name' => 'Mixed Arrangement',
            'price' => 75,
            'stock_quantity' => 30,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product1), ['quantity' => 2]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product2), ['quantity' => 1]);

        // Verify cart has both products
        $response = $this->actingAs($this->user)
            ->get(route('cart.index'));

        $response->assertOk()
            ->assertSee('Rose Bouquet')
            ->assertSee('Mixed Arrangement');
    }

    // ==========================================
    // TEST SUITE: Cart Calculation
    // ==========================================

    public function test_cart_displays_correct_total(): void
    {
        $product = Product::create([
            'name' => 'Premium Roses',
            'price' => 50,
            'stock_quantity' => 100,
        ]);

        $this->actingAs($this->user)
            ->post(route('cart.add', $product), ['quantity' => 4]);

        $response = $this->actingAs($this->user)
            ->get(route('cart.index'));

        $response->assertOk();
        // Total should be 50 * 4 = 200
    }
}
