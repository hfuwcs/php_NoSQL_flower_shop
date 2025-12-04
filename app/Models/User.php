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
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các attributes cần cast
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
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
     * Kiểm tra user có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Kiểm tra user có quyền truy cập Filament admin panel không.
     * 
     * Yêu cầu:
     * - User phải có is_admin = true
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Chỉ kiểm tra admin panel
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }
        
        return false;
    }
}
