<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Queue;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class RewardTest extends TestCase
{
    use RefreshMongoDB;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: View Rewards Page
    // ==========================================

    public function test_authenticated_user_can_view_rewards_page(): void
    {
        $user = User::factory()->create(['points_total' => 500]);

        Reward::create([
            'name' => 'Test Reward',
            'description' => 'Test description',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('rewards.index'));

        $response->assertOk()
            ->assertViewIs('rewards.index')
            ->assertSee('Test Reward')
            ->assertViewHas('userPoints', 500);
    }

    public function test_guest_cannot_view_rewards_page(): void
    {
        $response = $this->get(route('rewards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_only_active_rewards_are_displayed(): void
    {
        $user = User::factory()->create();

        Reward::create([
            'name' => 'Active Reward',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        Reward::create([
            'name' => 'Inactive Reward',
            'type' => 'coupon',
            'point_cost' => 200,
            'reward_details' => ['type' => 'percent', 'value' => 20],
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('rewards.index'));

        $response->assertSee('Active Reward')
            ->assertDontSee('Inactive Reward');
    }

    // ==========================================
    // TEST SUITE: Redeem Reward - Success Cases
    // ==========================================

    public function test_user_can_redeem_reward_with_sufficient_points(): void
    {
        $user = User::factory()->create(['points_total' => 1000]);

        $reward = Reward::create([
            'name' => 'Discount 10%',
            'type' => 'coupon',
            'point_cost' => 500,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        $response->assertRedirect()
            ->assertSessionHas('success');

        // Verify points deducted
        $this->assertEquals(500, $user->fresh()->points_total);

        // Verify PointTransaction created
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => -500,
            'action_type' => 'reward_redeemed',
        ], 'mongodb');

        // Verify UserReward created
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $user->id,
            'reward_id' => $reward->id,
            'status' => 'claimed',
        ], 'mongodb');

        // Verify Coupon created
        $this->assertEquals(1, Coupon::count());
    }

    public function test_redeem_creates_coupon_with_correct_details(): void
    {
        $user = User::factory()->create(['points_total' => 500]);

        $reward = Reward::create([
            'name' => 'Fixed $20 Off',
            'type' => 'coupon',
            'point_cost' => 300,
            'reward_details' => ['type' => 'fixed', 'value' => 20],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        $coupon = Coupon::first();

        $this->assertEquals('fixed', $coupon->type);
        $this->assertEquals(20, $coupon->value);
        $this->assertEquals(1, $coupon->usage_limit);
        $this->assertEquals(0, $coupon->usage_count);
        $this->assertTrue($coupon->expires_at->isFuture());
    }

    public function test_user_can_redeem_exact_points(): void
    {
        $user = User::factory()->create(['points_total' => 200]);

        $reward = Reward::create([
            'name' => 'Exact Points Reward',
            'type' => 'coupon',
            'point_cost' => 200,
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        $response->assertSessionHas('success');
        $this->assertEquals(0, $user->fresh()->points_total);
    }

    // ==========================================
    // TEST SUITE: Redeem Reward - Failure Cases
    // ==========================================

    public function test_user_cannot_redeem_without_sufficient_points(): void
    {
        $user = User::factory()->create(['points_total' => 100]);

        $reward = Reward::create([
            'name' => 'Expensive Reward',
            'type' => 'coupon',
            'point_cost' => 500,
            'reward_details' => ['type' => 'percent', 'value' => 25],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        $response->assertRedirect()
            ->assertSessionHas('error', 'You do not have enough points to redeem this reward.');

        // Points unchanged
        $this->assertEquals(100, $user->fresh()->points_total);

        // No records created
        $this->assertEquals(0, Coupon::count());
        $this->assertEquals(0, UserReward::count());
    }

    public function test_user_cannot_redeem_with_zero_points(): void
    {
        $user = User::factory()->create(['points_total' => 0]);

        $reward = Reward::create([
            'name' => 'Any Reward',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        $response->assertSessionHas('error');
        $this->assertEquals(0, Coupon::count());
    }

    public function test_guest_cannot_redeem_reward(): void
    {
        $reward = Reward::create([
            'name' => 'Guest Test',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        $response = $this->post(route('rewards.redeem', $reward));

        $response->assertRedirect(route('login'));
    }

    // ==========================================
    // TEST SUITE: My Rewards Page
    // ==========================================

    public function test_user_can_view_their_redeemed_rewards(): void
    {
        $user = User::factory()->create(['points_total' => 1000]);

        $reward = Reward::create([
            'name' => 'My Test Reward',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        // Redeem the reward
        $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        // View my rewards
        $response = $this->actingAs($user)
            ->get(route('rewards.my'));

        $response->assertOk()
            ->assertViewIs('rewards.my-rewards')
            ->assertViewHas('userRewards');
    }

    public function test_user_sees_only_their_own_rewards(): void
    {
        $user1 = User::factory()->create(['points_total' => 1000]);
        $user2 = User::factory()->create(['points_total' => 1000]);

        $reward = Reward::create([
            'name' => 'Shared Reward',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        // User1 redeems
        $this->actingAs($user1)
            ->post(route('rewards.redeem', $reward));

        // User2 checks their rewards - should be empty
        $response = $this->actingAs($user2)
            ->get(route('rewards.my'));

        $response->assertOk();
        $this->assertEquals(0, $response->viewData('userRewards')->count());
    }

    // ==========================================
    // TEST SUITE: Point Transaction for Redemption
    // ==========================================

    public function test_point_transaction_has_correct_metadata(): void
    {
        $user = User::factory()->create(['points_total' => 500]);

        $reward = Reward::create([
            'name' => 'Metadata Test Reward',
            'type' => 'coupon',
            'point_cost' => 200,
            'reward_details' => ['type' => 'percent', 'value' => 15],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('rewards.redeem', $reward));

        $transaction = PointTransaction::where('user_id', $user->id)->first();

        $this->assertNotNull($transaction->metadata);
        $this->assertEquals($reward->id, $transaction->metadata['reward_id']);
        $this->assertEquals('Metadata Test Reward', $transaction->metadata['reward_name']);
    }

    // ==========================================
    // TEST SUITE: Multiple Redemptions
    // ==========================================

    public function test_user_can_redeem_multiple_rewards(): void
    {
        $user = User::factory()->create(['points_total' => 1000]);

        $reward1 = Reward::create([
            'name' => 'Reward 1',
            'type' => 'coupon',
            'point_cost' => 200,
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => true,
        ]);

        $reward2 = Reward::create([
            'name' => 'Reward 2',
            'type' => 'coupon',
            'point_cost' => 300,
            'reward_details' => ['type' => 'fixed', 'value' => 30],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('rewards.redeem', $reward1));

        $this->actingAs($user)
            ->post(route('rewards.redeem', $reward2));

        $this->assertEquals(500, $user->fresh()->points_total); // 1000 - 200 - 300
        $this->assertEquals(2, Coupon::count());
        $this->assertEquals(2, UserReward::where('user_id', $user->id)->count());
    }

    public function test_user_can_redeem_same_reward_multiple_times(): void
    {
        $user = User::factory()->create(['points_total' => 600]);

        $reward = Reward::create([
            'name' => 'Repeatable Reward',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => true,
        ]);

        // Redeem 3 times
        $this->actingAs($user)->post(route('rewards.redeem', $reward));
        $this->actingAs($user)->post(route('rewards.redeem', $reward));
        $this->actingAs($user)->post(route('rewards.redeem', $reward));

        $this->assertEquals(300, $user->fresh()->points_total); // 600 - 300
        $this->assertEquals(3, Coupon::count());
        $this->assertEquals(3, UserReward::where('user_id', $user->id)->count());
    }
}
