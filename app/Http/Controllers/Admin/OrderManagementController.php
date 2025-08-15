<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderManagementController extends Controller
{
    /**
     * Display all orders (with pagination) for admin
     */
    public function index(Request $request)
    {
        // 🔐 Ensure only admins can access
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->query('per_page', 10);
        $orders = Order::with([
                'user:id,name',           // Only load necessary user fields
                'order_items.product:id,name' // Only product name
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform for frontend
        $formattedOrders = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : ['name' => 'Unknown'],
                'total_price' => round($order->total_price, 2),
                'status' => $order->status,
                'created_at' => $order->created_at,
                'order_items' => $order->order_items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                        ] : ['name' => 'Deleted Product'],
                        'quantity' => $item->quantity,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $formattedOrders,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Update order status (admin)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'paid', 'shipped', 'delivered', 'cancelled'])
            ]
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;

        $order->status = $request->status;
        $order->save();

        // 🔔 Optional: broadcast event
        // event(new \App\Events\OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'previous_status' => $oldStatus,
                'updated_at' => $order->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Delete an order (admin)
     */
    public function destroy($id)
    {
        $user = request()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order = Order::with('order_items')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            // Optional: restore stock if not delivered
            if ($order->status !== 'delivered') {
                foreach ($order->order_items as $item) {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $product->increment('quantity', $item->quantity);
                    }
                }
            }

            $order->order_items()->delete();
            $order->delete();

            return response()->json(['message' => 'Order deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}