<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'reply',
        'approved',
    ];

    /**
     * The consumer who wrote the feedback.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The product being reviewed.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
