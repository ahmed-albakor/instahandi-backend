<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Image;
use App\Models\Location;
use App\Models\Vendor;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
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

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'code' => $user->code,
            'account_type' => $validatedData['account_type'],
            'years_experience' => $validatedData['years_experience'],
            'longitude' => $validatedData['longitude'] ?? null,
            'latitude' => $validatedData['latitude'] ?? null,
            'has_crew' => $validatedData['has_crew'] ?? false,
            'crew_members' => $validatedData['crew_members'] ?? null,
        ]);

        $vendor->update([
            'code' => 'VND' . sprintf('%03d', $vendor->id),
        ]);

        Location::updateOrCreate(
            ['code' => $user->code],
            [
                'street_address' => $validatedData['street_address'],
                'exstra_address' => $validatedData['exstra_address'] ?? null,
                'country' => $validatedData['country'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zip_code' => $validatedData['zip_code'],
            ]
        );

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = ImageService::storeImage($request->file('profile_photo'), 'profile_photos');
            $user->update(['profile_photo' => $profilePhotoPath]);
        }

        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $image) {
                $imagePath = ImageService::storeImage($image, 'vendor_images');
                Image::create([
                    'code' => $user->code,
                    'path' => $imagePath,
                ]);
            }
        }

        $user->update([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone' => $validatedData['phone'],
            'description' => $validatedData['description'],
            'profile_setup' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup successfully.',
        ]);
    }
}
