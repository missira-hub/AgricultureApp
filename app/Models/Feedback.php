<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;     // import User
use App\Models\Product;  // import Product

class Feedback extends Model
{
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment'];

    // Automatically eager load user (reviewer) and product with farmer when fetching feedback
    protected $with = ['user', 'product.user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
