<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\CreateRequest;
use App\Http\Requests\Client\UpdateRequest;
use App\Http\Resources\ClientResource;
use App\Services\System\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    // الوظائف الأساسية
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $clients = $this->clientService->index($filters);

        return response()->json([
            'success' => true,
            'data' => ClientResource::collection($clients->items()),
            'meta' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $client = $this->clientService->show($id);

        return response()->json([
            'success' => true,
            'data' => new ClientResource($client),
        ]);
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $client = $this->clientService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully.',
            'data' => new ClientResource($client),
        ], 201);
    }

    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $client = $this->clientService->show($id);
        $data = $request->validated();

        $updatedClient = $this->clientService->update($client, $data);

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully.',
            'data' => new ClientResource($updatedClient),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $client = $this->clientService->show($id);
        $this->clientService->destroy($client);

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully.',
        ]);
    }

    // الوظائف القديمة
    public function setupProfile(Request $request): JsonResponse
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

    public function getData(): JsonResponse
    {
        $user = $this->clientService->profileData();

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        $updatedUser = $this->clientService->updateProfile($validatedData, $user);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data' => $updatedUser,
        ]);
    }
}
