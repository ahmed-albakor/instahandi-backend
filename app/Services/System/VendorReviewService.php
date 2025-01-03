<?php

namespace App\Services\System;

use App\Models\Order;
use App\Models\VendorReview;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;

class VendorReviewService
{
    public function index($filters)
    {
        $query = VendorReview::query()->with(['client.user', 'order']);

        $searchFields = ['review'];
        $numericFields = ['rating'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['vendor_id', 'client_id', 'order_id'];
        $inFields = [];

        return FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );
    }

    public function show($id)
    {
        $review = VendorReview::with(['client.user', 'vendor.user', 'order'])->find($id);

        if (!$review) {
            abort(response()->json([
                'success' => false,
                'message' => 'Vendor review not found.',
            ], 404));
        }

        return $review;
    }

    public function create(array $validatedData)
    {
        // if role is client, set client_id to the authenticated user's client id
        if (Auth::user()->role === 'client') {
            $validatedData['client_id'] = Auth::user()->client->id;
        }
        // set vendor_id from the order
        $order = Order::findOrFail($validatedData['order_id']);
        $validatedData['vendor_id'] = $order->vendor_id;
        // التحقق من حالة الطلب (يجب أن يكون مكتملًا)
        $order = Order::findOrFail($validatedData['order_id']);
        if ($order->status !== 'completed') {
            abort(response()->json([
                'success' => false,
                'message' => 'You can only review a completed order.',
            ], 403));
        }

        // التحقق من وجود تقييم مسبق
        $existingReview = VendorReview::where('order_id', $validatedData['order_id'])->exists();
        if ($existingReview) {
            abort(response()->json([
                'success' => false,
                'message' => 'This order has already been reviewed.',
            ], 400));
        }

        return VendorReview::create($validatedData);
    }

    public function updateReview(VendorReview $review, array $data)
    {
        $review->update($data);
        return $review;
    }

    public function deleteReview(VendorReview $review)
    {
        $review->delete();
    }
}
