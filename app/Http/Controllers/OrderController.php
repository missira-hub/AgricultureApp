<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Buy a single product (from "Buy Now" button)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);

            if ($product->quantity < $request->quantity) {
                return response()->json(['message' => 'Insufficient stock'], 400);
            }

            $totalPrice = $product->price * $request->quantity;

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);

            $product->decrement('quantity', $request->quantity);

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully.',
                'order_id' => $order->id,
                'total' => $totalPrice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Order failed.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consumer checkout from cart
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();

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
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('quantity', $item->quantity);
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful',
                'order_id' => $order->id,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Checkout failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user's orders (consumer)
     */
    public function userOrders()
    {
        $user = auth()->user();

        $orders = Order::with(['items.product'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $formatted = $orders->getCollection()->map(function ($order) {
            return [
                'order_id' => $order->id,
                'order_date' => $order->created_at->format('F j, Y, g:i a'),
                'total_price' => number_format($order->total_price, 2),
                'status' => $order->status ?? 'Completed',
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Deleted Product',
                        'quantity' => $item->quantity,
                        'price_each' => number_format($item->price, 2),
                        'subtotal' => number_format($item->price * $item->quantity, 2),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $formatted,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Farmer view: summary of orders on their products
     */
    public function farmerProductOrders()
    {
        $farmerId = auth()->id();

        $products = Product::with(['orderItems', 'orderItems.order'])
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
                'unit_price' => number_format($product->price, 2),
                'total_earned' => number_format($earnedAmount, 2),
            ];
        });

        return response()->json($productSummary);
    }
}
