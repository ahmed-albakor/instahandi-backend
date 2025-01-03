<?php

namespace App\Services\System;

use App\Models\User;
use App\Services\Helper\FilterService;
use App\Services\Helper\ImageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function index(array $filters = [])
    {
        $query = User::query()->with(['location', 'images']);

        $searchFields = ['first_name', 'last_name', 'email', 'phone'];
        $exactMatchFields = ['role', 'approve', 'profile_setup'];
        $numericFields = ['id'];
        $dateFields = ['created_at', 'updated_at'];

        $users = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields
        );

        return $users;
    }

    public function show($id)
    {
        $user = User::with(['location', 'images'])->find($id);

        if (!$user) {
            abort(response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404));
        }

        return $user;
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['code'] = 'USR' . rand(100000, 999999);

        $user = User::create($data);
        $user->update(['code' => 'USR' . sprintf('%03d', $user->id)]);

        if (isset($data['profile_photo'])) {
            $path = ImageService::storeImage($data['profile_photo'], 'users', $user->code);
            $user->update(['profile_photo' => $path]);
        }

        return $user;
    }

    public function update(User $user, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if (isset($data['profile_photo'])) {
            $path = ImageService::storeImage($data['profile_photo'], 'users', $user->code);
            $data['profile_photo'] = $path;
        }

        $user->update($data);

        return $user;
    }

    public function delete(User $user)
    {
        $user->delete();
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

        $user->delete();

        return true;
    }
}
