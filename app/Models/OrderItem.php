<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_at_purchase',
        'product_name',
        'delivery_status',
        'delivered_at',
        'can_review_after',
        'review_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price_at_purchase' => 'decimal:2',
        'quantity' => 'integer',
        'delivered_at' => 'datetime',
        'can_review_after' => 'datetime',
    ];

    /**
     * Mối quan hệ: Item này thuộc về Order nào.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Mối quan hệ: Item này là của Product nào.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Mối quan hệ: Item này có bài đánh giá nào.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}