<?php

namespace App\Http\Controllers;

use App\Http\Requests\SystemReview\CreateRequest;
use App\Http\Requests\SystemReview\UpdateRequest;
use App\Http\Resources\SystemReviewResource;
use App\Permissions\SystemReviewPermission;
use App\Services\System\SystemReviewService;

class SystemReviewController extends Controller
{
    protected $systemReviewService;

    public function __construct(SystemReviewService $systemReviewService)
    {
        $this->systemReviewService = $systemReviewService;
    }

    public function index()
    {
        $reviews = $this->systemReviewService->index();
        return response()->json([
            'success' => true,
            'data' => SystemReviewResource::collection($reviews),
        ]);
    }

    public function show($id)
    {
        $review = $this->systemReviewService->getReviewById($id);
        SystemReviewPermission::view($review);

        return response()->json([
            'success' => true,
            'data' => new SystemReviewResource($review),
        ]);
    }

    public function create(CreateRequest $request)
    {
        SystemReviewPermission::create();
        $review = $this->systemReviewService->createReview($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully.',
            'data' => new SystemReviewResource($review),
        ]);
    }

    public function update($id, UpdateRequest $request)
    {
        $review = $this->systemReviewService->getReviewById($id);
        SystemReviewPermission::update($review);

        $updatedReview = $this->systemReviewService->updateReview($review, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'data' => new SystemReviewResource($updatedReview),
        ]);
    }

    public function destroy($id)
    {
        $review = $this->systemReviewService->getReviewById($id);
        SystemReviewPermission::delete($review);

        $this->systemReviewService->deleteReview($review);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}
