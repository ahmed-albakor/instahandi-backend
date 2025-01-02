<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'code' => $this->code,
            'service_request_id' => $this->service_request_id,
            'proposal_id' => $this->proposal_id,
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'vendor_id' => $this->vendor_id,
            'price' => $this->price,
            'payment_type' => $this->payment_type,
            'estimated_hours' => $this->estimated_hours,
            'works_hours' => $this->works_hours,
            'start_date' => $this->start_date,
            'completion_date' => $this->completion_date,
            'payment_at' => $this->payment_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_request' => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
            'proposal' => new ProposalResource($this->whenLoaded('proposal')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'work_location' => new LocationResource($this->whenLoaded('workLocation')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'review' => new VendorReviewResource($this->whenLoaded('review')),
        ];
    }
}
