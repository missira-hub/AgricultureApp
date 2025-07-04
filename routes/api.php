<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FeedbackController;



// ------------------- Public routes -------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ------------------- Authenticated User Route -------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------- Admin Routes -------------------
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index']);
    // Add more admin routes
});

// ----------- Admin Dashboard Route (optional for frontend use) -----------
Route::get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome to Admin Dashboard']);
});

// ----------- Farmer Dashboard Route -----------
Route::get('/farmer/dashboard', function () {
    return response()->json(['message' => 'Welcome to Farmer Dashboard']);
});

// ----------- Consumer Dashboard Route -----------
Route::get('/consumer/dashboard', function () {
    return response()->json(['message' => 'Welcome to Consumer Dashboard']);
});

// ------------------- Farmer Routes -------------------
Route::middleware('auth:sanctum')->prefix('farmer')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/orders', [OrderController::class, 'farmerProductOrders']);
});

// ------------------- Consumer Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'userOrders']);
});

// ------------------- Test Routes -------------------
Route::middleware('auth:sanctum')->get('/test-role', function () {
    return response()->json([
        'role' => auth()->user()->role,
        'message' => 'Role check successful.'
    ]);
});

// Moved outside the closure
Route::middleware('auth:sanctum')->put('/user/profile', [AuthController::class, 'updateProfile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('farmers/orders', [OrderController::class, 'farmerProductOrders']);
});

Route::middleware('auth:sanctum')->prefix('messages')->group(function () {
    Route::post('/send', [MessageController::class, 'send']);
    Route::get('/conversation/{userId}', [MessageController::class, 'conversation']);
    Route::get('/inbox', [MessageController::class, 'inbox']);
    Route::get('/unread-count', [MessageController::class, 'unreadCount']);
    Route::delete('/{id}', [MessageController::class, 'delete']);
    Route::post('/{id}/read', [MessageController::class, 'markAsRead']);
    Route::post('/typing', [MessageController::class, 'typing']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::put('/feedback/{feedback}', [FeedbackController::class, 'update']);
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy']);

    Route::get('/product/{id}/feedback', [FeedbackController::class, 'productFeedback']);
    Route::get('/farmer/feedback', [FeedbackController::class, 'farmerFeedback']);

    Route::post('/feedback/{feedback}/approve', [FeedbackController::class, 'approve']); // Admin
    Route::post('/feedback/{feedback}/reply', [FeedbackController::class, 'reply']); // Farmer
});



