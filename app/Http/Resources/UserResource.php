<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $hidden = true;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role == 'admin') {
                $hidden = false;
            } elseif ($user->role == $this->role) {
                $hidden = $user->id != $this->id;
            }
        }
        return [
            'id' => $this->id,
            'code' => $this->code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->when(!$hidden, $this->email),
            'phone' => $this->when(!$hidden, $this->phone),
            'approve' => $this->when(!$hidden, $this->approve),
            'profile_setup' => $this->when(!$hidden, $this->profile_setup),
            'verify_code' => $this->when(!$hidden, $this->verify_code),
            'code_expiry_date' => $this->when(!$hidden, $this->code_expiry_date),
            'email_verified_at' => $this->when(!$hidden, $this->email_verified_at),
            'role' => $this->role,
            'description' => $this->description,
            'profile_photo' => $this->profile_photo,
            'location' => new LocationResource($this->whenLoaded('location')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
