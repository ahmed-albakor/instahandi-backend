<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Service;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function show($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $service->main_image = asset("storage/" . $service->main_image);

        $service->images = $service->getImages();

        return response()->json([
            'success' => true,
            'data' => $service,
        ], 200);
    }


    public function index(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $search = $request->input('search');

        $services = Service::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($service) {
                $service->main_image = asset("storage/" . $service->main_image);
                return $service;
            });

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }


    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:8096',
            'description' => 'nullable|string',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        $mainImagePath = ImageService::storeImage($request->file('main_image'), 'services');

        $service = Service::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? '',
            'main_image' => $mainImagePath,
            'code' => rand(100000, 999999),
        ]);

        $service->update(
            [
                'code' => 'SRVC' . sprintf('%03d', $service->id),
            ]
        );

        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $image) {
                $imagePath = ImageService::storeImage($image, 'services');
                Image::create([
                    'code' => $service->code,
                    'path' => $imagePath,
                ]);
            }
        }

        $service->main_image = asset("storage/" . $service->main_image);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => $service,
        ]);
    }


    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:8096',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('main_image')) {
            $mainImagePath = ImageService::updateImage($request->file('main_image'), 'services', $service->main_image);
            $service->update(['main_image' => $mainImagePath]);
        }

        $service->update([
            'name' => $validatedData['name'] ?? $service->name,
            'description' => $validatedData['description'] ?? $service->description,
        ]);

        $service->main_image = asset("storage/" . $service->main_image);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => $service,
        ]);
    }


    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service soft deleted successfully.',
        ]);
    }

    public function restore($id)
    {
        $service = Service::withTrashed()->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found in archive.',
            ], 404);
        }

        $service->restore();

        return response()->json([
            'success' => true,
            'message' => 'Service restored successfully.',
            'data' => $service,
        ]);
    }

    public function uploadAdditionalImages(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'additional_images' => 'array|required',
            'additional_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $newImages = collect($request->file('additional_images'))->map(function ($image) use ($service) {
            $imagePath = ImageService::storeImage($image, 'services');
            $imageModel = Image::create([
                'code' => $service->code,
                'path' => $imagePath,
            ]);
            $imageModel['path'] = asset('storage/' . $imageModel['path']);
            return $imageModel;
        });

        return response()->json([
            'success' => true,
            'message' => 'Additional images uploaded successfully.',
            'new_images' => $newImages,
        ]);
    }

    public function deleteAdditionalImages(Request $request, $id)
    {

        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:images,id',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();


        foreach ($validatedData['image_ids'] as $imageId) {

            $image = Image::find($imageId);

            if (!$image || $image->code != $service->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found.',
                ], 404);
            }
            // Storage::delete('public/' . $image->path);
            $image->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Selected images deleted successfully.',
        ]);
    }
}
