<?php

namespace App\Http\Controllers;

use App\Services\System\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
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
            // Location Validator
            'street_address' => 'required|string',
            'exstra_address' => 'nullable|string',
            'country' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096'
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

        $this->clientService->setupClientProfile($validatedData, $user);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup successfully.',
        ]);
    }
}
