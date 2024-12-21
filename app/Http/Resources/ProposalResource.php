<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProposalResource extends JsonResource
{
    public function toArray($request)
    {
        $hidden = true;
        if (Auth::check()) {
            $user = Auth::user();
            $hidden = $user->role == 'vendor' && $this->vendor_id != $user->vendor->id;
        }

        if (!$hidden)
            return [
                'id' => $this->id,
                'code' => $this->code,
                'service_request_id' => $this->service_request_id,
                'vendor_id' => $this->vendor_id,
                'message' => $this->message,
                'status' => $this->status,
                'price' => $this->price,
                'payment_type' => $this->payment_type,
                'estimated_hours' => $this->estimated_hours,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'service_request' => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
                'vendor' => new VendorResource($this->whenLoaded('vendor')),
            ];
    }
}
