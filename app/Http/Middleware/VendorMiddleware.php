<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VendorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not logged in. Please log in first.',
                'status' => 401
            ], 401);
        }

        $user = Auth::user();


        if ($user->approve != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please verify your account first.',
                'status' => 403
            ], 403);
        }

        if ($user->role !== 'vendor') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have vendor permissions.',
                'status' => 403
            ], 403);
        }

        if ($user->profile_setup !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile before proceeding.',
                'status' => 403
            ], 403);
        }

        return $next($request);
    }
}
