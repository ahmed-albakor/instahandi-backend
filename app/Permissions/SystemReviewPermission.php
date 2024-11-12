<?php

namespace App\Permissions;

use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\SystemReview;
use Illuminate\Support\Facades\Auth;

class SystemReviewPermission
{
    public static function view(SystemReview $review)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            default:
                $permission = $review->user_id == $user->id;
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

    public static function create()
    {
        $user = Auth::user();


        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $order = Order::where('vendor_id', $user->vendor->id)->first();
                $permission = $order;
                $message = ", You must get the first job to be able to review.";
                break;
                case 'client':
                    $serviceRequest = ServiceRequest::where('client_id', $user->client->id)->where('status', 'completed')->first();
                    $permission = $serviceRequest;
                    $message = ", You must request and complete at least one service to be eligible for review";
                break;
            default:
                $permission = false;
                break;
        }


        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => "Permissions error$message.",
                ], 403)
            );
        }
    }

    public static function update(SystemReview $review)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = $review->user_id == $user->id;
                break;
            default:
                $permission = $review->user_id == $user->id;
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

    public static function delete(SystemReview $review)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            default:
                $permission = $review->user_id == $user->id;
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
