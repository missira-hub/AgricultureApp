<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\FeedbackManagementController;
use App\Models\User;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Farmer\SalesController;





// ------------------- Auth Routes -------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/logout-all', [AuthController::class, 'logoutAll']);



// ------------------- Profile Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [ProfileController::class, 'show']);
Route::put('/user/profile', [ProfileController::class, 'updateProfile']);
Route::post('/farmer/profile', [FarmerController::class, 'update']);

});

// routes/api.php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

// In routes/api.php
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);


// ------------------- Admin Routes -------------------

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // User management
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::put('/users/{id}/upgrade', [UserManagementController::class, 'upgradeToFarmer']);
    Route::put('/users/{id}/block', [UserManagementController::class, 'blockUser']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard-stats', [AdminController::class, 'dashboardStats']);

    // Product management
    Route::get('/products', [ProductManagementController::class, 'index']);
    Route::delete('/products/{id}', [ProductManagementController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderManagementController::class, 'index']);
    Route::get('/orders/{id}', [OrderManagementController::class, 'show']);

    // Feedback
    Route::get('/feedback', [FeedbackManagementController::class, 'index']);
    Route::delete('/feedback/{id}', [FeedbackManagementController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/products', [ProductManagementController::class, 'index']);
    Route::delete('/products/{id}', [ProductManagementController::class, 'destroy']);
});



// ------------------- Farmer Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/farmer/dashboard', fn() => response()->json(['message' => 'Welcome to Farmer Dashboard']));
    Route::get('/farmer/products', [ProductController::class, 'index']);
    Route::post('/farmer/products', [ProductController::class, 'store']);
    Route::get('/farmer/products/{id}', [ProductController::class, 'show']);
    Route::put('/farmer/products/{id}', [ProductController::class, 'update']);
    Route::delete('/farmer/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/farmer/orders', [OrderController::class, 'farmerProductOrders']);
});

Route::middleware('auth:sanctum')->get('/farmer/sales-history', [OrderController::class, 'salesHistory']);


// ------------------- Consumer Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/consumer/dashboard', fn() => response()->json(['message' => 'Welcome to Consumer Dashboard']));

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
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);


    Route::middleware('auth:sanctum')->get('/cart/total', [CartController::class, 'getTotal']);


    // Product Feed
    Route::get('/products', [ProductController::class, 'feed']);
});

Route::middleware('auth:sanctum')->post('/user/avatar', [UserController::class, 'updateAvatar']);

// ------------------- Product Market Feed -------------------
Route::get('/market/feed', [ProductController::class, 'feed']);


// ------------------- Feedback Routes -------------------

// Public routes
Route::get('/feedbacks/approved', [FeedbackController::class, 'allApproved']);


// Authenticated user routes (review CRUD and queries)
Route::middleware('auth:sanctum')->group(function () {
    // CRUD routes for feedback (reviews)
    Route::post('/feedbacks', [FeedbackController::class, 'store']);
Route::put('/feedbacks/{feedback}', [FeedbackController::class, 'update']);
Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);

    // Queries for feedback
    Route::get('/feedbacks/product/{productId}', [FeedbackController::class, 'productFeedback']);
    Route::get('/feedbacks/farmer', [FeedbackController::class, 'farmerFeedback']);
    Route::get('/feedbacks/user', [FeedbackController::class, 'userReviews']);
});

// Admin-only routes (moderation)
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('/feedbacks', [FeedbackController::class, 'index']); // List all feedback paginated
    Route::post('/feedbacks/{id}/approve', [FeedbackController::class, 'approve']);
    Route::post('/feedbacks/{id}/reply', [FeedbackController::class, 'reply']);
    Route::delete('/feedbacks/{id}', [FeedbackController::class, 'destroy']); // Admin can also delete
});




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{conversationId}/messages', [ConversationController::class, 'messages']);
    Route::post('/conversations/{conversationId}/messages', [ConversationController::class, 'sendMessage']);
    Route::post('/conversations/{conversationId}/read', [ConversationController::class, 'markAsRead']);
    Route::post('/conversations/start', [ConversationController::class, 'startChat']);
});



Route::middleware('auth:sanctum')->post('/feedback', [FeedbackController::class, 'store']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::middleware('auth:sanctum')->get('/feed', [ProductController::class, 'feed']);
// in routes/api.php
Route::get('/units', function () {
    return \App\Models\Unit::all();
});


Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);

// routes/api.php
Route::get('/products/search', [ProductController::class, 'search']);


Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index']);
    Route::delete('/feedback/{id}', [\App\Http\Controllers\Admin\FeedbackController::class, 'destroy']);
    Route::post('/feedback/{id}/approve', [\App\Http\Controllers\Admin\FeedbackController::class, 'approve']);
});



Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/products', [ProductManagementController::class, 'index']);         // GET all products paginated
    Route::delete('/products/{id}', [ProductManagementController::class, 'destroy']); // DELETE product by id
});

Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {
    Route::put('/products/{id}', [ProductManagementController::class, 'update']);
    // other routes...
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/products', [ProductManagementController::class, 'store']);
    Route::put('/admin/products/{id}', [ProductManagementController::class, 'update']);
    Route::delete('/admin/products/{id}', [ProductManagementController::class, 'destroy']);
    Route::get('/admin/products', [ProductManagementController::class, 'index']);
});


Route::post('/webhook/stripe', [PaymentController::class, 'handleStripeWebhook']);
// routes/api.php
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/farmer/sales-history', [SalesController::class, 'salesHistory']);
});
Route::get('/payment-success', [PaymentController::class, 'success']);

// routes/api.php
Route::middleware('auth:sanctum')->post('/orders/{order}/checkout', [PaymentController::class, 'checkoutOrder']);
Route::middleware('auth:sanctum')->post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);

Route::get('/payment/verify', function (Request $request) {
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
    try {
        $session = \Stripe\Checkout\Session::retrieve($request->query('session_id'));
        
        return response()->json([
            'success' => true,
            'paid' => $session->payment_status === 'paid',
            'order_id' => $session->metadata->order_id ?? null,
            'session' => $session
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});