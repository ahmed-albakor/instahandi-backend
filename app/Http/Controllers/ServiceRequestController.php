<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\System\ServiceRequestService;
use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
    protected $serviceRequestService;

    public function __construct(ServiceRequestService $serviceRequestService)
    {
        $this->serviceRequestService = $serviceRequestService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $search = $request->input('search');
        $limit = $request->input('limit', 20);

        $serviceRequests = $this->serviceRequestService->index($search, $limit);

        $serviceRequests->getCollection()->transform(function ($serviceRequest) use ($user) {
            $serviceRequest->images = $serviceRequest->getImages();
            if ($serviceRequest->client->user) {
                if ($user->role == 'vendor')
                    $serviceRequest->client->user->makeHidden(['email', 'phone', 'approve', 'profile_setup', 'verify_code', 'code_expiry_date', 'email_verified_at', 'created_at', 'updated_at']);
                $serviceRequest->client->user->profile_photo = $serviceRequest->client->user->getProfilePhoto();
            }
            return $serviceRequest;
        });

        return response()->json([
            'success' => true,
            'data' => $serviceRequests->items(),
            'meta' => [
                'current_page' => $serviceRequests->currentPage(),
                'last_page' => $serviceRequests->lastPage(),
                'per_page' => $serviceRequests->perPage(),
                'total' => $serviceRequests->total(),
            ],
        ]);
    }


    public function show($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if (!$serviceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Service request not found.',
            ], 404);
        }

        $permission = $this->serviceRequestService->checkPermission($serviceRequest);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
            ], 403);
        }

        $user = Auth::user();


        $serviceRequest->images = $serviceRequest->getImages();

        $serviceRequest->load(['location', 'client.user']);

        if ($serviceRequest->client->user) {
            $serviceRequest->client->user->profile_photo = $serviceRequest->client->user->getProfilePhoto();
            if ($user->role == 'vendor')
                $serviceRequest->client->user->makeHidden(['email', 'phone', 'approve', 'profile_setup', 'verify_code', 'code_expiry_date', 'email_verified_at', 'created_at', 'updated_at']);
        }
        

        return response()->json([
            'success' => true,
            'data' => $serviceRequest,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'payment_type' => 'required|in:flat_rate,hourly_rate',
            'estimated_hours' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
            'service_id' => 'required|exists:services,id',
            'client_id' => 'required|exists:clients,id',
            // Location Validator
            'street_address' => 'required|string',
            'exstra_address' => 'nullable|string',
            'country' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            // Images Validator
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();
        $serviceRequest = $this->serviceRequestService->create($validatedData);

        $serviceRequest->images = $serviceRequest->getImages();
        $serviceRequest->load(['location']);

        return response()->json([
            'success' => true,
            'message' => 'Service request created successfully',
            'data' => $serviceRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if (!$serviceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Service request not found.',
            ], 404);
        }

        $permission = $this->serviceRequestService->checkPermission($serviceRequest);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,accepted,completed,rejected,canceled',
            'payment_type' => 'nullable|in:flat_rate,hourly_rate',
            'estimated_hours' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            // Location Validator
            'street_address' => 'nullable|sometimes|string',
            'exstra_address' => 'nullable|string',
            'country' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:20',
            // Images Validator
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();
        $serviceRequest = $this->serviceRequestService->update($serviceRequest, $validatedData);

        $serviceRequest->images = $serviceRequest->getImages();
        $serviceRequest->load(['location']);


        return response()->json([
            'success' => true,
            'message' => 'Service request updated successfully',
            'data' => $serviceRequest,
        ]);
    }

    public function destroy($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if (!$serviceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Service Request request not found.',
            ], 404);
        }

        $permission = $this->serviceRequestService->checkPermission($serviceRequest);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
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

        if (!$serviceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Service Request not found.',
            ], 404);
        }

        $permission = $this->serviceRequestService->checkPermission($serviceRequest);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
            ], 403);
        }

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

        $this->serviceRequestService->storeAdditionalImages($request->file('additional_images'), $service->code);

        return response()->json([
            'success' => true,
            'message' => 'Additional images uploaded successfully.',
        ]);
    }

    public function deleteAdditionalImages(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if (!$serviceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $permission = $this->serviceRequestService->checkPermission($serviceRequest);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
            ], 403);
        }

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

        $this->serviceRequestService->deleteAdditionalImages($validator->validated()['image_ids'], $service->code);

        return response()->json([
            'success' => true,
            'message' => 'Selected images deleted successfully.',
        ]);
    }
}
