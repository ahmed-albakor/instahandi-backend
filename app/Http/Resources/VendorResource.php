<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'code' => $this->code,
            'account_type' => $this->account_type,
            'status' => $this->status,
            'years_experience' => $this->years_experience,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'has_crew' => $this->has_crew,
            'has_business_insurance' => $this->has_business_insurance,
            'crew_members' => $this->crew_members,
            'average_rating' => $this->getAverageRatingAttribute(),
            'user' => new UserResource($this->whenLoaded('user')),
            'services' =>  ServiceResource::collection($this->whenLoaded('services')),
            'vendorServices' => VendorServiceResource::collection($this->whenLoaded('vendorServices')),
            'reviews' => VendorReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
