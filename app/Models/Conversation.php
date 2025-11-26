<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'subject',
        'product_id',
        'consumer_id', // ✅ Add this if you're using it
    ];

    // ✅ Define the users in the conversation (many-to-many)
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
                    ->withPivot('last_read_at', 'is_hidden')
                    ->withTimestamps();
    }

    // ✅ Add a helper relationship: the "farmer" is the non-consumer user
    public function farmer()
    {
        return $this->users()->where('users.id', '!=', $this->consumer_id);
    }

    // ✅ Direct relationship: consumer who started the chat
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    // ✅ Messages
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // ✅ Product (if applicable)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ✅ Optional: Scope to conversations with a specific user
    public function scopeWithUser($query, $userId)
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }
}