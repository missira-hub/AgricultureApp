<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Display the farmer's sales history with full product and farmer details.
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
                'product:id,user_id,name,description,price,quantity,category_id,unit_id,image', 
                'product.user:id,name', // Farmer info
                'product.category:id,name', // Category
                'product.unit:id,name,abbreviation', // Unit
                'order:id,user_id,total_price,status,created_at'
            ])
            ->whereHas('product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status', 'paid'); // Only paid orders
            })
            ->select('id', 'order_id', 'product_id', 'quantity', 'price', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order->id,
                    'quantity' => $item->quantity,
                    'unit_price' => round($item->price, 2),
                    'total_price' => round($item->quantity * $item->price, 2),
                    'created_at' => $item->created_at,

                    // Full Product Data
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'description' => $item->product->description,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                        'category' => $item->product->category ? [
                            'id' => $item->product->category->id,
                            'name' => $item->product->category->name
                        ] : null,
                        'unit' => $item->product->unit ? [
                            'id' => $item->product->unit->id,
                            'name' => $item->product->unit->name,
                            'abbreviation' => $item->product->unit->abbreviation
                        ] : null,
                        'user' => $item->product->user ? [
                            'id' => $item->product->user->id,
                            'name' => $item->product->user->name
                        ] : null
                    ]
                ];
            });

        return response()->json($sales);
    }
}