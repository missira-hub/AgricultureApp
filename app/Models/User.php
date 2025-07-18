<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens; // ✅ ADD THIS
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ✅ ADD HasApiTokens here

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function isFarmer()
{
    return $this->role === 'farmer';
}

public function isConsumer()
{
    return $this->role === 'consumer';
}
public function feedback()
{
    return $this->hasMany(Feedback::class, 'farmer_id')->where('approved', true);
}

public function averageRating()
{
    return $this->feedback()->avg('rating');
}
public function feedbackGiven()
{
    return $this->hasMany(Feedback::class, 'user_id');
}

public function products()
{
    return $this->hasMany(Product::class);
}

public function receivedFeedback()
{
    return $this->hasManyThrough(Feedback::class, Product::class);
}
public function conversations()
{
    return $this->belongsToMany(Conversation::class)
        ->withPivot('last_read_at', 'is_hidden')
        ->withTimestamps();
}

public function messages()
{
    return $this->hasMany(Message::class);
}
public function cart()
{
    return $this->hasMany(Cart::class);
}

}
