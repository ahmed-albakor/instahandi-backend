<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ServiceRequestResource extends JsonResource
{
    public function toArray($request)
    {
        $hidden = true;
        if (Auth::check()) {
            $user = Auth::user();
            $hidden = $user->role == 'vendor';
        }
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'payment_type' => $this->payment_type,
            'estimated_hours' => $this->estimated_hours,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'completion_date' => $this->completion_date,
            'service_id' => $this->service_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'proposals' => $this->when(! $hidden, ProposalResource::collection($this->whenLoaded('proposals'))),
            'proposals_count' =>  $this->proposals->count(),
            'location' => new LocationResource($this->whenLoaded('location')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
