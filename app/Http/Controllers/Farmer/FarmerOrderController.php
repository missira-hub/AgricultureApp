<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FarmerOrderController extends Controller
{
    /**
     * Display all orders containing the farmer's products
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 🔐 Ensure user is a farmer
        if (!$user || $user->role !== 'farmer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $orders = Order::with([
                    'items.product:id,user_id,name,price,image,unit_id',
                    'items.product.unit:id,name,abbreviation',
                    'customer:id,name,phone', // Must have phone column
                    'address' // Must have order_addresses table
                ])
                ->whereHas('items.product', function ($query) use ($user) {
                    $query->where('user_id', $user->id); // Only farmer's products
                })
                ->whereIn('status', ['paid', 'shipped', 'delivered'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total_price' => $order->total_price,
                        'status' => $order->status,
                        'delivery_method' => $order->delivery_method ?? 'delivery',
                        'created_at' => $order->created_at,
                        'shipped_at' => $order->shipped_at,
                        'customer_name' => $order->customer?->name ?? 'Unknown Customer',
                        'customer_phone' => $order->customer?->phone ?? '',
                        'shipping_address' => $order->address?->full_address ?? 'No address provided',
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'product' => [
                                    'id' => $item->product->id,
                                    'name' => $item->product->name,
                                    'image' => $item->product->image,
                                    'unit' => $item->product->unit ? [
                                        'id' => $item->product->unit->id,
                                        'abbreviation' => $item->product->unit->abbreviation
                                    ] : null
                                ]
                            ];
                        })
                    ];
                });

            return response()->json($orders);
        } catch (\Exception $e) {
            \Log::error('FarmerOrderController@index failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'farmer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $order = Order::whereHas('items.product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($id);

            if ($order->status !== 'paid') {
                return response()->json(['error' => 'Only paid orders can be shipped'], 400);
            }

            $order->status = 'shipped';
            $order->shipped_at = now();
            $order->save();

            return response()->json([
                'message' => 'Order marked as shipped',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'shipped_at' => $order->shipped_at
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('markAsShipped failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'farmer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $order = Order::whereHas('items.product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($id);

            if ($order->status !== 'shipped') {
                return response()->json(['error' => 'Only shipped orders can be delivered'], 400);
            }

            $order->status = 'delivered';
            $order->save();

            return response()->json([
                'message' => 'Order marked as delivered',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('markAsDelivered failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }
}