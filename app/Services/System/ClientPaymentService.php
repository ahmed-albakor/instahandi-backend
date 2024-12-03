<?php

namespace App\Services\System;

use App\Models\ClientPayment;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;

class ClientPaymentService
{
    public function getAllPayments()
    {
        $query = ClientPayment::query()->with(['client.user', 'serviceRequest.service']);

        $user = Auth::user();
        if ($user->role == 'client') {
            $query->where('client_id', $user->client->id);
        }

        $searchFields = ['code', 'description'];
        $numericFields = ['amount'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['method', 'status', 'client_id', 'service_request_id'];
        $inFields = [];

        return FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );
    }

    public function getPaymentById($id)
    {
        $payment = ClientPayment::find($id);

        if (!$payment) {
            abort(response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404));
        }

        return $payment;
    }

    public function createPayment(array $data)
    {
        $data['code'] = rand(1111, 5555);
        $clientPayment = ClientPayment::create($data);

        $clientPayment->update([
            'code' => 'CLTPY' . sprintf('%03d', $clientPayment->id),
        ]);

        return $clientPayment;
    }

    public function updatePayment(ClientPayment $payment, array $data)
    {
        $payment->update($data);
        return $payment;
    }

    public function deletePayment(ClientPayment $payment)
    {
        $payment->delete();
    }
}
