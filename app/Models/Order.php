<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'shipping_address',
        'payment_details',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'payment_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}