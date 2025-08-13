<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'price', 'quantity', 
        'image_url', 'category_id', 'farmer_id'
    ];


    protected $hidden = ['deleted_at'];

    /**
     * The farmer who owns the product.
     */
  public function user()
{
    return $this->belongsTo(User::class);
}


    /**
     * Alias for farmer (optional, same as user).
     */
public function farmer()
{
    return $this->belongsTo(User::class, 'farmer_id');
}


    /**
     * All feedback for this product.
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Order items linked to this product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
   // At the top, make sure this is imported:

public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}
public function unit()
{
    return $this->belongsTo(Unit::class);
}
public function product()
{
    return $this->belongsTo(Product::class);
}
public function consumer()
{
    return $this->belongsTo(User::class, 'consumer_id');
}

public function messages()
{
    return $this->hasMany(Message::class);
}

public function lastMessage()
{
    return $this->hasOne(Message::class)->latestOfMany();
}
}
