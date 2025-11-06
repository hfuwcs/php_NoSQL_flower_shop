<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'rewards';

    protected $fillable = [
        'name',             // Tên phần thưởng, vd: "Discount Coupon 10%"
        'description',      // Mô tả chi tiết
        'type',             // Loại phần thưởng, vd: 'coupon'
        'point_cost',       // Số điểm cần để đổi
        'reward_details',   // Chi tiết của phần thưởng, vd: { "type": "percent", "value": 10 }
        'is_active',        // Bật/tắt phần thưởng
    ];

    protected $casts = [
        'point_cost' => 'integer',
        // 'reward_details' => 'array',
        'is_active' => 'boolean',
    ];
}