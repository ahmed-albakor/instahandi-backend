<?php

namespace App\Http\Controllers;

use App\Models\ClientPayment;
use App\Models\User;
use App\Services\System\ClientPaymentService;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public $stripe;
    public $clientPaymentService;

    public function __construct(ClientPaymentService $clientPaymentService)
    {
        $this->stripe = new StripeClient(
            config('stripe.api_key.secret')
        );

        $this->clientPaymentService = $clientPaymentService;
    }

    public function createPaymentIntent(Request $request)
    {
        $amount = 500 * 100;
        $service_requests_id = 1;

        try {

            $user = User::find(3);
            $customerEmail = $user->email;
            $customerName = $user->first_name . ' ' . $user->last_name;
            $customerPhone = $user->phone;



            $existingCustomer = $this->stripe->customers->all([
                'email' => $customerEmail,
                'limit' => 1,
            ]);

            if (!empty($existingCustomer->data)) {
                $customerId = $existingCustomer->data[0]->id;
            } else {
                $newCustomer = $this->stripe->customers->create([
                    'email' => $customerEmail,
                    'name' => $customerName,
                    'phone' => $customerPhone,
                ]);
                $customerId = $newCustomer->id;
            }

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $customerId,
                'payment_method_types' => ['card'],
            ]);

            $payment = $this->clientPaymentService->createPayment([
                'client_id' => $user->client->id,
                'service_request_id' => $service_requests_id,
                'amount' => $amount,
                'method' => 'stripe',
                'status' => 'pending',
                'description' => '',
                'payment_data' => json_encode($paymentIntent),
            ]);

            return response()->json([
                'payment' => $payment,
                'client_secret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function confirmPayment(Request $request)
    {
        $paymentId = $request->input('payment_id');

        try {
            $payment = ClientPayment::find($paymentId);
            $payment_data = json_decode($payment->payment_data);

            $paymentIntent = $this->stripe->paymentIntents->retrieve($payment_data->id);

            if ($paymentIntent->status === 'succeeded') {
                $this->clientPaymentService->updatePayment($payment, [
                    'status' => 'confirm',
                    'payment_data' => json_encode($paymentIntent),
                ]);

                return response()->json(['status' => true, 'message' => 'Payment completed successfully.']);
            }

            return response()->json(['status' => false, 'message' => 'Payment not successful.']);
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
