<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [
        'name',
        'description',
        'category',
        'images',
        'average_rating',
        'review_count',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'average_rating' => 'double',
            'review_count' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
