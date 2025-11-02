<?php

namespace App\Services;

use App\Jobs\UpdateUserPointsAndTierJob;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PointService
{
    /**
     * Ghi nhận điểm thưởng cho một hành động của người dùng.
     *
     * @param User $user Người dùng nhận điểm.
     * @param string $actionType Loại hành động (vd: 'review_created').
     * @param Model|null $relatedModel Model liên quan (vd: Review, Order).
     * @return void
     */
    public function addPointsForAction(User $user, string $actionType, ?Model $relatedModel = null): void
    {
        $pointsToAward = $this->calculatePoints($actionType, $relatedModel);

        if ($pointsToAward <= 0) {
            return;
        }

        PointTransaction::create([
            'user_id' => $user->id,
            'points_awarded' => $pointsToAward,
            'action_type' => $actionType,
            'metadata' => $relatedModel ? [
                'related_model' => get_class($relatedModel),
                'related_id' => $relatedModel->id,
            ] : null,
        ]);


        Log::info('Attempting to dispatch UpdateUserPointsAndTierJob.', ['user_id' => $user->id]);

        UpdateUserPointsAndTierJob::dispatch($user->id);

        Log::info('Successfully dispatched UpdateUserPointsAndTierJob.', ['user_id' => $user->id]);
    }

    /**
     * Tính toán số điểm cần cộng dựa trên hành động và model liên quan.
     *
     * @param string $actionType
     * @param Model|null $relatedModel
     * @return int
     */
    private function calculatePoints(string $actionType, ?Model $relatedModel): int
    {
        return match ($actionType) {
            'review_created' => (int) config('gamification.points.review_created', 0),
            'order_completed' => $this->calculateOrderPoints($relatedModel),
            default => 0,
        };
    }

    /**
     * Tính điểm cho đơn hàng đã hoàn thành.
     *
     * @param Model|null $order
     * @return int
     */
    private function calculateOrderPoints(?Model $order): int
    {
        if (!$order instanceof Order) {
            return 0;
        }

        $pointsPerDollar = (int) config('gamification.points.order_completed_per_dollar', 0);

        // Tính điểm dựa trên tổng tiền, làm tròn xuống
        return (int) floor($order->total_amount * $pointsPerDollar);
    }
}
