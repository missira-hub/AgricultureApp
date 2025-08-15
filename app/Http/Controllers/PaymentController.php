<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Order;

class PaymentController extends Controller
{
    
    
public function createCheckoutSession(Request $request)
{
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $order = Order::with('items')->findOrFail($request->order_id);

    // Calculate total amount in smallest currency unit (e.g. cents or kuruş)
    $totalAmount = 0;
    foreach ($order->items as $item) {
        $totalAmount += $item->price * $item->quantity;
    }

    if ($totalAmount <= 0) {
        return response()->json(['message' => 'Invalid order amount'], 400);
    }

    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'try',
                'product_data' => ['name' => 'Order #' . $order->id],
                'unit_amount' => (int) round($totalAmount * 100),
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => env('FRONTEND_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => env('FRONTEND_URL') . '/payment-cancelled',
        'metadata' => [
            'order_id' => $order->id,
        ],
    ]);

    return response()->json(['sessionId' => $session->id]);
}


public function handleWebhook(Request $request)
{
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    } catch (\Exception $e) {
        Log::error('Stripe webhook signature error', ['message' => $e->getMessage()]);
        return response('Webhook Error', 400);
    }

    Log::info('Stripe webhook received', ['type' => $event->type]);

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $orderId = $session->metadata['order_id'] ?? null;

        Log::info('checkout.session.completed received', [
            'metadata' => $session->metadata ?? null,
            'order_id' => $orderId,
        ]);

        if ($orderId) {
            $order = Order::find($orderId);

            if ($order) {
                $order->status = 'paid';
                $order->save();
                Log::info("Order #{$orderId} status updated to PAID.");
            } else {
                Log::warning("Order #{$orderId} not found.");
            }
        } else {
            Log::warning('Order ID not found in metadata');
        }
    }

    return response('Webhook Handled', 200);
}

}
