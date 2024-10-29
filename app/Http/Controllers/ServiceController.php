<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\CreateRequest;
use App\Http\Requests\Service\UpdateRequest;
use App\Http\Resources\ServiceResource;
use App\Services\Helper\ResponseService;
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

        $service->load("images");

        return response()->json([
            'success' => true,
            'data' => new ServiceResource($service),
        ], 200);
    }

    public function index()
    {
        $services = $this->serviceService->getAllServices();

        return response()->json([
            'success' => true,
            'data' => $services->items(),
            'meta' => ResponseService::meta($services)
        ]);
    }

    public function create(CreateRequest $request)
    {

        $service = $this->serviceService->createService($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => $service,
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

        $service = $this->serviceService->updateService($service, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => $service,
        ]);
    }

    public function destroy($id)
    {
        $service = $this->serviceService->getServiceById($id);

        $this->serviceService->deleteService($service);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.',
        ]);
    }

    public function uploadAdditionalImages(Request $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

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
