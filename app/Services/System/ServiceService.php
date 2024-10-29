<?php

namespace App\Services\System;

use App\Models\Service;
use App\Models\Image;
use App\Services\Helper\FilterService;
use App\Services\Helper\ImageService;

class ServiceService
{
    public function getServiceById($id)
    {
        $service = Service::find($id);


        abort(
            response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404)
        );

        return $service;
    }

    public function getAllServices()
    {
        $query = Service::query();

        $searchFields = ['code', 'name', 'description'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = [];
        $inFields = [];

        $services =  FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $services;
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
