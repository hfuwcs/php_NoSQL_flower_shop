<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
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
            'price' => (float) $this->price, // Đảm bảo price là số
        ];
    }

    /**
     * Scope a query to only include products of a given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByCategory(Builder $query, ?string $category): Builder
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    /**
     * Scope a query to filter products by a price range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  float|null  $min
     * @param  float|null  $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByPriceRange(Builder $query, ?float $min, ?float $max): Builder
    {
        if ($min) {
            $query->where('price', '>=', $min);
        }
        
        if ($max) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }
}
