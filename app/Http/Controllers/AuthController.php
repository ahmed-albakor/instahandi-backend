<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\System\AuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,vendor,client',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginUserData = $validator->validated();

        $user = $this->authService->login($loginUserData);

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Invalid Credentials',
            ], 401);
        }

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'user' => $user,
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:vendor,client',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $this->authService->register($validator->validated());

        // Mail::to($user->email)->send(new VerifyEmail($verifyCode));

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully. Please verify your email.',
        ]);
    }


    public function sendCode()
    {
        $user = Auth::user();

        if ($user->approve == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Your account already Verified.',
            ]);
        }

        $verifyCode = rand(100000, 999999);
        $codeExpiry = Carbon::now()->addMinutes(30);

        $user->update([
            'verify_code' => $verifyCode,
            'code_expiry_date' => $codeExpiry,
        ]);

        // Mail::to($user->email)->send(new VerifyEmail($verifyCode));

        return response()->json([
            'success' => true,
            'message' => 'Code send successfully, check you email.',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'verify_code' => 'required|string|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }

        if (!$this->authService->verifyCode($user, $request->verify_code)) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Invalid or expired verification code',
            ], 401);
        }

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Verification successful. Set up your profile.',
            'access_token' => $token,
            'role' => $user->role,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        $this->authService->logout(PersonalAccessToken::findToken($token));

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}
