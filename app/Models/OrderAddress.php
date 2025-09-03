<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'full_address',
        'city',
        'postal_code',
        'phone',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}