<?php

namespace App\Services\System;

use App\Models\Service;
use App\Models\Image;
use App\Services\Helper\ImageService;

class ServiceService
{
    public function getServiceById($id)
    {
        return Service::find($id);
    }

    public function getAllServices($search, $limit)
    {
        return Service::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->paginate($limit ?? 20);
    }

    public function createService($validatedData)
    {
        $service = Service::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? '',
            'main_image' => ' ',
            'code' => rand(100000, 999999),
        ]);

        $code = 'SRVC' . sprintf('%03d', $service->id);

        $mainImagePath = ImageService::storeImage($validatedData['main_image'], 'services', $code);
        $service->update([
            'code' => $code,
            'main_image' => $mainImagePath,
        ]);

        if (isset($validatedData['additional_images'])) {
            $this->storeAdditionalImages($validatedData['additional_images'], $service->code);
        }

        return $service;
    }

    public function updateService($service, $validatedData)
    {
        if (isset($validatedData['main_image'])) {
            $mainImagePath = ImageService::storeImage($validatedData['main_image'], 'services', $service->code);
            $service->update(['main_image' => $mainImagePath]);
        }

        $service->update([
            'name' => $validatedData['name'] ?? $service->name,
            'description' => $validatedData['description'] ?? $service->description,
        ]);

        if (isset($validatedData['additional_images'])) {
            $this->storeAdditionalImages($validatedData['additional_images'], $service->code);
        }

        return $service;
    }

    public function deleteService($service)
    {
        Image::where('code', $service->code)->delete();
        $service->delete();
    }

    public function restoreService($service)
    {
        $service->restore();
    }

    public function storeAdditionalImages($images, $code)
    {
        foreach ($images as $image) {
            $imagePath = ImageService::storeImage($image, 'services_additional', $code);
            Image::create([
                'code' => $code,
                'path' => $imagePath,
            ]);
        }
    }

    public function deleteAdditionalImages($imageIds, $serviceCode)
    {
        foreach ($imageIds as $imageId) {
            $image = Image::where('id', $imageId)->where('code', $serviceCode)->first();
            if ($image) {
                $image->delete();
            }
        }
    }
}
