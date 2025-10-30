<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'order_items';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_at_purchase',
        'product_name',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_purchase' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}