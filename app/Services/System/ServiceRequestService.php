<?php

namespace App\Services\System;

use App\Models\Image;
use App\Models\Location;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Services\Helper\FilterService;
use App\Services\Helper\ImageService;
use Illuminate\Support\Facades\Auth;

class ServiceRequestService
{
    public function index()
    {
        $query = ServiceRequest::query()->with(['location', 'images', 'client.user']);


        $searchFields = ['code', 'title', 'description'];
        $numericFields = ['price'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['payment_type', 'status', 'client_id'];
        $inFields = [];

        $serviceRequests =  FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $serviceRequests;
    }


    public function show($id)
    {
        $serviceRequest =  ServiceRequest::find($id);

        if (!$serviceRequest) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Service request not found.',
                ], 404)
            );
        }
        return $serviceRequest;
    }

    public function create($validatedData)
    {
        $user = Auth::user();
        if ($user->role == 'client') {
            $validatedData['client_id'] = $user->client->id;
        }
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


        LocationsService::create([
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
            LocationsService::updateOrCreate(
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


    public function acceptServiceRequest($serviceRequest, orderService $orderService)
    {

        $user = Auth::user();

        $order =  $orderService->createOrder([
            'service_request_id' => $serviceRequest->id,
            'status' => 'pending',
            'title' => $serviceRequest->description,
            'description' => $serviceRequest->description,
            'vendor_id' => $user->vendor->id,
            'price' => $serviceRequest->price,
            'payment_type' => $serviceRequest->payment_type,
        ]);

        $serviceRequest->update(
            [
                'status' => 'accepted',
            ]
        );

        // TODO : Send Notification to Cleint for tell him


        return $order;
    }


    public function hireVendor($serviceRequest, orderService $orderService, $proposal)
    {
        $order =  $orderService->createOrder([
            'service_request_id' => $serviceRequest->id,
            'proposal_id' => $proposal->id,
            'status' => 'pending',
            'title' => $serviceRequest->description,
            'description' => $proposal->message,
            'vendor_id' => $proposal->vendor_id,
            'price' => $proposal->price,
            'payment_type' => $proposal->payment_type,
        ]);

        $serviceRequest->update(
            [
                'status' => 'accepted',
            ]
        );
        $proposal->update(
            [
                'status' => 'accepted',
            ]
        );

        // TODO : Send Notification to Vendor for tell him


        return $order;
    }
}
