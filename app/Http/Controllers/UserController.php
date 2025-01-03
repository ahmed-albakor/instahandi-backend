<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\System\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $users = $this->userService->index($filters);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = $this->userService->show($id);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $user = $this->userService->show($id);
        $data = $request->validated();

        $updatedUser = $this->userService->update($user, $data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => new UserResource($updatedUser),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = $this->userService->show($id);
        $this->userService->delete($user);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
