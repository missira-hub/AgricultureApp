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
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\API\ProfileController;

Route::middleware('auth:sanctum')->get('/user/profile', [ProfileController::class, 'show']);
Route::middleware('auth:sanctum')->put('/user/profile', [ProfileController::class, 'update']);


// ------------------- Public routes -------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/logout-all', [AuthController::class, 'logoutAll']);

// ------------------- Authenticated User Route -------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/user/update-profile', [UserController::class, 'updateProfile']);



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
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/farmer/products', [ProductController::class, 'index']);
    Route::post('/farmer/products', [ProductController::class, 'store']);
    Route::delete('/farmer/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/farmer/products/{id}', [ProductController::class, 'show']);
    Route::put('/farmer/products/{id}', [ProductController::class, 'update']);
    Route::delete('/farmer/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/farmer/orders', [OrderController::class, 'farmerProductOrders']);
});

// ------------------- Consumer Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    // Cart
    Route::get('/consumer/cart', [CartController::class, 'index']);
    Route::post('/consumer/cart', [CartController::class, 'store']);
    Route::put('/consumer/cart/{id}', [CartController::class, 'update']);
    Route::delete('/consumer/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/consumer/cart/clear', [CartController::class, 'clear']);

    // Orders
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'userOrders']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Product feed
    Route::get('/products', [ProductController::class, 'feed']);
});



// Moved outside the closure
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

    /*
    |--------------------------------------------------------------------------
    | Feedback Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('feedback')->group(function () {
        Route::post('/', [FeedbackController::class, 'store']);
        Route::put('/{feedback}', [FeedbackController::class, 'update']);
        Route::delete('/{feedback}', [FeedbackController::class, 'destroy']);
        Route::get('/product/{id}', [FeedbackController::class, 'productFeedback']);
        Route::get('/farmer', [FeedbackController::class, 'farmerFeedback']);
        Route::post('/{feedback}/approve', [FeedbackController::class, 'approve']); // Admin only
        Route::post('/{feedback}/reply', [FeedbackController::class, 'reply']); // Farmer only
    });

    Route::get('/market/feed', [ProductController::class, 'feed']);

    Route::get('/test-conversation', function() {
    $user1 = \App\Models\User::find(1);
    $user2 = \App\Models\User::find(2);
    
    $conversation = \App\Models\Conversation::create([
        'subject' => 'Test Conversation'
    ]);
    
    $conversation->users()->attach([$user1->id, $user2->id]);
    
    $message = $conversation->messages()->create([
        'user_id' => $user1->id,
        'body' => 'Hello there!'
    ]);
    
    event(new \App\Events\NewMessage($message));
    
    return $conversation;
});



