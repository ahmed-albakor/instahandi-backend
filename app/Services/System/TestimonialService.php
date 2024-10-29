<?php

namespace App\Services\System;

use App\Models\Testimonial;
use App\Services\Helper\FilterService;
use App\Services\Helper\ImageService;

class TestimonialService
{
    public function getTestimonialById($id)
    {

        $testimonial =   Testimonial::find($id);

        if (!$testimonial) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Testimonial not found.',
                ], 404)
            );
        }
        return $testimonial;
    }

    public function getAllTestimonials()
    {
        $query = Testimonial::query();

        $searchFields = ['job', 'message', 'client_name'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = [];
        $inFields = [];

        $testimonials =  FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $testimonials;
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
