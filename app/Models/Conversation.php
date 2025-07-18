<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['subject', 'product_id'];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at', 'is_hidden')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper method to add participants
    public function addParticipants(array $userIds)
    {
        return $this->users()->syncWithoutDetaching($userIds);
    }
    public function participants()
{
    return $this->belongsToMany(User::class, 'conversation_user', 'conversation_id', 'user_id')
                ->withPivot('last_read_at')
                ->withTimestamps();
}
public function lastMessage()
{
    return $this->hasOne(Message::class)->latestOfMany();
}

}