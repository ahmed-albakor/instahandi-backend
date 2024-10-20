<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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

        $user = User::where('email', $loginUserData['email'])
            ->where('role', $loginUserData['role'])
            ->first();

        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
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
            // 'first_name' => 'required|string|max:255',
            // 'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:vendor,client',
            // 'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $verifyCode = Str::random(6);
        $codeExpiry = Carbon::now()->addMinutes(30);

        $user = User::create([
            'code' => Str::random(8),
            'first_name' => ' ',
            'last_name' => ' ',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => ' ',
            'approve' => 0,
            'verify_code' => $verifyCode,
            'code_expiry_date' => $codeExpiry,
        ]);

        $user->update(
            [
                'code' =>  'USR' . sprintf('%03d', $user->id),
            ]
        );

        // Mail::to($user->email)->send(new VerifyEmail($verifyCode));

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully. Please verify your email.',
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

        if ($user->verify_code !== $request->verify_code || Carbon::now()->greaterThan($user->code_expiry_date)) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Invalid or expired verification code',
            ], 401);
        }

        $user->update([
            'approve' => 1,
            'verify_code' => null,
            'code_expiry_date' => null,
        ]);

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Verification successful. Set up your profile.',
            'access_token' => $token,
            'role' => $user->role,
            'user' => $user,
        ]);
    }


    public function test()
    {
        return response()->json(
            [
                'success' => true,
                'message' => 'success test'
            ]
        );
    }
}
