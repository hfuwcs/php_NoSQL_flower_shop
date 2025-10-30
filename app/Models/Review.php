<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
            'rating' => 'integer',
            'upvotes' => 'integer',
            'downvotes' => 'integer',
            'comments' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}