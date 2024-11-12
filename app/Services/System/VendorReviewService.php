<?php

namespace App\Services\System;

use App\Models\Order;
use App\Models\VendorReview;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;

class VendorReviewService
{
    public function index()
    {
        $query = VendorReview::query()->with(['vendor', 'client']);

        $searchFields = ['review'];
        $numericFields = ['rating'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['order_id', 'vendor_id', 'client_id'];
        $inFields = [];

        $reviews = FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $reviews;
    }

    public function getReviewById($id, $relationships = [])
    {
        $review = VendorReview::find($id);

        if (!$review) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Review not found.',
                ], 404)
            );
        }

        $review->load($relationships);

        return $review;
    }

    public function createReview(array $data)
    {
        return VendorReview::create($data);
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
