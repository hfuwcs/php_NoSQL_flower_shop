<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as MongoUser;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends MongoUser implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Kiểm tra user có quyền truy cập Filament admin panel không
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }
}
