<?php

namespace Tests\Feature;

use App\Models\Reward;
use App\Models\User;
use App\Models\Coupon;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class RewardRedemptionTest extends TestCase
{
    use RefreshMongoDB;

    protected Reward $reward;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
        // Tạo reward mẫu
        $this->reward = Reward::create([
            'name' => 'Test Coupon',
            'type' => 'coupon',
            'point_cost' => 500,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);
    }

    public function test_user_can_redeem_reward_with_sufficient_points()
    {
        // 1. Setup User có 1000 điểm
        $user = User::factory()->create(['points_total' => 1000]);

        // 2. Action: Gọi API đổi thưởng
        $response = $this->actingAs($user)
            ->post(route('rewards.redeem', $this->reward));

        // 3. Assert: Chuyển hướng thành công
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 4. Assert Database:
        // Điểm bị trừ đúng 500
        $this->assertEquals(500, $user->fresh()->points_total);

        // PointTransaction được ghi lại (điểm âm)
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => -500,
            'action_type' => 'reward_redeemed'
        ], 'mongodb');

        // UserReward được tạo
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $user->id,
            'reward_id' => $this->reward->id,
        ], 'mongodb');

        // Coupon thực sự được tạo ra trong collection coupons
        $this->assertEquals(1, Coupon::count());
    }

    public function test_user_cannot_redeem_without_sufficient_points()
    {
        // User chỉ có 100 điểm
        $user = User::factory()->create(['points_total' => 100]);

        $response = $this->actingAs($user)
            ->post(route('rewards.redeem', $this->reward));

        // Assert lỗi
        $response->assertSessionHas('error');
        
        // Điểm không đổi
        $this->assertEquals(100, $user->fresh()->points_total);
        
        // Không có coupon nào được tạo
        $this->assertEquals(0, Coupon::count());
    }
}