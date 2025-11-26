<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Event;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'amount' => 'required|numeric|min:0.5',
            'product_name' => 'required|string|max:255',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $amountInCents = intval(round($request->amount * 100));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'try',
                        'product_data' => [
                            'name' => $request->product_name,
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/payment-cancel'),
                'client_reference_id' => $request->order_id,
            ]);

            return response()->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Stripe API error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stripe webhook handler to listen for checkout.session.completed events
     * and update order status accordingly.
     */
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret'); // set your webhook secret here

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the checkout.session.completed event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $orderId = $session->client_reference_id;

            if ($orderId) {
                $order = Order::find($orderId);

                if ($order) {
                    $order->status = 'paid';
                    $order->payment_intent = $session->payment_intent ?? null;
                    $order->save();
                } else {
                    Log::warning("Order with ID $orderId not found during webhook processing.");
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
