<?php

namespace App\Services\System;

use App\Models\VendorPayment;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;

class VendorPaymentService
{
    public function getAllPayments()
    {
        $query = VendorPayment::query()->with(['vendor.user', 'order.serviceRequest.service']);

        $user = Auth::user();
        if ($user->role == 'vendor') {
            $query->where('vendor_id', $user->vendor->id);
        }

        $searchFields = ['code', 'description'];
        $numericFields = ['amount'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['method', 'status', 'venodr_id', 'order_id'];
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
        $payment = VendorPayment::find($id);

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
        $VendorPayment = VendorPayment::create($data);

        $VendorPayment->update([
            'code' => 'VNDPY' . sprintf('%03d', $VendorPayment->id),
        ]);

        return $VendorPayment;
    }

    public function updatePayment(VendorPayment $payment, array $data)
    {
        $payment->update($data);
        return $payment;
    }

    public function deletePayment(VendorPayment $payment)
    {
        $payment->delete();
    }
}
