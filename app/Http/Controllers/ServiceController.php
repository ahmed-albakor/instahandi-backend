<?php

namespace App\Http\Controllers;

use App\Services\System\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function show($id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $service->main_image = asset("storage/" . $service->main_image);
        $service->images = $service->getImages();

        return response()->json([
            'success' => true,
            'data' => $service,
        ], 200);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $services = $this->serviceService->getAllServices($search, $request->limit);

        return response()->json([
            'success' => true,
            'data' => $services->items(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ]
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:8096',
            'description' => 'nullable|string',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = $this->serviceService->createService($validator->validated());

        $service->main_image = asset("storage/" . $service->main_image);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => $service,
        ]);
    }

    public function update(Request $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:8096',
            'description' => 'nullable|string',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = $this->serviceService->updateService($service, $validator->validated());

        $service->main_image = asset("storage/" . $service->main_image);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => $service,
        ]);
    }

    public function destroy($id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $this->serviceService->deleteService($service);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.',
        ]);
    }

    public function restore($id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found in archive.',
            ], 404);
        }

        $this->serviceService->restoreService($service);

        return response()->json([
            'success' => true,
            'message' => 'Service restored successfully.',
            'data' => $service,
        ]);
    }

    public function uploadAdditionalImages(Request $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
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

        $this->serviceService->storeAdditionalImages($request->file('additional_images'), $service->code);

        return response()->json([
            'success' => true,
            'message' => 'Additional images uploaded successfully.',
        ]);
    }

    public function deleteAdditionalImages(Request $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
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

        $this->serviceService->deleteAdditionalImages($validator->validated()['image_ids'], $service->code);

        return response()->json([
            'success' => true,
            'message' => 'Selected images deleted successfully.',
        ]);
    }
}
