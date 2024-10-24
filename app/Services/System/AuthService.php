<?php

namespace App\Services\System;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function login($loginUserData)
    {
        $user = User::where('email', $loginUserData['email'])
            ->where('role', $loginUserData['role'])
            ->first();

        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    public function register($requestData)
    {
        $verifyCode = rand(100000, 999999);
        $codeExpiry = Carbon::now()->addMinutes(30);

        $user = User::create([
            'code' => Str::random(8),
            'first_name' => ' ',
            'last_name' => ' ',
            'email' => $requestData['email'],
            'password' => Hash::make($requestData['password']),
            'role' => $requestData['role'],
            'phone' => ' ',
            'approve' => 0,
            'verify_code' => $verifyCode,
            'code_expiry_date' => $codeExpiry,
        ]);

        $user->update(['code' => 'USR' . sprintf('%03d', $user->id)]);

        return $user;
    }

    public function verifyCode($user, $verifyCode)
    {
        if ($user->verify_code !== $verifyCode || Carbon::now()->greaterThan($user->code_expiry_date)) {
            return false;
        }

        $user->update([
            'approve' => 1,
            'verify_code' => null,
            'code_expiry_date' => null,
        ]);

        return $user;
    }

    public function logout($token)
    {
        return $token->delete();
    }
}
