<?php

namespace App\Permissions;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;

class ProposalPermission
{

    public static function update(Proposal $proposal)
    {

        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = $proposal->vendor_id == $user->vendor->id;
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
    public static function destory(Proposal $proposal)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $permission = true;
                break;
            case 'vendor':
                $permission = $proposal->vendor_id == $user->vendor->id;
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

    public static function reject(Proposal $proposal)
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
                $permission = $proposal->serviceRequest->client->user->id == $user->id;
                break;
            default:
                $permission = false;
                break;
        }

        $permission = $proposal->status == 'pending';

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
