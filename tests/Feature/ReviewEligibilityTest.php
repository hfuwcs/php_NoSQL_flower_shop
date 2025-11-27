<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class ReviewEligibilityTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    public function test_user_cannot_review_if_not_delivered()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        // Tạo đơn hàng chưa giao (shipped)
        $order = Order::create(['user_id' => $user->id, 'status' => 'processing']);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'delivery_status' => 'shipped', // Chưa phải delivered
            'review_deadline_at' => now()->addDays(7),
        ]);

        $payload = [
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'title' => 'Great',
            'content' => 'Content',
        ];

        // Cố tình gửi request
        $response = $this->actingAs($user)->post(route('reviews.store', $product), $payload);

        // Phải bị chặn (403 Forbidden)
        $response->assertForbidden();
    }

    public function test_user_can_review_if_delivered_and_within_time()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::create(['user_id' => $user->id]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'delivery_status' => 'delivered', // Đã giao
            'review_deadline_at' => now()->addDays(7), // Còn hạn
        ]);

        $payload = [
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'title' => 'Great',
            'content' => 'Content',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $product), $payload);

        // Thành công và chuyển hướng
        $response->assertRedirect(route('orders.history'));
        
        // Review được tạo trong DB - sử dụng Model query thay vì assertDatabaseHas
        // vì MongoDB lưu ObjectId không tương thích với assertDatabaseHas
        $review = \App\Models\Review::where('rating', 5)->first();
        $this->assertNotNull($review);
        $this->assertEquals((string) $user->id, (string) $review->user_id);
        $this->assertEquals((string) $product->id, (string) $review->product_id);
    }
}