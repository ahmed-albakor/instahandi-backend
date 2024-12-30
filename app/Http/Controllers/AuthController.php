<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RestPasswordRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Models\User;
use App\Services\Helper\FirebaseService;
use App\Services\System\AuthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $loginUserData = $request->validated();
    
        // تسجيل الدخول
        $user = $this->authService->login($loginUserData);
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Invalid Credentials',
            ], 401);
        }
    
        // إنشاء التوكن
        $token = $user->createToken($user->first_name . '-AuthToken')->plainTextToken;
    
        // التعامل مع device_token إذا كان موجودًا
        if ($request->has('device_token')) {
            $deviceToken = $request->device_token;
    
            // حفظ التوكن الأخير في قاعدة البيانات
            $latestToken = $user->tokens()->latest()->first();
            if ($latestToken) {
                $latestToken->update([
                    'device_token' => $deviceToken,
                ]);
            }
    
            // الاشتراك في التوبيكات
            $topics = [
                'user-' . $user->id,
                'role-' . $user->role,
                'all-users',
            ];
    
            foreach ($topics as $topic) {
                $subscriptionResult = FirebaseService::subscribeToTopic($deviceToken, $topic);
    
                if (!$subscriptionResult['success']) {
                    // تسجيل الخطأ إذا فشل الاشتراك
                    \Log::error('Failed to subscribe to topic', [
                        'topic' => $topic,
                        'device_token' => $deviceToken,
                        'error' => $subscriptionResult['error'] ?? 'Unknown error',
                    ]);
                }
            }
        }
    
        // الاستجابة النهائية
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'user' => $user,
        ]);
    }
س    


    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

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

        # TODO
        // Mail::to($user->email)->send(new VerifyEmail($verifyCode));

        return response()->json([
            'success' => true,
            'message' => 'Code send successfully, check you email.',
        ]);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }

        ## TODO: Un Comment This 
        // if (!$this->authService->verifyCode($user, $request->verify_code)) {
        //     return response()->json([
        //         'success' => false,
        //         'status' => 401,
        //         'message' => 'Invalid or expired verification code',
        //     ], 401);
        // }

        if ($user->approve == 0)
            $user->update([
                'approve' => 1
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

    public function forgetPassword(ForgetPasswordRequest $request)
    {

        $res = $this->authService->forgetPassword($request->validated());

        if (!$res) {
            return response()->json([
                'success' => false,
                'message' => 'this email not found in InstaHandi',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verify Code send to Email, check it.',
        ]);
    }

    public function resetPassword(RestPasswordRequest $request)
    {
        $res = $this->authService->resetPassword($request->validated());

        if ($res['success'] == true) {
            return response()->json(
                [
                    'success' => true,
                    'access_token' => $res['token'],
                    'message' => 'Password updated successfully'
                ],
                201,
            );
        }
    }

    public function logout()
    {
        $token = request()->bearerToken();

        $personalAccessToken = PersonalAccessToken::findToken($token);


        if ($personalAccessToken) {

            $deviceToken = $personalAccessToken->device_token;

            if ($deviceToken) {
                $user = Auth::user();
                FirebaseService::removeTopicFromToken($deviceToken, $user->id);
                FirebaseService::removeTopicFromToken($deviceToken, $user->role);
                FirebaseService::removeTopicFromToken($deviceToken, 'all-users');
            }

            // $personalAccessToken->delete();
        }


        $this->authService->logout($personalAccessToken);

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}
