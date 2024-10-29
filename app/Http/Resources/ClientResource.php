<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'service_requests' => ServiceRequestResource::collection($this->whenLoaded('serviceRequests')),
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
            // 'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            // 'vendor_reviews' => VendorReviewResource::collection($this->whenLoaded('vendorReviews')),
        ];
    }
}
