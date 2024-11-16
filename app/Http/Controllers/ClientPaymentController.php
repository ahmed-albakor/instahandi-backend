<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientPayment\CreateRequest;
use App\Http\Requests\ClientPayment\UpdateRequest;
use App\Http\Resources\ClientPaymentResource;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Permissions\ClientPaymentPermission;
use App\Services\Helper\StripeService;
use App\Services\System\ClientPaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ClientPaymentController extends Controller
{
    protected $stripeService;
    protected $clientPaymentService;

    public function __construct(StripeService $stripeService, ClientPaymentService $clientPaymentService)
    {
        $this->stripeService = $stripeService;
        $this->clientPaymentService = $clientPaymentService;
    }

    public function index()
    {
        $payments = $this->clientPaymentService->getAllPayments();

        return response()->json([
            'success' => true,
            'data' => ClientPaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $payment = $this->clientPaymentService->getPaymentById($id);
        ClientPaymentPermission::view($payment);

        return response()->json([
            'success' => true,
            'data' => new ClientPaymentResource($payment),
        ]);
    }

    public function createPaymentIntent(CreateRequest $request)
    {
        $amount = 0;
        $service_request_id = $request->service_request_id;

        $service_request = ServiceRequest::find($service_request_id);

        $acceptedProposal = $service_request->proposals()
            ->where('status', 'accepted')
            ->first();

        if ($acceptedProposal) {
            $payment_type = $acceptedProposal->payment_type;
            $price = $acceptedProposal->price;
        } else {
            $payment_type = $service_request->payment_type;
            $price = $service_request->price;
        }

        if ($payment_type == 'flat_rate') {
            $amount = $price * 100;
        } elseif ($payment_type == 'hourly_rate') {
            $estimatedHours = $this->extractEstimatedHours($service_request->estimated_hours);
            $amount = $price * $estimatedHours * 100;
        }



        try {
            ClientPaymentPermission::create();


            $user = Auth::user();


            $customerEmail = $user->email;
            $customerName = $user->first_name . ' ' . $user->last_name;
            $customerPhone = $user->phone;

            $customerId = $this->stripeService->findOrCreateCustomer($customerEmail, $customerName, $customerPhone);

            $paymentIntent = $this->stripeService->createPaymentIntent($amount, 'usd', $customerId);

            $payment = $this->clientPaymentService->createPayment([
                'client_id' => $user->client->id,
                'service_request_id' => $service_request_id,
                'amount' => $amount,
                'method' => 'stripe',
                'status' => 'pending',
                'description' => '',
                'payment_data' => json_encode($paymentIntent),
            ]);
            // return response()->json('success');

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully.',
                'data' => new ClientPaymentResource($payment),
                'client_secret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function extractEstimatedHours($estimatedHours)
    {
        preg_match_all('/\d+/', $estimatedHours, $matches);

        if (!empty($matches[0])) {
            return (int) $matches[0][0];
        }


        return 2;
    }



    public function confirmPayment(Request $request)
    {
        $paymentId = request()->payment_id;

        try {
            $payment = $this->clientPaymentService->getPaymentById($paymentId);
            $payment_data = json_decode($payment->payment_data);

            $paymentIntent = $this->stripeService->retrievePaymentIntent($payment_data->id);

            if ($paymentIntent->status === 'succeeded') {
                $this->clientPaymentService->updatePayment($payment, [
                    'status' => 'confirm',
                    'payment_data' => json_encode($paymentIntent),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment completed successfully.'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment not successful.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update($id, UpdateRequest $request)
    {
        $payment = $this->clientPaymentService->getPaymentById($id);
        ClientPaymentPermission::update($payment);

        $updatedPayment = $this->clientPaymentService->updatePayment($payment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
            'data' => new ClientPaymentResource($updatedPayment),
        ]);
    }

    public function destroy($id)
    {
        $payment = $this->clientPaymentService->getPaymentById($id);
        ClientPaymentPermission::delete($payment);

        $this->clientPaymentService->deletePayment($payment);

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }
}
