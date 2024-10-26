<?php

namespace App\Services\System;

use App\Models\Image;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Services\Helper\ImageService;
use Illuminate\Support\Facades\Auth;

class ServiceRequestService
{
    public function index($search = null, $limit = 20, $sortField = 'created_at', $sortOrder = 'desc')
    {

        $user = Auth::user();

        $query = ServiceRequest::query()->with(['location']);

        $query->when($search, function ($query, $search) {
            return $query->where('title', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });


        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('payment_type')) {
            $query->where('payment_type', request('payment_type'));
        }

        $query->when(request('price_min'), function ($query, $price_min) {
            return $query->where('price', '>=', $price_min);
        });

        $query->when(request('price_max'), function ($query, $price_max) {
            return $query->where('price', '<=', $price_max);
        });

        if ($user->role == 'client') {
            $query->where('client_id', $user->client->id);
        } else {
            if (request('client_id')) {
                $query->where('client_id', request('client_id'));
            }
        }


        $query->when(request('created_at_from'), function ($query, $created_at_from) {
            return $query->whereDate('created_at', '>=', $created_at_from);
        });

        $query->when(request('created_at_to'), function ($query, $created_at_to) {
            return $query->whereDate('created_at', '<=', $created_at_to);
        });

        $allowedSortFields = ['title', 'code', 'price', 'created_at', 'client_id'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortOrder);

        if ($user->role == 'vendor' || $user->role == 'admin') {
            $query->with(['client.user']);
        }



        return $query->paginate($limit);
    }


    public function show($id)
    {
        return ServiceRequest::find($id);
    }

    public function create($validatedData)
    {
        $serviceRequest = ServiceRequest::create([
            'code' => 'SRVREQ' . rand(100000, 999999),
            'client_id' => $validatedData['client_id'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'status' => 'pending',
            'payment_type' => $validatedData['payment_type'],
            'estimated_hours' => $validatedData['estimated_hours'],
            'price' => $validatedData['price'],
            'start_date' => $validatedData['start_date'],
            'completion_date' => $validatedData['completion_date'],
            'service_id' => $validatedData['service_id'],
        ]);

        $serviceRequest->update(['code' => 'SRVREQ' . sprintf('%03d', $serviceRequest->id)]);


        Location::create([
            'code' => $serviceRequest->code,
            'street_address' => $validatedData['street_address'],
            'exstra_address' => $validatedData['exstra_address'] ?? null,
            'country' => $validatedData['country'],
            'city' => $validatedData['city'],
            'state' => $validatedData['state'],
            'zip_code' => $validatedData['zip_code'],
        ]);

        if (isset($validatedData['images'])) {
            foreach ($validatedData['images'] as $image) {
                $path = ImageService::storeImage($image, 'service_requests', $serviceRequest->code);
                Image::create([
                    'code' => $serviceRequest->code,
                    'path' => $path,
                ]);
            }
        }

        return $serviceRequest;
    }

    public function update($serviceRequest, $validatedData)
    {
        $serviceRequest->update($validatedData);

        $locationData = array_filter([
            'street_address' => $validatedData['street_address'] ?? null,
            'exstra_address' => $validatedData['exstra_address'] ?? null,
            'country' => $validatedData['country'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state' => $validatedData['state'] ?? null,
            'zip_code' => $validatedData['zip_code'] ?? null,
        ]);

        if (!empty($locationData)) {
            Location::updateOrCreate(
                ['code' => $serviceRequest->code],
                $locationData
            );
        }

        if (isset($validatedData['images'])) {
            foreach ($validatedData['images'] as $image) {
                $path = ImageService::storeImage($image, 'service_requests', $serviceRequest->code);
                Image::create([
                    'code' => $serviceRequest->code,
                    'path' => $path,
                ]);
            }
        }

        return $serviceRequest;
    }

    public function delete($serviceRequest)
    {
        Image::where('code', $serviceRequest->code)->delete();

        Location::where('code', $serviceRequest->code)->delete();

        $serviceRequest->delete();
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

    public function checkPermission(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();

        if ($user->role == 'client') {
            if ($user->client->id != $serviceRequest->client_id) {
                return false;
            }
        }

        return true;
    }
}
