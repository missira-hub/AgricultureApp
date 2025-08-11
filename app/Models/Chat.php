<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id'];

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_user', 'chat_id', 'user_id');
    }
}