<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'vendor_id' => $this->vendor_id,
            'client_id' => $this->client_id,
            'rating' => $this->rating,
            'review' => $this->review,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'client' => new ClientResource($this->whenLoaded('client')),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
