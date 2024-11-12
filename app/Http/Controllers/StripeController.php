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
            config('stripe.api_key.secret') // استخدم إعدادات المفتاح السري من ملف config/stripe.php
        );
    }

    public function createPaymentIntent(Request $request)
    {
        $amount = $request->input('amount');
        $currency = $request->input('currency', 'usd');

        try {
            // إنشاء PaymentIntent باستخدام كائن StripeClient
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmPayment(Request $request)
    {
        $paymentIntentId = $request->input('paymentIntentId');

        try {
            // استرجاع وتأكيد الدفع باستخدام StripeClient
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            $paymentIntent = $this->stripe->paymentIntents->confirm($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                return response()->json(['status' => 'success', 'message' => 'Payment completed successfully.']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Payment not successful.']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
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
}
