<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title',
        'content',
        'upvotes',
        'downvotes',
        'comments',
    ];
    
    protected $attributes = [
        'upvotes' => 0,
        'downvotes' => 0,
        'comments' => [],
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'string',
            'user_id' => 'string',
            'rating' => 'integer',
            'upvotes' => 'integer',
            'downvotes' => 'integer',
            'comments' => 'array',
        ];
    }

    public function setProductIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 24) {
            $this->attributes['product_id'] = new ObjectId($value);
        } elseif ($value instanceof ObjectId) {
            $this->attributes['product_id'] = $value;
        } else {
            $this->attributes['product_id'] = $value;
        }
    }

    /**
     * Set user_id attribute - convert string to ObjectId before saving
     */
    public function setUserIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 24) {
            $this->attributes['user_id'] = new ObjectId($value);
        } elseif ($value instanceof ObjectId) {
            $this->attributes['user_id'] = $value;
        } else {
            $this->attributes['user_id'] = $value;
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}