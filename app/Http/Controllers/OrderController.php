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
     * Buy a single product (Buy Now)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            if ($product->quantity < $request->quantity) {
                return response()->json(['message' => 'Insufficient stock'], 400);
            }

            $totalPrice = $product->price * $request->quantity;

            $order = Order::create([
                'user_id'     => $user->id,
                'total_price' => $totalPrice,
                'status'      => 'pending',
            ]);

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'price'      => $product->price,
            ]);

            $product->decrement('quantity', $request->quantity);

            DB::commit();

            return response()->json([
                'message'  => 'Order placed successfully.',
                'order_id' => $order->id,
                'total'    => $totalPrice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Order failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Checkout all items in cart
     */
    public function checkout(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'consumer') {
            return response()->json(['message' => 'Only consumers can checkout'], 403);
        }

        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'     => $user->id,
                'total_price' => $total,
                'status'      => 'pending',
            ]);

            foreach ($cartItems as $item) {
                if ($item->product->quantity < $item->quantity) {
                    throw new \Exception("Not enough stock for {$item->product->name}");
                }

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);

                $item->product->decrement('quantity', $item->quantity);
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message'  => 'Checkout successful',
                'order_id' => $order->id,
                'total'    => $total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Checkout failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user's orders
     */
   public function userOrders()
{
    $orders = Order::with('items.product')
        ->where('user_id', auth()->id())
        ->orderByDesc('created_at')
        ->paginate(10);

    $orders->getCollection()->transform(function ($order) {
        $calculatedTotal = $order->items->sum(fn($item) => $item->price * $item->quantity);

        return [
            'order_id'    => $order->id,
            'order_date'  => $order->created_at->format('F j, Y, g:i a'),
            'total_price' => (float) $calculatedTotal,
            'status'      => $order->status ?? 'Completed',
            'items_count' => $order->items->count(),
            'items'       => $order->items->map(fn($item) => [
                'product_name' => $item->product->name ?? 'Deleted Product',
                'quantity'     => $item->quantity,
                'price_each'   => (float) $item->price,
                'subtotal'     => (float) ($item->price * $item->quantity),
            ]),
        ];
    });

    return response()->json([
        'data'       => $orders->items(),
        'pagination' => [
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
            'per_page'     => $orders->perPage(),
            'total'        => $orders->total(),
        ],
    ]);
}

    /**
     * Farmer summary of orders for their products
     */
    public function farmerProductOrders()
    {
        try {
            $products = Product::with(['orderItems.order'])
                ->where('user_id', auth()->id())
                ->get();

            $summary = $products->map(function ($product) {
                $soldQty = $product->orderItems->sum('quantity');
                $earned  = $product->orderItems->sum(fn($i) => $i->quantity * $i->price);

                return [
                    'product_id'       => $product->id,
                    'name'             => $product->name,
                    'description'      => $product->description,
                    'initial_quantity' => $product->quantity + $soldQty,
                    'sold_quantity'    => $soldQty,
                    'remaining_quantity' => $product->quantity,
                    'unit_price'       => (float) $product->price,
                    'total_earned'     => (float) $earned,
                ];
            });

            return response()->json($summary);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch farmer orders', 'details' => $e->getMessage()], 500);
        }
    }
    
public function salesHistory()
{
    $user = Auth::user();

    // Ensure the user is a farmer
    if ($user->role !== 'farmer') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Get all order items where the product belongs to the farmer
    $sales = OrderItem::with(['product', 'order'])
        ->whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($item) {
            return [
                'order_id'     => $item->order->id,
                'product_name' => $item->product->name ?? 'Unknown',
                'quantity'     => $item->quantity,
                'total_price'  => $item->quantity * $item->price,
                'created_at'   => $item->created_at->format('Y-m-d H:i'),
            ];
        });

    return response()->json($sales);
}
public function destroy($id)
{
    $order = Order::with('items')->find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    if ($order->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    try {
        // Delete related order items first (if foreign key constraints exist)
        if ($order->items()->exists()) {
            $order->items()->delete();
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete order',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
