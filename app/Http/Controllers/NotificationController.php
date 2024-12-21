<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notification\CreateNotificationRequest;
use App\Http\Requests\Notification\UpdateReadStatusRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserNotificationResource;
use App\Services\System\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->notificationService->index();

        return response()->json([
            'success' => true,
            'data' => UserNotificationResource::collection($notifications),
        ]);
    }

    public function store(CreateNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $notification = $this->notificationService->createNotification($data);

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully.',
            'data' => new NotificationResource($notification),
        ]);
    }

    public function markAsRead(UpdateReadStatusRequest $request, $id): JsonResponse
    {
        $this->notificationService->markNotificationAsRead(
            $request->user()->id,
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    public function deleteUserNotification($id): JsonResponse
    {
        $this->notificationService->deleteUserNotification(Auth::id(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted for the user.',
        ]);
    }

    public function deleteNotification($notificationId): JsonResponse
    {
        $this->notificationService->deleteNotification($notificationId);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }
}
