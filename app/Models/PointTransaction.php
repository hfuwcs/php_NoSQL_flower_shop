<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'point_transactions';

    protected $fillable = [
        'user_id',
        'points_awarded',
        'action_type',
        'metadata',
    ];

    protected $casts = [
        'points_awarded' => 'integer',
        'metadata' => 'array',
    ];

    public function user()
    {
        // Lưu ý: foreignKey là 'user_id', localKey là '_id' trên collection 'users'
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}