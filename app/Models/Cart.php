<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'carts';

    protected $fillable = [
        'user_id',
        'items',
        'applied_coupon',
        'points_total',
        'membership',
    ];
    protected $attributes = [
        'points_total' => 0,
        'membership' => null,
    ];

    protected $casts = [
        'items' => 'array',
        'applied_coupon' => 'array',
        'points_total' => 'integer',
        'membership' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
