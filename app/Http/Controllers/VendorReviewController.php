<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorReview\CreateRequest;
use App\Http\Requests\VendorReview\UpdateRequest;
use App\Http\Resources\VendorReviewResource;
use App\Models\Order;
use App\Services\Helper\ResponseService;
use App\Services\System\VendorReviewService;
use App\Permissions\VendorReviewPermission;
use Illuminate\Http\JsonResponse;

class VendorReviewController extends Controller
{
    protected $vendorReviewService;

    public function __construct(VendorReviewService $vendorReviewService)
    {
        $this->vendorReviewService = $vendorReviewService;
    }

    public function index(): JsonResponse
    {
        $filters = request()->all();
        $reviews = $this->vendorReviewService->index($filters);

        return response()->json([
            'success' => true,
            'data' => VendorReviewResource::collection($reviews->items()),
            'meta' => ResponseService::meta($reviews),
        ]);
    }

    public function show($id): JsonResponse
    {
        $review = $this->vendorReviewService->show($id);

        return response()->json([
            'success' => true,
            'data' => new VendorReviewResource($review),
        ]);
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $order = Order::find($validatedData['order_id']);
        VendorReviewPermission::create($order);

        $review = $this->vendorReviewService->create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully.',
            'data' => new VendorReviewResource($review),
        ], 201);
    }

    public function update($id, UpdateRequest $request): JsonResponse
    {
        $review = $this->vendorReviewService->show($id);
        VendorReviewPermission::update($review);
        $review = $this->vendorReviewService->updateReview($review, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'data' => new VendorReviewResource($review),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $review = $this->vendorReviewService->show($id);
        VendorReviewPermission::destroy($review);
        $this->vendorReviewService->deleteReview($review);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}
