<?php

namespace App\Jobs;

use App\Models\MembershipTier;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class UpdateUserPointsAndTierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user's ID.
     *
     * @var string
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $userId
     */
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('UpdateUserPointsAndTierJob started', ['userId' => $this->userId]);

        $user = User::find($this->userId);
        if (!$user) {
            Log::warning('UpdateUserPointsAndTierJob: User not found.', ['userId' => $this->userId]);
            return;
        }

        $totalPoints = PointTransaction::where('user_id', $user->id)->sum('points_awarded');
        Log::info('Calculated total points', ['userId' => $this->userId, 'totalPoints' => $totalPoints]);

        $newTier = MembershipTier::where('min_points', '<=', $totalPoints)
                                 ->orderBy('min_points', 'desc')
                                 ->first();

        $user->points_total = $totalPoints;
        
        if ($newTier) {
            $user->membership = [
                'tier_id' => $newTier->id,
                'name' => $newTier->name,
            ];
        } else {
            $user->membership = null;
        }

        $user->save();

        Redis::zadd('leaderboard:users:by_points', $totalPoints, $user->id);
        
        Log::info('User points and tier updated successfully', [
            'user_id' => $user->id, 
            'total_points' => $totalPoints,
            'new_tier' => $newTier ? $newTier->name : 'None'
        ]);
    }
}