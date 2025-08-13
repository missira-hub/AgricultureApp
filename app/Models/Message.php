<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'message', 'is_read'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
{
    return $this->belongsTo(User::class, 'user_id');  // or 'sender_id' if it exists
}

    public function users()
{
    return $this->belongsToMany(User::class, 'conversation_user');
}

public function messages()
{
    return $this->hasMany(Message::class);
}

}
