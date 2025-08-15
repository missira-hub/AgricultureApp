<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderManagementController extends Controller
{
    // List all orders with user and items
    public function index()
    {
        return Order::with(['user', 'orderItems.product'])->paginate(20);
    }

    // View single order details
    public function show($id)
    {
        return Order::with(['user', 'orderItems.product'])->findOrFail($id);
    }
}
