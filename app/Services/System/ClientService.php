<?php

namespace App\Services\System;

use App\Models\Client;
use App\Models\Image;
use App\Models\Location;
use App\Models\User;
use App\Services\Helper\ImageService;
use Illuminate\Support\Facades\Auth;

class ClientService
{
    public function setupClientProfile($validatedData, $user)
    {
        $client = Client::create([
            'user_id' => $user->id,
            'code' => $user->code,
        ]);

        $client->update([
            'code' => 'CLT' . sprintf('%03d', $client->id),
        ]);

        Location::updateOrCreate(
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
}
