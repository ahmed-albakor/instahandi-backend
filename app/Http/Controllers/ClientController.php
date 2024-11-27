<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\SetupProfileRequest;
use App\Http\Requests\Client\UpdateProfileRequest;
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

        $this->clientService->setupClientProfile($validatedData, $user);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup successfully.',
        ]);
    }


    public function getData()
    {
        $user = $this->clientService->profileData();

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        $validatedData = $request->validated();

        $user = Auth::user();
        $updatedUser = $this->clientService->updateProfile($validatedData, $user);
        

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $updatedUser
        ]);
    }
}
