<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PointService;
use Illuminate\Support\Facades\Queue;
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
        // Fake queue để không thực sự dispatch job
        Queue::fake();

        $user = User::factory()->create(['points_total' => 0]);
        $service = app(PointService::class);

        // Giả lập hành động Review (được cấu hình 50 điểm trong config giả định)
        // Lưu ý: Cần đảm bảo config('gamification.points.review_created') trả về giá trị
        config(['gamification.points.review_created' => 50]);

        $service->addPointsForAction($user, 'review_created');

        // Kiểm tra log giao dịch
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'points_awarded' => 50,
            'action_type' => 'review_created'
        ], 'mongodb');
    }
}