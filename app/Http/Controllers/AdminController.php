<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

class AdminController extends Controller
{
    // Admin dashboard summary
    public function index()
    {
        return response()->json([
            'users_count' => User::count(),
            'products_count' => Product::count(),
            'orders_count' => Order::count(),
            'latest_users' => User::latest()->take(5)->get(),
            'latest_orders' => Order::latest()->take(5)->with('user')->get(),
        ]);
    }
}
