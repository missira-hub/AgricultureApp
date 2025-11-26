<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    } catch (\Exception $e) {
        Log::error('Stripe webhook error: ' . $e->getMessage());
        return response('Webhook Error', 400);
    }

    Log::info('Stripe event received: ' . $event->type);

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        // Log to inspect metadata
        Log::info('Session metadata:', (array) $session->metadata);

        $orderId = $session->metadata->order_id ?? null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->status = 'paid';
                $order->save();

                Log::info("✅ Order #$orderId marked as paid.");
            } else {
                Log::warning("❌ Order not found: ID $orderId");
            }
        } else {
            Log::warning("❌ No order_id found in metadata.");
        }
    }

    return response('Webhook Handled', 200);
}
}