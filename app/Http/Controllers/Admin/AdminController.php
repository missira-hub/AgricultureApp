<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function getUsers()
    {
        return response()->json([
            'users' => User::with(['consumer', 'farmer', 'admin'])->get()
        ]);
    }

    public function getAllUsers()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(User::all());
    }

    public function dashboardStats()
    {
        return response()->json([
            'user_count' => $this->getUserCount(),
            'product_count' => $this->getProductCount(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'average_rating' => $this->getAverageRating(),
            'recent_activities' => $this->getRecentActivities(),
            'latest_orders' => $this->getLatestOrders(),
            'latest_feedbacks' => $this->getLatestFeedbacks(),
        ]);
    }

    protected function getUserCount()
    {
        return DB::table('users')->count();
    }

    protected function getProductCount()
    {
        return DB::table('products')->count();
    }

    protected function getMonthlyRevenue()
    {
        $now = Carbon::now(); // ✅ You forgot to import Carbon
        return DB::table('orders')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
        ->where('status', 'completed')
        ->sum('total_price'); // correct column name
}

    protected function getTotalSales()
    {
        return DB::table('orders')
            ->where('status', 'completed')
            ->sum('total_price');
    }

    protected function getAverageRating()
    {
        return round(DB::table('feedback')->avg('rating'), 1);
    }

    protected function getRecentActivities()
    {
        $latestUsers = DB::table('users')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(fn($user) => [
                'type' => 'user_registration',
                'title' => 'New user registered',
                'description' => $user->name . ' signed up',
                'date' => $user->created_at,
                'icon' => '👥'
            ]);

        $latestOrders = DB::table('orders')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(fn($order) => [
                'type' => 'new_order',
                'title' => 'New order',
                'description' => 'Order #' . $order->id . ' placed',
                'date' => $order->created_at,
                'icon' => '💰',
                'value' => '₺' . $order->total_price
            ]);

        return $latestUsers->merge($latestOrders)->sortByDesc('date')->values();
    }

    protected function getLatestOrders()
    {
        return DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.name as user_name')
            ->orderBy('orders.created_at', 'desc')
            ->take(5)
            ->get();
    }

    protected function getLatestFeedbacks()
    {
        return DB::table('feedback')
            ->join('users', 'feedback.user_id', '=', 'users.id')
            ->join('products', 'feedback.product_id', '=', 'products.id')
            ->select('feedback.*', 'users.name as user_name', 'products.name as product_name')
            ->orderBy('feedback.created_at', 'desc')
            ->take(5)
            ->get();
    }
}
