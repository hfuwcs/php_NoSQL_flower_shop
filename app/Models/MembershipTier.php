<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class MembershipTier extends Model
{
    use HasFactory;
    
    protected $connection = 'mongodb';
    protected $collection = 'membership_tiers';

    protected $fillable = [
        'name',
        'min_points',
        'benefits',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'benefits' => 'array',
    ];
}