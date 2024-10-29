<?php

namespace App\Permissions;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderPermission
{
    public static function show() {}

    public static function create() {}

    public static function update(Order $order)
    {

        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = $order->vendor_id == $user->vendor->id;
                break;
            case 'client':
                $permission = false;
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
    public static function destory(Order $order)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = $order->vendor_id == $user->vendor->id;
                break;
            case 'client':
                $permission = false;
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
