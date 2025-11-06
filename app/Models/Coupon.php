<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'coupons';

    protected $fillable = [
        'code',
        'type', // 'fixed' (số tiền cố định) or 'percent' (phần trăm)
        'value', // Giá trị của mã giảm giá
        'expires_at', // Ngày hết hạn
        'usage_limit', // Giới hạn tổng số lần sử dụng
        'usage_count', // Số lần đã sử dụng
    ];

    protected $casts = [
        'value' => 'double',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
    ];

    /**
     * Kiểm tra xem mã giảm giá có hợp lệ để sử dụng hay không.
     */
    public function isValid(): bool
    {
        // Chưa hết hạn
        $isNotExpired = !$this->expires_at || $this->expires_at->isFuture();

        // Vẫn còn lượt sử dụng
        $hasUsesLeft = !$this->usage_limit || ($this->usage_count < $this->usage_limit);

        return $isNotExpired && $hasUsesLeft;
    }
}