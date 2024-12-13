<?php

namespace App\Services\System;

use App\Models\Location;
use Illuminate\Support\Facades\Http;

class LocationsService
{
    /**
     * Fetch latitude and longitude from address.
     */
    private static function getCoordinates($address)
    {
        // Replace with your Google Maps API Key
        $apiKey = config('services.google_maps.api_key');

        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $address,
            'key' => $apiKey,
        ]);

        if ($response->successful() && isset($response['results'][0]['geometry']['location'])) {
            $location = $response['results'][0]['geometry']['location'];
            return [
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
            ];
        }

        throw new \Exception('Unable to fetch coordinates for the given address.');
    }

    /**
     * Create a new location record.
     */
    public static function create(array $data)
    {
        // Prepare the full address
        $address = "{$data['street_address']}, {$data['city']}, {$data['state']}, {$data['country']}, {$data['zip_code']}";

        // Fetch coordinates
        $coordinates = LocationsService::getCoordinates($address);

        // Merge coordinates into data and create record
        $data = array_merge($data, $coordinates);

        return Location::create($data);
    }

    /**
     * Create or update a location record.
     */
    public static function updateOrCreate(array $data)
    {
        // Extract the unique key from data (e.g., `code`)
        $uniqueKey = ['code' => $data['code']];

        // Prepare the full address
        $address = "{$data['street_address']}, {$data['city']}, {$data['state']}, {$data['country']}, {$data['zip_code']}";

        // Fetch coordinates
        $coordinates = LocationsService::getCoordinates($address);

        // Merge coordinates into data
        $data = array_merge($data, $coordinates);

        // Use updateOrCreate to insert or update based on the unique key
        return Location::updateOrCreate($uniqueKey, $data);
    }
}
