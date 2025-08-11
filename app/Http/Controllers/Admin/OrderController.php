<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // View all orders
    public function index()
    {
        return Order::with(['user', 'orderItems.product'])->paginate(15);
    }

    // Show a specific order
    public function show($id)
    {
        return Order::with(['user', 'orderItems.product'])->findOrFail($id);
    }

    // Update order status (e.g., pending, shipped, delivered, cancelled)
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order,
        ]);
    }

    // Delete order (optional)
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }
}
