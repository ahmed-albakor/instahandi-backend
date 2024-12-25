<?php

namespace App\Services\System;

use App\Models\Image;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorService as ModelsVendorService;
use App\Services\Helper\FilterService;
use App\Services\Helper\ImageService;
use Illuminate\Support\Facades\Auth;

class VendorService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $query = Vendor::query()->with(['user.location', 'services']);

        $searchFields = ['code', 'user.first_name', 'user.last_name', 'user.email', 'user.phone'];
        $numericFields = ['years_experience'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['user_id', 'account_type', 'has_crew'];
        $inFields = [];

        $testimonials = FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $testimonials;
    }

    public function getVendorById($id)
    {

        $vendor = Vendor::find($id);

        if (! $vendor) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Vendor not found.',
                ], 404)
            );
        }

        return $vendor;
    }


    public function create(array $validatedData, User $user = null)
    {
        if (!$user) {
            $validatedData['user']['role'] = 'vendor';
            $user = $this->userService->create($validatedData['user']);
        }

        $validatedData['user_id'] = $user->id;
        unset($validatedData['user']);
        $validatedData['code'] = rand(1111, 5555);

        $vendor = Vendor::create($validatedData);
        $vendor->update(['code' => 'VND' . sprintf('%03d', $vendor->id)]);

        $this->updateOrCreateLocation($validatedData, $user);

        return $vendor;
    }

    public function update(Vendor $vendor, array $validatedData)
    {
        if (isset($validatedData['user'])) {
            $this->userService->update($vendor->user, $validatedData['user']);
        }

        unset($validatedData['user']);
        $vendor->update($validatedData);

        $this->updateOrCreateLocation($validatedData, $vendor->user);

        return $vendor;
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return $vendor->user->delete();
    }

    public function setupVendorProfile(array $validatedData, User $user)
    {
        if ($user->profile_setup) {
            return $user;
        }

        $vendor = $this->createVendor($validatedData, $user);

        $this->updateOrCreateLocation($validatedData, $user);

        $this->handleOptionalFields($validatedData, $user);

        $user->update($this->extractUserFields($validatedData, $user));
        $user->update(['profile_setup' => true]);

        if (isset($validatedData['service_ids'])) {
            $this->updateVendorServices($validatedData['service_ids'], $vendor);
        }

        return $user;
    }

    public function profileData(): User
    {
        $user_id = Auth::id();
        $user = User::find($user_id);

        $user->load(['vendor.services', 'vendor.reviews.client.user', 'images', 'location']);

        return $user;
    }

    public function updateProfile(array $validatedData, User $user)
    {
        if (isset($user->vendor)) {
            $this->updateVendorProfile($validatedData, $user->vendor);

            $this->updateOrCreateLocation($validatedData, $user);

            $this->handleOptionalFields($validatedData, $user);

            $user->update($this->extractUserFields($validatedData, $user));

            if (isset($validatedData['service_ids'])) {
                $this->updateVendorServices($validatedData['service_ids'], $user->vendor);
            }

            if (request()->has('images_remove')) {
                ImageService::removeImages(request()->input('images_remove'));
            }
        }

        $user->load(['vendor.services', 'images', 'location']);

        return $user;
    }

    // Create or Update Vendor Profile
    private function createVendor(array $data, User $user): Vendor
    {
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'code' => $user->code,
            'account_type' => $data['account_type'],
            'years_experience' => $data['years_experience'],
            'longitude' => $data['longitude'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'has_crew' => $data['has_crew'] ?? false,
            'crew_members' => $data['crew_members'] ?? null,
        ]);

        $vendor->update(['code' => 'VND' . sprintf('%03d', $vendor->id)]);

        return $vendor;
    }

    private function updateVendorProfile(array $data, Vendor $vendor)
    {
        $vendor->update([
            'account_type' => $data['account_type'] ?? $vendor->account_type,
            'years_experience' => $data['years_experience'] ?? $vendor->years_experience,
            'longitude' => $data['longitude'] ?? $vendor->longitude,
            'latitude' => $data['latitude'] ?? $vendor->latitude,
            'has_crew' => $data['has_crew'] ?? $vendor->has_crew,
            'status' => $data['status'] ?? $vendor->has_crew,
            'crew_members' => $data['crew_members'] ?? $vendor->crew_members,
        ]);
    }

    // Create or Update Location
    private function updateOrCreateLocation(array $data, User $user)
    {
        LocationsService::updateOrCreate(
            ['code' => $user->code],
            [
                'street_address' => $data['street_address'] ?? $user->location->street_address ?? null,
                'exstra_address' => $data['exstra_address'] ?? $user->location->exstra_address ?? null,
                'country' => $data['country'] ?? $user->location->country ?? null,
                'city' => $data['city'] ?? $user->location->city ?? null,
                'state' => $data['state'] ?? $user->location->state ?? null,
                'zip_code' => $data['zip_code'] ?? $user->location->zip_code ?? null,
            ]
        );
    }

    // Handle Optional Fields
    private function handleOptionalFields(array $data, User $user)
    {
        $optionalFields = [
            'profile_photo' => fn($value) => $this->updateProfilePhoto($value, $user),
            'additional_images' => fn($value) => $this->storeAdditionalImages($value, $user),
        ];

        foreach ($optionalFields as $key => $action) {
            if (isset($data[$key])) {
                $action($data[$key]);
            }
        }
    }

    private function updateProfilePhoto($photo, User $user)
    {
        $path = ImageService::storeImage($photo, 'profile_photos');
        $user->update(['profile_photo' => $path]);
    }

    private function storeAdditionalImages(array $images, User $user)
    {
        foreach ($images as $image) {
            $path = ImageService::storeImage($image, 'vendor_images');
            Image::create([
                'code' => $user->code,
                'path' => $path,
            ]);
        }
    }

    // Extract User Fields for Update
    private function extractUserFields(array $data, User $user): array
    {
        return [
            'first_name' => $data['first_name'] ?? $user->first_name,
            'last_name' => $data['last_name'] ?? $user->last_name,
            'phone' => $data['phone'] ?? $user->phone,
            'description' => $data['description'] ?? $user->description,
        ];
    }

    // Update Vendor Services
    private function updateVendorServices(array $serviceIds, Vendor $vendor)
    {
        $existingServices = ModelsVendorService::where('vendor_id', $vendor->id)
            ->whereIn('service_id', $serviceIds)
            ->pluck('service_id')
            ->toArray();

        $newServices = array_diff($serviceIds, $existingServices);

        foreach ($newServices as $serviceId) {
            ModelsVendorService::create([
                'vendor_id' => $vendor->id,
                'service_id' => $serviceId,
            ]);
        }
    }
}
