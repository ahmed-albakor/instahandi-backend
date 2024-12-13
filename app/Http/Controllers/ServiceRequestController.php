<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest\CreateRequest;
use App\Http\Requests\ServiceRequest\HireVendorRequest;
use App\Http\Requests\ServiceRequest\UpdateRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Image;
use App\Models\Proposal;
use App\Permissions\ServiceRequestPermission;
use App\Services\Helper\ResponseService;
use App\Services\System\OrderService;
use App\Services\System\ServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
    protected $serviceRequestService;

    public function __construct(ServiceRequestService $serviceRequestService)
    {
        $this->serviceRequestService = $serviceRequestService;
    }

    public function index()
    {
        $serviceRequests = $this->serviceRequestService->index();

        return response()->json([
            'success' => true,
            'data' => ServiceRequestResource::collection($serviceRequests->items()),
            'meta' => ResponseService::meta($serviceRequests),
        ]);
    }


    public function show($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        $serviceRequest->load(['location', 'client.user.location', 'proposals.vendor.user', 'proposals.vendor.services', 'images', 'service']);


        return response()->json([
            'success' => true,
            'data' => new ServiceRequestResource($serviceRequest),
        ]);
    }

    public function store(CreateRequest $request)
    {
        $validatedData = $request->validated();

        $serviceRequest = $this->serviceRequestService->create($validatedData);

        $serviceRequest->load(['location', 'images', 'service']);

        return response()->json([
            'success' => true,
            'message' => 'Service request created successfully',
            'data' => $serviceRequest,
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::update($serviceRequest);

        $validatedData = $request->validated();

        $serviceRequest = $this->serviceRequestService->update($serviceRequest, $validatedData);

        $serviceRequest->load(['location', 'images', 'service']);


        return response()->json([
            'success' => true,
            'message' => 'Service request updated successfully',
            'data' => $serviceRequest,
        ]);
    }

    public function destroy($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::destory($serviceRequest);


        if (!in_array($serviceRequest->status, ['canceled', 'rejected', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => "You cannot delete the service request in {$serviceRequest->status} status.",
            ], 403);
        }

        $this->serviceRequestService->delete($serviceRequest);

        return response()->json([
            'success' => true,
            'message' => 'Service request deleted successfully',
        ]);
    }


    public function uploadAdditionalImages(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::update($serviceRequest);


        $validator = Validator::make($request->all(), [
            'additional_images' => 'array|required',
            'additional_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->serviceRequestService->storeAdditionalImages($request->file('additional_images'), $serviceRequest->code);

        $new_images = Image::where('code', $serviceRequest->code)->get();

        return response()->json([
            'success' => true,
            'message' => 'Additional images uploaded successfully.',
            'data' => $new_images,
        ]);
    }

    public function deleteAdditionalImages(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::update($serviceRequest);

        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->serviceRequestService->deleteAdditionalImages($validator->validated()['image_ids'], $serviceRequest->code);

        return response()->json([
            'success' => true,
            'message' => 'Selected images deleted successfully.',
        ]);
    }


    ########## vendor ########## 

    public function acceptServiceRequset($id, OrderService $orderService)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if ($serviceRequest->status != 'pending') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You cannot accept the service request if it not in status pending',
                ]
            );
        }

        $order = $this->serviceRequestService->acceptServiceRequest($serviceRequest, $orderService);

        return response()->json([
            'success' => true,
            'message' => 'Jop Created Successfully',
            'data' => $order,
        ], 201);
    }

    public function hireVendor($id, OrderService $orderService, HireVendorRequest $request)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        $proposal = Proposal::find($request->proposal_id);

        ServiceRequestPermission::hireVendor($serviceRequest, $proposal);

        $acceptedProposal = $serviceRequest->proposals()
            ->where('status', 'accepted')
            ->exists();


        if ($acceptedProposal || $serviceRequest->status == 'accepted') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You have already hired a vendor or the request is still pending.',
                ],
                400
            );
        }

        $order = $this->serviceRequestService->hireVendor($serviceRequest, $orderService, $proposal);

        return response()->json([
            'success' => true,
            'message' => 'Vendor hired successfully.',
            'data' => $order,
        ], 201);
    }
}
