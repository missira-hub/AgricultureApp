<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

// ------------------- Public routes -------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ------------------- Common Authenticated User Route -------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------- Admin Routes -------------------
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Admin dashboard']);
    });

    // Add other admin routes here (user management, order management, etc.)
});

// ------------------- Farmer Routes -------------------
Route::middleware(['auth:sanctum', 'role:farmer'])->prefix('farmer')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/orders', [OrderController::class, 'farmerProductOrders']);
});
Route::middleware(['role:admin'])->get('/test-role', function () {
    return response()->json(['message' => 'Role middleware works!']);
});


// ------------------- Consumer Routes -------------------
// Routes that only authenticated consumers can access
Route::middleware(['auth:sanctum', 'role:consumer'])->group(function () {

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/checkout', [OrderController::class, 'placeOrder']);
    Route::get('/orders', [OrderController::class, 'userOrders']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->get('/test-role', function () {
    return response()->json(['message' => 'Role middleware works!']);
});
