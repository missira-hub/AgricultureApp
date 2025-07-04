<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id', 'receiver_id', 'message', 'is_read', 'read_at',
    ];

    protected $dates = ['read_at', 'deleted_at', 'created_at', 'updated_at'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
