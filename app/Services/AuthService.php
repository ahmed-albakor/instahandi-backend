<?php


namespace App\Services;


use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

/**
 * خدمة المصادقة على عملية تسجيل الدخول
 */
class AuthService
{


    public static function login(array $request)
    {
        $username = $request['username'];
        $password = $request['password'];


        $user = User::where('username', 'LIKE', $username)->first();

        if ($user && Hash::check($password, $user->password)) {

            return [
                'api_token' => $user->createToken($user->name)->plainTextToken,
            ];
        }


        // ExceptionService::invalidCredentials();
        throw new Exception('error ex');
    }

    // delete the current token only
    public static function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    //delete all user tokens
    public static function logoutAll(User $user): bool
    {
        return $user->tokens()->delete();
    }
}
