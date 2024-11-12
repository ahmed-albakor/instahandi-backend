<?php

namespace App\Permissions;

use App\Models\Order;
use App\Models\VendorReview;
use Illuminate\Support\Facades\Auth;

class VendorReviewPermission
{
    public static function show(VendorReview $review)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = true;
                break;
            case 'client':
                $permission = true;
                break;
            default:
                $permission = false;
                break;
        }

        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Permissions error.',
                ], 403)
            );
        }
    }

    public static function create(Order $order)
    {
        $user = Auth::user();

        $permission = true;

        $permission = $user->role == 'client';

        if ($permission)
            $permission = $order->serviceRequest->client_id == $user->client->id;

        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Permissions error.',
                ], 403)
            );
        }
    }

    public static function update(VendorReview $review)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                // Vendors shouldn't update reviews, so permission is false
                $permission = false;
                break;
            case 'client':
                $permission = $review->client_id == $user->client->id;
                break;
            default:
                $permission = false;
                break;
        }

        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Permissions error.',
                ], 403)
            );
        }
    }

    public static function destroy(VendorReview $review)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'client':
                $permission = $review->client_id == $user->client->id;
                break;
            case 'vendor':
                $permission = false; // Vendors shouldn't delete reviews
                break;
            default:
                $permission = false;
                break;
        }

        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Permissions error.',
                ], 403)
            );
        }
    }
}
