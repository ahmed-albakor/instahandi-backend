<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(
            config('stripe.api_key.secret')
        );
    }

    public function pay()
    {
        $session = $this->stripe->checkout->sessions->create(
            [
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'T-shirt',
                                'description' => 'Test Desc',
                                'images' => [
                                    'https://media.istockphoto.com/id/1696167872/photo/aerial-view-of-forest-at-sunset-on-the-background-of-mountains-in-dolomites.webp?b=1&s=612x612&w=0&k=20&c=qVbrzFsB2EyzKv6hJxqx7nT4oBpElskLvxeLsI07G5Y=',
                                ],
                            ],
                            'unit_amount' => 2000,
                        ],
                        'quantity' => 2,
                    ],
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'My Service',
                            ],
                            'unit_amount' => 200000,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => 'http://localhost:8000/success',
                'cancel_url' => 'http://localhost:8000/cancel',
            ]
        );

        return redirect($session->url);
    }




    public function createPaymentIntent(Request $request)
    {
        // إعداد مفتاح Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $amount = $request->input('amount');
        $currency = $request->input('currency', 'usd');

        // إنشاء PaymentIntent
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }

    public function confirmPayment(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntentId = $request->input('paymentIntentId');

        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->confirm();

        if ($paymentIntent->status === 'succeeded') {
            return response()->json(['status' => 'success', 'message' => 'Payment completed successfully.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Payment failed.']);
        }
    }
}
