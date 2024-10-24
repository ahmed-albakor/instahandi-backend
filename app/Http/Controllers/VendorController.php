<?php

namespace App\Http\Controllers;

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

    public function setupProfile(Request $request)
    {
        $user = Auth::user();

        if ($user->profile_setup) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already set up.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:25',
            'description' => 'nullable|string',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:8096',
            'account_type' => 'required|in:Individual,Company',
            'years_experience' => 'required|integer|min:0',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
            'has_crew' => 'boolean',
            'crew_members' => 'nullable|json',
            // Location validator
            'street_address' => 'required|string',
            'exstra_address' => 'nullable|string',
            'country' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $this->vendorService->setupVendorProfile($validatedData, $user);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup successfully.',
        ]);
    }
}
