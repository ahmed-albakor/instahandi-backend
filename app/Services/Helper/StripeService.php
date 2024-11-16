<?php

namespace App\Services\Helper;

use Stripe\StripeClient;

class StripeService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.api_key.secret'));
    }

    public function findOrCreateCustomer($email, $name, $phone)
    {
        $existingCustomer = $this->stripe->customers->all([
            'email' => $email,
            'limit' => 1,
        ]);

        if (!empty($existingCustomer->data)) {
            return $existingCustomer->data[0]->id;
        }

        $newCustomer = $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
        ]);

        return $newCustomer->id;
    }

    public function createPaymentIntent($amount, $currency, $customerId)
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'payment_method_types' => ['card'],
        ]);
    }

    public function retrievePaymentIntent($paymentIntentId)
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }


    //
        // public function pay()
        // {
        //     try {
        //         $session = $this->stripeService->createCheckoutSession([
        //             'line_items' => [
        //                 [
        //                     'price_data' => [
        //                         'currency' => 'usd',
        //                         'product_data' => [
        //                             'name' => 'T-shirt',
        //                             'description' => 'Test Desc',
        //                             'images' => [
        //                                 'https://media.istockphoto.com/id/1696167872/photo/aerial-view-of-forest-at-sunset-on-the-background-of-mountains-in-dolomites.webp?b=1&s=612x612&w=0&k=20&c=qVbrzFsB2EyzKv6hJxqx7nT4oBpElskLvxeLsI07G5Y=',
        //                             ],
        //                         ],
        //                         'unit_amount' => 2000,
        //                     ],
        //                     'quantity' => 2,
        //                 ],
        //                 [
        //                     'price_data' => [
        //                         'currency' => 'usd',
        //                         'product_data' => [
        //                             'name' => 'My Service',
        //                         ],
        //                         'unit_amount' => 200000,
        //                     ],
        //                     'quantity' => 1,
        //                 ],
        //             ],
        //             'mode' => 'payment',
        //             'success_url' => 'http://localhost:8000/success',
        //             'cancel_url' => 'http://localhost:8000/cancel',
        //         ]);

        //         return redirect($session->url);
        //     } catch (\Exception $e) {
        //         return response()->json(['error' => $e->getMessage()], 500);
        //     }
        // }
    //

    }
