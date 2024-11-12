<?php

namespace App\Services\System;

use App\Models\SystemReview;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;

class SystemReviewService
{
    public function index()
    {
        $query = SystemReview::query()->with('user');

        $user = Auth::user();
        if ($user->role != 'admin') {
            $query->where('user_id', $user->id);
        }

        $searchFields = ['review'];
        $numericFields = ['rating'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['user_id'];
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


    public function getReviewById($id)
    {
        $review = SystemReview::find($id);
        if (!$review) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Review not found.',
                ], 404)
            );
        }
        return $review;
    }

    public function createReview(array $data)
    {
        $user = Auth::user();
        $data['user_id'] = $user->id;
        return SystemReview::create($data);
    }

    public function updateReview(SystemReview $review, array $data)
    {
        $review->update($data);
        return $review;
    }

    public function deleteReview(SystemReview $review)
    {
        $review->delete();
    }
}
