<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use HasFactory, Searchable;

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

     /**
     * Lấy mảng dữ liệu có thể tìm kiếm cho model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
        ];
    }
}
