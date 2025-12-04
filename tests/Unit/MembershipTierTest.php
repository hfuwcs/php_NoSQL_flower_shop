<?php

namespace Tests\Unit;

use App\Models\MembershipTier;
use App\Models\User;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class MembershipTierTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: Membership Tier Creation
    // ==========================================

    public function test_can_create_bronze_tier(): void
    {
        $tier = MembershipTier::create([
            'name' => 'Bronze',
            'min_points' => 0,
            'benefits' => [
                'discount_percent' => 0,
                'free_shipping_threshold' => 100,
            ],
        ]);

        $this->assertEquals('Bronze', $tier->name);
        $this->assertEquals(0, $tier->min_points);
    }

    public function test_can_create_silver_tier(): void
    {
        $tier = MembershipTier::create([
            'name' => 'Silver',
            'min_points' => 500,
            'benefits' => [
                'discount_percent' => 5,
                'free_shipping_threshold' => 75,
            ],
        ]);

        $this->assertEquals('Silver', $tier->name);
        $this->assertEquals(500, $tier->min_points);
    }

    public function test_can_create_gold_tier(): void
    {
        $tier = MembershipTier::create([
            'name' => 'Gold',
            'min_points' => 1500,
            'benefits' => [
                'discount_percent' => 10,
                'free_shipping_threshold' => 50,
                'priority_support' => true,
            ],
        ]);

        $this->assertEquals('Gold', $tier->name);
        $this->assertEquals(1500, $tier->min_points);
        $this->assertTrue($tier->benefits['priority_support']);
    }

    public function test_can_create_platinum_tier(): void
    {
        $tier = MembershipTier::create([
            'name' => 'Platinum',
            'min_points' => 5000,
            'benefits' => [
                'discount_percent' => 15,
                'free_shipping_threshold' => 0,
                'priority_support' => true,
                'exclusive_access' => true,
            ],
        ]);

        $this->assertEquals('Platinum', $tier->name);
        $this->assertEquals(5000, $tier->min_points);
        $this->assertEquals(0, $tier->benefits['free_shipping_threshold']);
    }

    // ==========================================
    // TEST SUITE: Tier Query
    // ==========================================

    public function test_can_query_tiers_by_min_points(): void
    {
        MembershipTier::create(['name' => 'Bronze', 'min_points' => 0, 'benefits' => []]);
        MembershipTier::create(['name' => 'Silver', 'min_points' => 500, 'benefits' => []]);
        MembershipTier::create(['name' => 'Gold', 'min_points' => 1500, 'benefits' => []]);

        // Find tier for user with 1000 points
        $tier = MembershipTier::where('min_points', '<=', 1000)
            ->orderBy('min_points', 'desc')
            ->first();

        $this->assertEquals('Silver', $tier->name);
    }

    public function test_can_get_tier_for_new_user(): void
    {
        MembershipTier::create(['name' => 'Bronze', 'min_points' => 0, 'benefits' => []]);
        MembershipTier::create(['name' => 'Silver', 'min_points' => 500, 'benefits' => []]);

        $tier = MembershipTier::where('min_points', '<=', 0)
            ->orderBy('min_points', 'desc')
            ->first();

        $this->assertEquals('Bronze', $tier->name);
    }

    public function test_can_get_highest_tier(): void
    {
        MembershipTier::create(['name' => 'Bronze', 'min_points' => 0, 'benefits' => []]);
        MembershipTier::create(['name' => 'Silver', 'min_points' => 500, 'benefits' => []]);
        MembershipTier::create(['name' => 'Gold', 'min_points' => 1500, 'benefits' => []]);
        MembershipTier::create(['name' => 'Platinum', 'min_points' => 5000, 'benefits' => []]);

        // User with 10000 points should get Platinum
        $tier = MembershipTier::where('min_points', '<=', 10000)
            ->orderBy('min_points', 'desc')
            ->first();

        $this->assertEquals('Platinum', $tier->name);
    }

    // ==========================================
    // TEST SUITE: Tier Benefits
    // ==========================================

    public function test_tier_benefits_contain_discount(): void
    {
        $tier = MembershipTier::create([
            'name' => 'Gold',
            'min_points' => 1500,
            'benefits' => [
                'discount_percent' => 10,
            ],
        ]);

        $this->assertArrayHasKey('discount_percent', $tier->benefits);
        $this->assertEquals(10, $tier->benefits['discount_percent']);
    }

    public function test_tier_benefits_contain_free_shipping_threshold(): void
    {
        $tier = MembershipTier::create([
            'name' => 'Silver',
            'min_points' => 500,
            'benefits' => [
                'free_shipping_threshold' => 75,
            ],
        ]);

        $this->assertArrayHasKey('free_shipping_threshold', $tier->benefits);
        $this->assertEquals(75, $tier->benefits['free_shipping_threshold']);
    }

    // ==========================================
    // TEST SUITE: Tier Order
    // ==========================================

    public function test_tiers_can_be_ordered_ascending(): void
    {
        MembershipTier::create(['name' => 'Gold', 'min_points' => 1500, 'benefits' => []]);
        MembershipTier::create(['name' => 'Bronze', 'min_points' => 0, 'benefits' => []]);
        MembershipTier::create(['name' => 'Silver', 'min_points' => 500, 'benefits' => []]);

        $tiers = MembershipTier::orderBy('min_points', 'asc')->get();

        $this->assertEquals('Bronze', $tiers[0]->name);
        $this->assertEquals('Silver', $tiers[1]->name);
        $this->assertEquals('Gold', $tiers[2]->name);
    }
}
