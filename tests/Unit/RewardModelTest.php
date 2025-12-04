<?php

namespace Tests\Unit;

use App\Models\Reward;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class RewardTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: Reward Creation
    // ==========================================

    public function test_can_create_coupon_type_reward(): void
    {
        $reward = Reward::create([
            'name' => 'Discount 10%',
            'description' => 'Get 10% off your next purchase',
            'type' => 'coupon',
            'point_cost' => 500,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        $this->assertEquals('coupon', $reward->type);
        $this->assertEquals(500, $reward->point_cost);
        $this->assertTrue($reward->is_active);
    }

    public function test_can_create_fixed_coupon_reward(): void
    {
        $reward = Reward::create([
            'name' => '$20 Off',
            'description' => 'Get $20 off your purchase',
            'type' => 'coupon',
            'point_cost' => 800,
            'reward_details' => ['type' => 'fixed', 'value' => 20],
            'is_active' => true,
        ]);

        $this->assertEquals('fixed', $reward->reward_details['type']);
        $this->assertEquals(20, $reward->reward_details['value']);
    }

    public function test_can_create_physical_gift_reward(): void
    {
        $reward = Reward::create([
            'name' => 'Free Flower Vase',
            'description' => 'A beautiful ceramic vase',
            'type' => 'physical_gift',
            'point_cost' => 2000,
            'reward_details' => ['product_sku' => 'VASE-001'],
            'is_active' => true,
        ]);

        $this->assertEquals('physical_gift', $reward->type);
        $this->assertEquals('VASE-001', $reward->reward_details['product_sku']);
    }

    // ==========================================
    // TEST SUITE: Reward Attributes
    // ==========================================

    public function test_point_cost_is_cast_to_integer(): void
    {
        $reward = Reward::create([
            'name' => 'Test Reward',
            'type' => 'coupon',
            'point_cost' => '1000',
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => true,
        ]);

        $reward->refresh();

        $this->assertIsInt($reward->point_cost);
        $this->assertEquals(1000, $reward->point_cost);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $reward = Reward::create([
            'name' => 'Boolean Test',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => 1,
        ]);

        $reward->refresh();

        $this->assertIsBool($reward->is_active);
        $this->assertTrue($reward->is_active);
    }

    public function test_inactive_reward(): void
    {
        $reward = Reward::create([
            'name' => 'Inactive Reward',
            'type' => 'coupon',
            'point_cost' => 300,
            'reward_details' => ['type' => 'percent', 'value' => 15],
            'is_active' => false,
        ]);

        $this->assertFalse($reward->is_active);
    }

    // ==========================================
    // TEST SUITE: Reward Details (subdocument)
    // ==========================================

    public function test_reward_details_stores_percent_type(): void
    {
        $reward = Reward::create([
            'name' => 'Percent Reward',
            'type' => 'coupon',
            'point_cost' => 400,
            'reward_details' => ['type' => 'percent', 'value' => 25],
            'is_active' => true,
        ]);

        $reward->refresh();

        $this->assertEquals('percent', $reward->reward_details['type']);
        $this->assertEquals(25, $reward->reward_details['value']);
    }

    public function test_reward_details_stores_fixed_type(): void
    {
        $reward = Reward::create([
            'name' => 'Fixed Reward',
            'type' => 'coupon',
            'point_cost' => 600,
            'reward_details' => ['type' => 'fixed', 'value' => 50],
            'is_active' => true,
        ]);

        $reward->refresh();

        $this->assertEquals('fixed', $reward->reward_details['type']);
        $this->assertEquals(50, $reward->reward_details['value']);
    }

    // ==========================================
    // TEST SUITE: Query Active Rewards
    // ==========================================

    public function test_can_query_only_active_rewards(): void
    {
        Reward::create([
            'name' => 'Active 1',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => true,
        ]);

        Reward::create([
            'name' => 'Active 2',
            'type' => 'coupon',
            'point_cost' => 200,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        Reward::create([
            'name' => 'Inactive',
            'type' => 'coupon',
            'point_cost' => 300,
            'reward_details' => ['type' => 'percent', 'value' => 15],
            'is_active' => false,
        ]);

        $activeRewards = Reward::where('is_active', true)->get();

        $this->assertCount(2, $activeRewards);
    }

    public function test_rewards_can_be_ordered_by_point_cost(): void
    {
        Reward::create([
            'name' => 'Medium',
            'type' => 'coupon',
            'point_cost' => 500,
            'reward_details' => ['type' => 'percent', 'value' => 10],
            'is_active' => true,
        ]);

        Reward::create([
            'name' => 'Cheap',
            'type' => 'coupon',
            'point_cost' => 100,
            'reward_details' => ['type' => 'percent', 'value' => 5],
            'is_active' => true,
        ]);

        Reward::create([
            'name' => 'Expensive',
            'type' => 'coupon',
            'point_cost' => 1000,
            'reward_details' => ['type' => 'percent', 'value' => 20],
            'is_active' => true,
        ]);

        $rewards = Reward::orderBy('point_cost', 'asc')->get();

        $this->assertEquals('Cheap', $rewards[0]->name);
        $this->assertEquals('Medium', $rewards[1]->name);
        $this->assertEquals('Expensive', $rewards[2]->name);
    }
}
