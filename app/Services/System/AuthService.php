<?php

namespace App\Services\System;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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

    public function forgetPassword($requestData)
    {
        $email = $requestData['email'];
        $role = $requestData['role'];

        $user = User::where('email', $email)->where('role', $role)->first();


        if (!$user) {
            return false;
        }

        $verifyCode = rand(100000, 999999);
        $codeExpiry = Carbon::now()->addMinutes(30);

        $user->update([
            'verify_code' => $verifyCode,
            'code_expiry_date' => $codeExpiry,
        ]);

        # TODO
        // Mail::to($user->email)->send(new VerifyEmail($verifyCode));

        return true;
    }

    public function resetPassword($requestData)
    {
        $user_id = Auth::id();

        $user = User::find($user_id);

        $password = $requestData['password'];
        $user->update([
            'password' => Hash::make($password),
        ]);

        $user->tokens()->delete();

        $newToken = $user->createToken('NewTokenName')->plainTextToken;

        return [
            'success' => true,
            'token' => $newToken,
        ];
    }

    public function logout($token)
    {
        return $token->delete();
    }



    // delete account from user
    // first he is request to delete account
    // we are send email to his, has code to delete account
    // insert code in user table
    // user send code to delete account
    // we are check code in user table
    // if code is correct we are delete account
    // if code is incorrect we are send error message
    public function requestDeleteAccount(User $user)
    {
        $verifyCode = rand(100000, 999999);
        $codeExpiry = Carbon::now()->addMinutes(30);

        $user->update([
            'verify_code' => $verifyCode,
            'code_expiry_date' => $codeExpiry,
        ]);

        # TODO
        // Mail::to($user->email)->send(new VerifyEmail($verifyCode));   
    }

    public function confirmDeleteAccount(User $user, $code)
    {
        if ($user->verify_code !== $code || Carbon::now()->greaterThan($user->code_expiry_date)) {
            return false;
        }

        if ($user->role == "client") {
            $user->client->delete();
        }
        if ($user->role == "vendor") {
            $user->vendor->delete();
        }
        $user->delete();

        return true;
    }
}
