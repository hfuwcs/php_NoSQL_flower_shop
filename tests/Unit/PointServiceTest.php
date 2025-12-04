<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Support\Facades\Queue;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class PointServiceTest extends TestCase
{
    use RefreshMongoDB;

    protected PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
        $this->pointService = app(PointService::class);
        
        // Cấu hình điểm thưởng cho test
        config([
            'gamification.points.review_created' => 10,
            'gamification.points.order_completed_per_dollar' => 10,
        ]);
    }

    // ==========================================
    // TEST SUITE: Add Points for Actions
    // ==========================================

    public function test_adds_points_for_review_created_action(): void
    {
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);

        $this->pointService->addPointsForAction($user, 'review_created');

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => 10,
            'action_type' => 'review_created',
        ], 'mongodb');
    }

    public function test_does_not_add_points_for_unknown_action(): void
    {
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);

        $this->pointService->addPointsForAction($user, 'unknown_action');

        $this->assertEquals(0, PointTransaction::where('user_id', $user->id)->count());
    }

    public function test_adds_points_for_order_completed_based_on_total(): void
    {
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 100.50, // $100.50 * 10 = 1005 điểm
            'shipping_address' => ['city' => 'HCM'],
        ]);

        $this->pointService->addPointsForAction($user, 'order_completed', $order);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => 1005, // floor(100.50 * 10)
            'action_type' => 'order_completed',
        ], 'mongodb');
    }

    public function test_order_points_are_zero_for_zero_total(): void
    {
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 0,
            'shipping_address' => ['city' => 'HCM'],
        ]);

        $this->pointService->addPointsForAction($user, 'order_completed', $order);

        // Không tạo transaction nếu điểm = 0
        $this->assertEquals(0, PointTransaction::where('user_id', $user->id)->count());
    }

    // ==========================================
    // TEST SUITE: Point Calculation
    // ==========================================

    public function test_order_points_calculation_floors_decimal(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 10.99, // 10.99 * 10 = 109.9 -> floor = 109
            'shipping_address' => [],
        ]);

        $this->pointService->addPointsForAction($user, 'order_completed', $order);

        $transaction = PointTransaction::where('user_id', $user->id)->first();
        $this->assertEquals(109, $transaction->points_awarded);
    }

    // ==========================================
    // TEST SUITE: PointTransaction Metadata
    // ==========================================

    public function test_point_transaction_stores_metadata_for_related_model(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 50,
            'shipping_address' => [],
        ]);

        $this->pointService->addPointsForAction($user, 'order_completed', $order);

        $transaction = PointTransaction::where('user_id', $user->id)->first();

        $this->assertNotNull($transaction->metadata);
        $this->assertEquals(Order::class, $transaction->metadata['related_model']);
        $this->assertEquals($order->id, $transaction->metadata['related_id']);
    }
}
