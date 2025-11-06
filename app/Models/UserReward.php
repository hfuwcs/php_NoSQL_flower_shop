<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class UserReward extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'user_rewards';

    protected $fillable = [
        'user_id',
        'reward_id',
        'status',       // Trạng thái, vd: 'claimed' (đã đổi), 'used' (đã dùng)
        'claimed_at',
        'reward_data',  // Dữ liệu cụ thể của phần thưởng, vd: { "coupon_code": "USER1-REWARD-XYZ" }
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'reward_data' => 'array',
    ];

    /**
     * Phần thưởng này thuộc về User nào.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Thông tin gốc của phần thưởng này.
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}
