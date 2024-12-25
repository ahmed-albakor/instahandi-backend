<?php

namespace App\Services\System;

use App\Models\Client;
use App\Models\Image;
use App\Models\User;
use App\Services\Helper\FilterService;
use App\Services\Helper\ImageService;
use App\Services\System\UserService;
use Illuminate\Support\Facades\Auth;

class ClientService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // الوظائف الأساسية
    public function index($filters)
    {
        $query = Client::query()->with(['user']);

        $searchFields = ['user.first_name', 'user.email', 'user.phone'];
        $numericFields = ['id'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['user_id'];
        $inFields = [];

        return FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );
    }

    public function show($id)
    {
        $client = Client::with(['user'])->find($id);

        if (!$client) {
            abort(response()->json([
                'success' => false,
                'message' => 'Client not found.',
            ], 404));
        }

        return $client;
    }

    public function create(array $validatedData, User $user = null)
    {
        if (!$user) {
            $validatedData['user']['role'] = 'client';
            $user = $this->userService->create($validatedData['user']);
        }

        $validatedData['user_id'] = $user->id;
        unset($validatedData['user']);
        
        $validatedData['code'] = rand(1111, 5555);

        $client = Client::create($validatedData);
        $client->update(['code' => 'CLT' . sprintf('%03d', $client->id)]);

        LocationsService::updateOrCreate(
            ['code' => $user->code],
            [
                'street_address' => $validatedData['street_address'],
                'exstra_address' => $validatedData['exstra_address'] ?? null,
                'country' => $validatedData['country'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zip_code' => $validatedData['zip_code'],
            ]
        );

        return $client;
    }

    public function update(Client $client, array $validatedData)
    {
        if (isset($validatedData['user'])) {
            $this->userService->update($client->user, $validatedData['user']);
        }

        unset($validatedData['user']);
        $client->update($validatedData);

        LocationsService::updateOrCreate(
            ['code' => $client->user->code],
            [
                'street_address' => $validatedData['street_address'],
                'exstra_address' => $validatedData['exstra_address'] ?? null,
                'country' => $validatedData['country'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zip_code' => $validatedData['zip_code'],
            ]
        );

        return $client;
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return $client->user->delete();
    }

    // الوظائف القديمة
    public function setupClientProfile($validatedData, $user)
    {
        $client = Client::create([
            'user_id' => $user->id,
            'code' => $user->code,
        ]);

        $client->update([
            'code' => 'CLT' . sprintf('%03d', $client->id),
        ]);

        LocationsService::updateOrCreate(
            ['code' => $user->code],
            [
                'street_address' => $validatedData['street_address'],
                'exstra_address' => $validatedData['exstra_address'] ?? null,
                'country' => $validatedData['country'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zip_code' => $validatedData['zip_code'],
            ]
        );

        if (isset($validatedData['profile_photo'])) {
            $profilePhotoPath = ImageService::storeImage($validatedData['profile_photo'], 'profile_photos');
            $user->update(['profile_photo' => $profilePhotoPath]);
        }

        if (isset($validatedData['additional_images'])) {
            foreach ($validatedData['additional_images'] as $image) {
                $imagePath = ImageService::storeImage($image, 'client_images');
                Image::create([
                    'code' => $user->code,
                    'path' => $imagePath,
                ]);
            }
        }

        $user->update([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone' => $validatedData['phone'],
            'description' => $validatedData['description'] ?? null,
            'profile_setup' => true,
        ]);

        return $user;
    }

    public function profileData()
    {
        $user_id = Auth::id();
        $user = User::find($user_id);

        $user->load(['client', 'images', 'location']);

        return $user;
    }

    public function updateProfile($validatedData, $user)
    {
        LocationsService::updateOrCreate(
            ['code' => $user->code],
            [
                'street_address' => $validatedData['street_address'] ?? $user->location->street_address,
                'exstra_address' => $validatedData['exstra_address'] ?? $user->location->exstra_address,
                'country' => $validatedData['country'] ?? $user->location->country,
                'city' => $validatedData['city'] ?? $user->location->city,
                'state' => $validatedData['state'] ?? $user->location->state,
                'zip_code' => $validatedData['zip_code'] ?? $user->location->zip_code,
            ]
        );

        if (isset($validatedData['profile_photo']) && $validatedData['profile_photo']->isValid()) {
            $profilePhotoPath = ImageService::storeImage($validatedData['profile_photo'], 'profile_photos');
            $user->update(['profile_photo' => $profilePhotoPath]);
        }

        if (isset($validatedData['additional_images'])) {
            foreach ($validatedData['additional_images'] as $image) {
                if ($image->isValid()) {
                    $imagePath = ImageService::storeImage($image, 'client_images');
                    Image::create([
                        'code' => $user->code,
                        'path' => $imagePath,
                    ]);
                }
            }
        }

        if (request()->has('images_remove')) {
            ImageService::removeImages(request()->input('images_remove'));
        }

        $user->update([
            'first_name' => $validatedData['first_name'] ?? $user->first_name,
            'last_name' => $validatedData['last_name'] ?? $user->last_name,
            'phone' => $validatedData['phone'] ?? $user->phone,
            'description' => $validatedData['description'] ?? $user->description,
        ]);

        $user->load(['client', 'images', 'location']);

        return $user;
    }
}
