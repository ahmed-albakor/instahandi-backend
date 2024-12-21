<?php

namespace App\Services\System;

use App\Models\Notification;
use App\Models\UserNotification;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function index()
    {

        $query = UserNotification::query()->with(['notification']);

        $user = Auth::user();
        if ($user->role != 'admin') {
            $query->where('user_id', $user->id);
        }


        $searchFields = ['notification.title', 'notification.message'];

        $numericFields = [];

        $dateFields = ['created_at', 'read_at'];

        $exactMatchFields = ['notification_id', 'is_read'];

        $query =  FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields
        );

        return $query;
    }

    public function createNotification(array $data)
    {
        return DB::transaction(function () use ($data) {
            $users = $data['users'];
            unset($data['users']);

            $notification = Notification::create($data);

            foreach ($users as $userId) {
                UserNotification::create([
                    'user_id' => $userId,
                    'notification_id' => $notification->id,
                ]);
            }

            return $notification;
        });
    }

    public function markNotificationAsRead($userId, $id)
    {
        $userNotification = UserNotification::where('user_id', $userId)
            ->where('notification_id', $id)
            ->first();

        if (!$userNotification) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'User Notification not found.',
                ], 404)
            );
        }

        UserNotification::where('user_id', $userId)
            ->where('id', '<=', $userNotification->id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }


    public function deleteUserNotification($userId, $notificationId)
    {
        DB::transaction(function () use ($userId, $notificationId) {
            UserNotification::where('user_id', $userId)
                ->where('notification_id', $notificationId)
                ->delete();

            $remainingNotifications = UserNotification::where('notification_id', $notificationId)->count();

            if ($remainingNotifications === 0) {
                Notification::where('id', $notificationId)->delete();
            }
        });
    }


    public function deleteNotification($notificationId)
    {
        DB::transaction(function () use ($notificationId) {
            UserNotification::where('notification_id', $notificationId)->delete();
            Notification::where('id', $notificationId)->delete();
        });
    }
}
