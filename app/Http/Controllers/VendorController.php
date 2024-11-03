<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendor\SetupProfileRequest;
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
}
