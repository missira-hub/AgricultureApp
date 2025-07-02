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
        ->get();

    return response()->json($orders);
}

public function farmerProductOrders()
{
    $farmerId = auth()->id();

    // Get order items that involve the farmer's products
    $orderItems = OrderItem::with(['order.user', 'product'])
        ->whereHas('product', function ($query) use ($farmerId) {
            $query->where('user_id', $farmerId);
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($orderItems);
}

}