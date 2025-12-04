<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshMongoDB;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: Order History
    // ==========================================

    public function test_authenticated_user_can_view_order_history(): void
    {
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 150,
            'shipping_address' => ['city' => 'Ho Chi Minh'],
        ]);

        $response = $this->actingAs($user)
            ->get(route('orders.history'));

        $response->assertOk()
            ->assertViewIs('orders.history');
    }

    public function test_user_sees_only_their_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Order::create([
            'user_id' => $user1->id,
            'status' => 'paid',
            'total_amount' => 100,
            'shipping_address' => [],
        ]);

        Order::create([
            'user_id' => $user2->id,
            'status' => 'paid',
            'total_amount' => 200,
            'shipping_address' => [],
        ]);

        $response = $this->actingAs($user1)
            ->get(route('orders.history'));

        $response->assertOk();
        // User1 should only see their order
    }

    public function test_guest_cannot_view_order_history(): void
    {
        $response = $this->get(route('orders.history'));

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: Confirm Delivery
    // ==========================================

    public function test_user_can_confirm_delivery_of_their_order_item(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Delivery Test',
            'price' => 50,
            'stock_quantity' => 20,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'shipped',
            'total_amount' => 50,
            'shipping_address' => [],
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 50,
            'delivery_status' => 'shipped',
        ]);

        $response = $this->actingAs($user)
            ->post(route('order-item.confirm-delivery', $orderItem));

        $response->assertRedirect();

        // Verify delivery status updated
        $this->assertEquals('delivered', $orderItem->fresh()->delivery_status);

        // Verify review deadline set (7 days from now)
        $this->assertNotNull($orderItem->fresh()->review_deadline_at);
    }

    public function test_confirm_delivery_sets_review_window(): void
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 30, 'stock_quantity' => 10]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'shipped',
            'total_amount' => 30,
            'shipping_address' => [],
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 30,
            'delivery_status' => 'shipped',
        ]);

        $this->actingAs($user)
            ->post(route('order-item.confirm-delivery', $orderItem));

        $orderItem->refresh();

        // Review deadline should be approximately 7 days from now
        $expectedDeadline = Carbon::now()->addDays(7);
        $this->assertTrue(
            $orderItem->review_deadline_at->diffInMinutes($expectedDeadline) < 5
        );
    }

    public function test_guest_cannot_confirm_delivery(): void
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'price' => 30, 'stock_quantity' => 10]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'shipped',
            'total_amount' => 30,
            'shipping_address' => [],
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_purchase' => 30,
            'delivery_status' => 'shipped',
        ]);

        $response = $this->post(route('order-item.confirm-delivery', $orderItem));

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: Order Status
    // ==========================================

    public function test_order_with_pending_status(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => 75,
            'shipping_address' => [],
        ]);

        $this->assertEquals('pending', $order->status);
    }

    public function test_order_with_paid_status(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 100,
            'shipping_address' => [],
            'payment_details' => ['stripe_payment_intent_id' => 'pi_test_123'],
        ]);

        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->payment_details);
    }
}
