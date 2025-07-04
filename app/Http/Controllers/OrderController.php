<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
{
    $user = auth()->user();

    // Check if user is a consumer
    if ($user->role !== 'consumer') {
        return response()->json(['message' => 'Only consumers can checkout.'], 403);
    }

    $cartItems = Cart::with('product')
        ->where('user_id', $user->id)
        ->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    $total = $cartItems->sum(function ($item) {
        return $item->product->price * $item->quantity;
    });

    DB::beginTransaction();
    try {
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        Cart::where('user_id', $user->id)->delete();

        DB::commit();
        return response()->json([
            'message' => 'Checkout successful',
            'order_id' => $order->id,
            'total' => $total
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Checkout failed',
            'details' => $e->getMessage()
        ], 500);
    }
}


    // OrderController.php
public function userOrders()
{
    $user = auth()->user();

    $orders = Order::with(['items.product'])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->map(function ($order) {
            return [
                'order_id' => $order->id,
                'order_date' => $order->created_at->format('F j, Y, g:i a'),  // e.g. July 3, 2025, 11:57 am
                'total_price' => number_format($order->total_price, 2),
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price_each' => number_format($item->price, 2),
                        'subtotal' => number_format($item->price * $item->quantity, 2),
                    ];
                }),
                'status' => $order->status ?? 'Completed',  // Add status field if you have one
            ];
        });

    return response()->json($orders);
}


public function farmerProductOrders()
{
    $farmerId = auth()->id();

    // Get farmer's products with order items
    $products = \App\Models\Product::with(['orderItems', 'orderItems.order'])
        ->where('user_id', $farmerId)
        ->get();

    $productSummary = $products->map(function ($product) {
        $soldQuantity = $product->orderItems->sum('quantity');
        $earnedAmount = $product->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'initial_quantity' => $product->quantity + $soldQuantity,
            'sold_quantity' => $soldQuantity,
            'remaining_quantity' => $product->quantity,
            'unit_price' => $product->price,
            'total_earned' => $earnedAmount,
        ];
    });

    return response()->json($productSummary);
}


}