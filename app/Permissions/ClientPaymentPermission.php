<?php

namespace App\Permissions;

use App\Models\ClientPayment;
use Illuminate\Support\Facades\Auth;

class ClientPaymentPermission
{
    public static function view(ClientPayment $payment)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'client':
                $permission = $payment->client_id == $user->client->id;
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

   

        $permission = $user->role === 'client';

        if (!$permission) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Permissions error.',
                ], 403)
            );
        }
    }

    public static function update(ClientPayment $payment)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = false;
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

    public static function delete(ClientPayment $payment)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = false;
                break;
            case 'client':
                $permission = $payment->client_id == $user->client->id;
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
