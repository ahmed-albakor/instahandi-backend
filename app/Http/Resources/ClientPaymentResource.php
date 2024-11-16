<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'client_id' => $this->client_id,
            'service_request_id' => $this->service_request_id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'description' => $this->description,
            'payment_data' => $this->payment_data,
            'client' => new ClientResource($this->whenLoaded('client')),
            'service_request' => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
