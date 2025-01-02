<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientPayment\CreateRequest;
use App\Http\Requests\ClientPayment\UpdateRequest;
use App\Http\Resources\ClientPaymentResource;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Permissions\ClientPaymentPermission;
use App\Services\Helper\ResponseService;
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
            'meta' => ResponseService::meta($payments),
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
        $complete_payment = $request->complete_payment && $request->complete_payment == 1;
        if ($complete_payment) {
            // invoice total amount - payments amount = remaining amount 
            $payments = $service_request->payments;
            $totalPaidAmount = $payments->where('status', 'confirm')->sum('amount');
            $invoiceAmount = $service_request->invoice->price;
            $remainingAmount = $invoiceAmount - $totalPaidAmount;
            $amount = $remainingAmount * 100;
        }



        try {
            ClientPaymentPermission::create();

            $user = Auth::user();


            $customerEmail = $user->email;
            $customerName = $user->first_name . ' ' . $user->last_name;
            $customerPhone = $user->phone;

            $customerId = $this->stripeService->findOrCreateCustomer($customerEmail, $customerName, $customerPhone);

            $paymentIntent = $this->stripeService->createPaymentIntent($amount, 'usd', $customerId, ['country' => 'US']);

            $payment = $this->clientPaymentService->createPayment([
                'client_id' => $user->client->id,
                'service_request_id' => $service_request_id,
                'amount' => $amount / 100,
                'method' => 'stripe',
                'status' => 'pending',
                'description' => $complete_payment ? 'Complete Payment' : 'Initial Payment',
                'payment_data' => $paymentIntent,
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

    public function confirmPayment($id)
    {
        $paymentId = $id;

        // try {
        $payment = $this->clientPaymentService->getPaymentById($paymentId);

        $payment_data = is_string($payment->payment_data) ? json_decode($payment->payment_data, true) : $payment->payment_data;

        $paymentIntent = $this->stripeService->retrievePaymentIntent($payment_data['id']);

        if ($paymentIntent->status === 'succeeded') {
            $this->clientPaymentService->updatePayment($payment, [
                'status' => 'confirm',
                'payment_data' => json_encode($paymentIntent),
            ]);

            $serviveRequest = ServiceRequest::find($payment->service_request_id);

            $serviveRequest->can_job = 1;

            // if payments > invoice price then set invoice status to paid  and paid_at to current date
            // if service request has invoice
            if ($serviveRequest->invoice) {
                $payments = $serviveRequest->payments;
                $totalPaidAmount = $payments->where('status', 'confirm')->sum('amount');
                $invoiceAmount = $serviveRequest->invoice->price;
                if ($totalPaidAmount >= $invoiceAmount) {
                    $serviveRequest->invoice->status = 'paid';
                    $serviveRequest->invoice->paid_at = now();
                    $serviveRequest->invoice->save();
                }
            }

            $serviveRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment not successful.'
        ]);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }
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
