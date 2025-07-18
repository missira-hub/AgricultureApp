<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Feedback;

class Product extends Model
{
    use HasFactory, SoftDeletes;

   protected $fillable = ['name', 'description', 'price', 'quantity', 'image'];


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
        return $this->belongsTo(User::class, 'user_id');
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
}
