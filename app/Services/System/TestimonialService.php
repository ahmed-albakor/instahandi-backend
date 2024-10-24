<?php

namespace App\Services\System;

use App\Models\Testimonial;
use App\Services\Helper\ImageService;

class TestimonialService
{
    public function getTestimonialById($id)
    {
        return Testimonial::find($id);
    }

    public function getAllTestimonials($search, $limit)
    {
        return Testimonial::query()
            ->when($search, function ($query, $search) {
                return $query->where('message', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('job', 'like', "%{$search}%");
            })
            ->paginate($limit ?? 10);
    }

    public function createTestimonial($validatedData, $profilePhoto)
    {
        $testimonial = Testimonial::create([
            'message' => $validatedData['message'],
            'rating' => $validatedData['rating'],
            'client_name' => $validatedData['client_name'],
            'job' => $validatedData['job'],
            'profile_photo' => ' ',
            'admin_id' => $validatedData['admin_id'],
        ]);

        $path = ImageService::storeImage($profilePhoto, 'testimonials', $testimonial->id);

        $testimonial->update(['profile_photo' => $path]);

        return $testimonial;
    }

    public function updateTestimonial($testimonial, $validatedData, $profilePhoto = null)
    {
        if ($profilePhoto) {
            $path = ImageService::storeImage($profilePhoto, 'testimonials', $testimonial->id);
            $validatedData['profile_photo'] = $path;
        }

        $testimonial->update($validatedData);

        return $testimonial;
    }

    public function deleteTestimonial($testimonial)
    {
        $testimonial->delete();
    }
}
