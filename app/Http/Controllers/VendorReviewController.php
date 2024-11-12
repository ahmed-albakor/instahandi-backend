<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorReview\CreateRequest;
use App\Http\Requests\VendorReview\UpdateRequest;
use App\Http\Resources\VendorReviewResource;
use App\Models\Order;
use App\Models\VendorReview;
use App\Permissions\VendorReviewPermission;
use App\Services\Helper\ResponseService;
use App\Services\System\VendorReviewService;
use Illuminate\Support\Facades\Auth;

class VendorReviewController extends Controller
{
    protected $vendorReviewService;

    public function __construct(VendorReviewService $vendorReviewService)
    {
        $this->vendorReviewService = $vendorReviewService;
    }

    public function index()
    {
        $reviews = $this->vendorReviewService->index();

        return response()->json([
            'success' => true,
            'data' => VendorReviewResource::collection($reviews->items()),
            'meta' => ResponseService::meta($reviews)
        ]);
    }

    public function show($id)
    {
        $relationships = ['order', 'vendor', 'client'];
        $review = $this->vendorReviewService->getReviewById($id, $relationships);
        VendorReviewPermission::show($review);

        return response()->json([
            'success' => true,
            'data' => new VendorReviewResource($review),
        ]);
    }

    public function create(CreateRequest $request)
    {
        $data = $request->validated();

        $order = Order::find($data['order_id']);

        VendorReviewPermission::create($order);

        if ($order->status != 'completed') {
            return  response()->json([
                'success' => false,
                'message' => 'You cannot review because a Order not completed.',
            ]);
        }

        $vendor_review = VendorReview::where('order_id', $order->id)->first();

        if ($vendor_review) {
            return  response()->json([
                'success' => false,
                'message' => 'You can review on time on order.',
            ], 403);
        }

        $user = Auth::user();

        $data['client_id'] = $user->client->id;

        $data['vendor_id'] = $order->vendor_id;

        $review = $this->vendorReviewService->createReview($data);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully.',
            'data' => new VendorReviewResource($review),
        ]);
    }

    public function update($id, UpdateRequest $request)
    {
        $review = $this->vendorReviewService->getReviewById($id);
        VendorReviewPermission::update($review);
        $review = $this->vendorReviewService->updateReview($review, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'data' => new VendorReviewResource($review),
        ]);
    }

    public function destroy($id)
    {
        $review = $this->vendorReviewService->getReviewById($id);
        VendorReviewPermission::destroy($review);
        $this->vendorReviewService->deleteReview($review);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}
