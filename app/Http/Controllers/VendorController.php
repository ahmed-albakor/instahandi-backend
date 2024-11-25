<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendor\SetupProfileRequest;
use App\Http\Requests\Vendor\UpdateProfileRequest;
use App\Http\Resources\VendorResource;
use App\Models\User;
use App\Services\Helper\ResponseService;
use App\Services\System\VendorService;
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
            'data' => $user
        ], 200);
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        $validatedData = $request->validated();


        $user = Auth::user();
        $updatedUser = $this->vendorService->updateProfile($validatedData, $user);

        return response()->json([
            'message' => 'Vendor profile updated successfully!',
            'user' => $updatedUser,
        ]);
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

        $vendor->load(['user.images', 'user.location', 'services', 'reviews']);

        return response()->json([
            'success' => true,
            'data' => new VendorResource($vendor),
        ]);
    }
}
