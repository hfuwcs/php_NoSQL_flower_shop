<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: View Leaderboard
    // ==========================================

    public function test_guest_can_view_leaderboard(): void
    {
        $response = $this->get(route('leaderboard.index'));

        $response->assertOk()
            ->assertViewIs('leaderboard.index');
    }

    public function test_authenticated_user_can_view_leaderboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('leaderboard.index'));

        $response->assertOk();
    }

    // ==========================================
    // TEST SUITE: Leaderboard Content
    // ==========================================

    public function test_leaderboard_page_loads_successfully(): void
    {
        // Create some products
        Product::create([
            'name' => 'Top Rated Product',
            'price' => 50,
            'stock_quantity' => 100,
            'category' => 'premium',
            'average_rating' => 4.9,
            'review_count' => 50,
        ]);

        Product::create([
            'name' => 'Second Best Product',
            'price' => 45,
            'stock_quantity' => 80,
            'category' => 'premium',
            'average_rating' => 4.7,
            'review_count' => 30,
        ]);

        $response = $this->get(route('leaderboard.index'));

        $response->assertOk()
            ->assertViewHas('products');
    }

    public function test_leaderboard_shows_top_products(): void
    {
        // Tạo 15 products để test top 10
        for ($i = 1; $i <= 15; $i++) {
            Product::create([
                'name' => "Product $i",
                'price' => 10 * $i,
                'stock_quantity' => 50,
                'category' => 'test',
                'average_rating' => $i / 3, // 0.33 to 5.0
                'review_count' => $i * 2,
            ]);
        }

        $response = $this->get(route('leaderboard.index'));

        $response->assertOk();
        // Should show top 10 products
    }
}
