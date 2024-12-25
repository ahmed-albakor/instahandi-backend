<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendor\CreateRequest;
use App\Http\Requests\Vendor\SetupProfileRequest;
use App\Http\Requests\Vendor\UpdateProfileRequest;
use App\Http\Requests\Vendor\UpdateRequest;
use App\Http\Resources\VendorResource;
use App\Http\Resources\UserResource;
use App\Services\Helper\ResponseService;
use App\Services\System\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }
    public function index()
    {
        $testimonials = $this->vendorService->index();

        return response()->json([
            'success' => true,
            'data' => VendorResource::collection($testimonials->items()),
            'meta' => ResponseService::meta($testimonials),
        ]);
    }


    public function show($id)
    {

        $vendor = $this->vendorService->getVendorById($id);

        $vendor->load(['user.images', 'user.location', 'services', 'reviews.client.user']);

        return response()->json([
            'success' => true,
            'data' => new VendorResource($vendor),
        ]);
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $vendor = $this->vendorService->create($data);

        $vendor->load(['user.images', 'user.location', 'services', 'reviews.client.user']);

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully.',
            'data' => new VendorResource($vendor),
        ], 201);
    }

    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $vendor = $this->vendorService->getVendorById($id);
        $data = $request->validated();

        $updatedVendor = $this->vendorService->update($vendor, $data);

        $vendor->load(['user.images', 'user.location', 'services', 'reviews.client.user']);

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully.',
            'data' => new VendorResource($updatedVendor),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $vendor = $this->vendorService->getVendorById($id);
        $this->vendorService->destroy($vendor);

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully.',
        ]);
    }


    public function setupProfile(SetupProfileRequest $request)
    {
        $user = Auth::user();

        if ($user->profile_setup) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already set up.',
            ], 400);
        }

        $validatedData = $request->validated();

        $this->vendorService->setupVendorProfile($validatedData, $user);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup successfully.',
        ]);
    }


    public function getData()
    {
        $user = $this->vendorService->profileData();

        return response()->json([
            'success' => true,
            'data' =>  new UserResource($user),
        ], 200);
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        $validatedData = $request->validated();


        $user = Auth::user();
        $updatedUser = $this->vendorService->updateProfile($validatedData, $user);

        return response()->json([
            'success' => true,
            'message' => 'Vendor profile updated successfully!',
            'data' => $updatedUser,
        ]);
    }
}
