<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PointService;
use Illuminate\Support\Facades\Redis;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class PointAccumulationTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    public function test_points_are_added_and_leaderboard_updated()
    {
        // Mock Redis để không cần server thật khi test
        Redis::shouldReceive('zadd')
            ->once()
            ->with('leaderboard:users:by_points', 50, \Mockery::any());

        $user = User::factory()->create(['points_total' => 0]);
        $service = app(PointService::class);

        // Giả lập hành động Review (được cấu hình 50 điểm trong config giả định)
        // Lưu ý: Cần đảm bảo config('gamification.points.review_created') trả về giá trị
        config(['gamification.points.review_created' => 50]);

        $service->addPointsForAction($user, 'review_created');

        // Chạy job cập nhật điểm (vì job này dispatch sync trong môi trường test)
        // Kiểm tra User được cập nhật điểm trong DB
        $this->assertEquals(50, $user->fresh()->points_total);

        // Kiểm tra log giao dịch
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => 50,
            'action_type' => 'review_created'
        ], 'mongodb');
    }
}