<?php

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceRequestPermission
{
    public static function update(ServiceRequest $serviceRequest)
    {

        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = false;
                break;
            case 'client':
                $permission = $serviceRequest->client_id == $user->client->id;
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

    public static function destory(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = false;
                break;
            case 'client':
                $permission = $serviceRequest->client_id == $user->client->id;
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
