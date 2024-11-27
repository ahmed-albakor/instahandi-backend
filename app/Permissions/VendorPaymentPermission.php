<?php

namespace App\Permissions;

use App\Models\VendorPayment;
use Illuminate\Support\Facades\Auth;

class VendorPaymentPermission
{
    public static function view(VendorPayment $payment)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = $payment->vendor_id == $user->vendor->id;
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

    public static function create()
    {
        $user = Auth::user();

   

        $permission = $user->role === 'vendor';

        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Permissions error.',
                ], 403)
            );
        }
    }

    public static function update(VendorPayment $payment)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = false;
                break;
            case 'vendor':
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

    public static function delete(VendorPayment $payment)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = false;
                break;
            case 'vendor':
                $permission = $payment->vendor_id == $user->vendor->id;
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
