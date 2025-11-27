<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Throwable;

class RewardController extends Controller
{
    /**
     * Hiển thị trang cửa hàng đổi thưởng.
     */
    public function index(): View
    {
        $rewards = Reward::where('is_active', true)
            ->orderBy('point_cost', 'asc')
            ->get();

        $userPoints = Auth::user()->points_total;

        return view('rewards.index', [
            'rewards' => $rewards,
            'userPoints' => $userPoints,
        ]);
    }

    /**
     * Xử lý yêu cầu đổi thưởng của người dùng.
     *
     * @param Reward $reward
     * @return RedirectResponse
     */
    public function redeem(Reward $reward): RedirectResponse
    {
        // Debug log
        Log::info('Reward details type:', ['type' => gettype($reward->reward_details)]);
        Log::info('Reward details value:', ['value' => $reward->reward_details]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Kiểm tra logic nghiệp vụ cơ bản trước khi vào transaction
        if ($user->points_total < $reward->point_cost) {
            return back()->with('error', 'You do not have enough points to redeem this reward.');
        }

        try {
            DB::transaction(function () use ($reward, $user) {


                $lockedUser = User::where('_id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->points_total < $reward->point_cost) {
                    throw new \Exception('Not enough points. The transaction has been rolled back.');
                }

                $lockedUser->decrement('points_total', $reward->point_cost);

                PointTransaction::create([
                    'user_id' => $lockedUser->id,
                    'points_awarded' => -$reward->point_cost,
                    'action_type' => 'reward_redeemed',
                    'metadata' => ['reward_id' => $reward->id, 'reward_name' => $reward->name]
                ]);

                $newCouponCode = $this->generateUniqueCouponForReward($lockedUser, $reward);
                //dd('PointTransaction OK');
                //dd($newCouponCode);

                Coupon::create([
                    'code' => $newCouponCode,
                    'type' => $reward->reward_details['type'],
                    'value' => $reward->reward_details['value'],
                    'usage_limit' => 1,
                    'usage_count' => 0,
                    'expires_at' => now()->addDays(30),
                ]);
                //dd('Coupon Create OK');


                UserReward::create([
                    'user_id' => $lockedUser->id,
                    'reward_id' => $reward->id,
                    'status' => 'claimed',
                    'claimed_at' => now(),
                    'reward_data' => [
                        'coupon_code' => $newCouponCode,
                    ],
                ]);
                $this->processRewardByType($lockedUser, $reward);
            });
        } catch (Throwable $e) {
            return back()->with('error', 'An error occurred while redeeming the reward: ' . $e->getMessage());
        }

        return back()->with('success', 'You have successfully redeemed "' . $reward->name . '"!');
    }
    /**
     * Hiển thị trang chứa các phần thưởng mà người dùng đã đổi.
     */
    public function myRewards(): View
    {
        $user = Auth::user();
        $userRewards = UserReward::where('user_id', $user->id)
            ->with('reward')
            ->latest('claimed_at')
            ->paginate(10);

        return view('rewards.my-rewards', [
            'userRewards' => $userRewards,
        ]);
    }

    /**
     * Phân luồng xử lý dựa trên loại phần thưởng.
     */
    protected function processRewardByType(User $user, Reward $reward): void
    {
        match ($reward->type) {
            'coupon', 'free_shipping' => $this->processCouponReward($user, $reward),
            'physical_gift' => $this->processPhysicalGiftReward($user, $reward),
            default => throw new \Exception("Unsupported reward type: {$reward->type}"),
        };
    }

    /**
     * Xử lý phần thưởng loại Coupon và Free Shipping.
     */
    protected function processCouponReward(User $user, Reward $reward): void
    {
        $newCouponCode = $this->generateUniqueCouponForReward($user, $reward);
        
        Coupon::create([
            'code' => $newCouponCode,
            'type' => $reward->reward_details['type'], // 'percent', 'fixed', 'free_shipping'
            'value' => $reward->reward_details['value'] ?? null, // Free ship có thể không có value
            'usage_limit' => 1,
            'expires_at' => now()->addDays(30),
        ]);
        
        UserReward::create([
            'user_id' => $user->id,
            'reward_id' => $reward->id,
            'status' => 'claimed',
            'claimed_at' => now(),
            'reward_data' => ['coupon_code' => $newCouponCode],
        ]);
    }

    /**
     * Xử lý phần thưởng quà tặng vật lý.
     */
    protected function processPhysicalGiftReward(User $user, Reward $reward): void
    {
        UserReward::create([
            'user_id' => $user->id,
            'reward_id' => $reward->id,
            'status' => 'claimed', // Admin sẽ đổi thành 'processed' sau khi xử lý
            'claimed_at' => now(),
            'reward_data' => [
                'product_sku' => $reward->reward_details['product_sku'] ?? 'N/A',
            ],
        ]);

        // Gửi thông báo cho admin (todo)
        // Cần tạo User admin và Notification này
        // $admin = User::where('is_admin', true)->first();
        // if ($admin) {
        //     Notification::send($admin, new AdminGiftRedeemed($user, $reward));
        // }
    }

    /**
     * Helper method to generate a unique coupon code.
     *
     * @param User $user
     * @param Reward $reward
     * @return string
     */
    private function generateUniqueCouponForReward(User $user, Reward $reward): string
    {
        $prefix = 'RD';
        $userIdPart = substr(strtoupper($user->id), -4);
        $rewardIdPart = substr(strtoupper($reward->id), -4);
        $randomPart = strtoupper(Str::random(6));

        return "{$prefix}-{$userIdPart}-{$rewardIdPart}-{$randomPart}";
    }
}
