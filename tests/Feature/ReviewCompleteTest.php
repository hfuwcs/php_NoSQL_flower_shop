<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\PointService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Queue;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshMongoDB;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
        
        config([
            'gamification.points.review_created' => 10,
        ]);
    }

    // ==========================================
    // TEST SUITE: Review Creation
    // ==========================================

    public function test_user_can_create_review_for_delivered_order_item(): void
    {
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 50,
            'stock_quantity' => 100,
            'average_rating' => 0,
            'review_count' => 0,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 50,
            'shipping_address' => ['city' => 'HCM'],
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 50,
            'delivery_status' => 'delivered',
            'review_deadline_at' => Carbon::now()->addDays(7),
            'review_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'order_item_id' => $orderItem->id,
                'rating' => 5,
                'title' => 'Great product!',
                'content' => 'I love this flower arrangement.',
            ]);

        $response->assertRedirect(route('orders.history'))
            ->assertSessionHas('success');

        // Verify review created
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great product!',
        ], 'mongodb');

        // Verify order item updated with review_id
        $this->assertNotNull($orderItem->fresh()->review_id);
    }

    public function test_user_earns_points_for_creating_review(): void
    {
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);
        $product = Product::create([
            'name' => 'Points Test Product',
            'price' => 30,
            'stock_quantity' => 50,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 30,
            'shipping_address' => [],
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 30,
            'delivery_status' => 'delivered',
            'review_deadline_at' => Carbon::now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'order_item_id' => $orderItem->id,
                'rating' => 4,
                'title' => 'Nice',
                'content' => 'Good quality.',
            ]);

        // Verify point transaction created
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => 10,
            'action_type' => 'review_created',
        ], 'mongodb');
    }

    public function test_guest_cannot_create_review(): void
    {
        $product = Product::create([
            'name' => 'Guest Test',
            'price' => 25,
            'stock_quantity' => 30,
        ]);

        $response = $this->post(route('reviews.store', $product), [
            'rating' => 5,
            'title' => 'Test',
            'content' => 'Test content',
        ]);

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: Review Validation
    // ==========================================

    public function test_review_requires_rating(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 10, 'stock_quantity' => 50]);
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 10,
            'shipping_address' => [],
        ]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 10,
            'delivery_status' => 'delivered',
            'review_deadline_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'order_item_id' => $orderItem->id,
                'title' => 'Missing rating',
                'content' => 'Content here',
            ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_review_rating_must_be_between_1_and_5(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 10, 'stock_quantity' => 50]);
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 10,
            'shipping_address' => [],
        ]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 10,
            'delivery_status' => 'delivered',
            'review_deadline_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'order_item_id' => $orderItem->id,
                'rating' => 6, // Invalid
                'title' => 'Invalid rating',
                'content' => 'Content',
            ]);

        $response->assertSessionHasErrors(['rating']);
    }

    // ==========================================
    // TEST SUITE: Review Voting
    // ==========================================

    public function test_user_can_upvote_review(): void
    {
        $user = User::factory()->create();
        $reviewAuthor = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 10, 'stock_quantity' => 50]);

        $review = Review::create([
            'user_id' => $reviewAuthor->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great',
            'content' => 'Amazing product',
            'upvotes' => 0,
            'downvotes' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('reviews.vote', $review), [
                'vote_type' => 'up'
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_downvote_review(): void
    {
        $user = User::factory()->create();
        $reviewAuthor = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 10, 'stock_quantity' => 50]);

        $review = Review::create([
            'user_id' => $reviewAuthor->id,
            'product_id' => $product->id,
            'rating' => 3,
            'title' => 'OK',
            'content' => 'Average product',
            'upvotes' => 0,
            'downvotes' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('reviews.vote', $review), [
                'vote_type' => 'down'
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_vote_requires_valid_vote_type(): void
    {
        $user = User::factory()->create();
        $reviewAuthor = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 10, 'stock_quantity' => 50]);

        $review = Review::create([
            'user_id' => $reviewAuthor->id,
            'product_id' => $product->id,
            'rating' => 4,
            'title' => 'Good',
            'content' => 'Nice',
            'upvotes' => 0,
            'downvotes' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('reviews.vote', $review), [
                'vote_type' => 'invalid'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vote_type']);
    }

    // ==========================================
    // TEST SUITE: Review Create Form
    // ==========================================

    public function test_user_can_access_review_create_form(): void
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 20, 'stock_quantity' => 30]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 20,
            'shipping_address' => [],
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 20,
            'delivery_status' => 'delivered',
            'review_deadline_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->get(route('reviews.create', $orderItem));

        $response->assertOk()
            ->assertViewIs('reviews.create');
    }
}
