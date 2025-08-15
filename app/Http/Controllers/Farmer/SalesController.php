<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Display the farmer's sales history.
     * Only includes orders with status = 'paid'
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesHistory(Request $request)
    {
        $user = Auth::user();

        // Ensure the user is a farmer
        if (!$user || $user->role !== 'farmer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get order items where:
        // - Product belongs to this farmer
        // - Order status is 'paid'
        $sales = OrderItem::with([
                'product:id,name', 
                'order:id,user_id,total_price,status,created_at'
            ])
            ->whereHas('product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status', 'paid'); // 🔐 Only paid orders
            })
            ->select('id', 'order_id', 'product_id', 'quantity', 'price', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'order_id' => $item->order->id,
                    'product_name' => $item->product->name ?? 'Unknown Product',
                    'quantity' => $item->quantity,
                    'unit_price' => round($item->price, 2),
                    'total_price' => round($item->quantity * $item->price, 2),
                    'order_status' => $item->order->status,
                    'created_at' => $item->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json($sales);
    }
}