<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function salesHistory(Request $request)
    {
        $farmerId = $request->user()->id;

        $sales = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.user_id', $farmerId)
            ->select(
                'orders.id as order_id',
                'products.name as product_name',
                'order_items.quantity',
                'order_items.total_price',
                'orders.created_at'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return response()->json($sales);
    }
}
