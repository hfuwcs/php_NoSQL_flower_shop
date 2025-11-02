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
    ];

    protected $casts = [
        'items' => 'array',
        'applied_coupon'=>'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}