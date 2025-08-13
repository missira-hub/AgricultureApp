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
    return $this->belongsToMany(Conversation::class, 'conversation_user')
                ->withPivot('last_read_at', 'is_hidden')
                ->withTimestamps();
}

public function messages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

public function cart()
{
    return $this->hasMany(Cart::class);
}
public function cartItems() {
    return $this->hasMany(Cart::class); // or your cart model
}
public function consumer() {
    return $this->hasOne(Consumer::class);
}

public function farmer() {
    return $this->hasOne(Farmer::class);
}

public function admin() {
    return $this->hasOne(Admin::class);
}
// In your User model
protected $appends = ['avatar_url'];

public function getAvatarUrlAttribute()
{
    if ($this->avatar) {
        return '/storage/' . $this->avatar; // Adjust path if needed
    }
    return '/default-avatar.png'; // fallback
}
}
