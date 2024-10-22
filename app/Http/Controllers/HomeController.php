<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Vendor;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function getData()
    {

        $services = Service::limit(8)->get()->map(function ($service) {
            $service->main_image = asset("storage/" . $service->main_image);
            return $service;
        });

        $vendors = Vendor::with(['user', 'reviews'])->limit(4)->get()->map(function ($vendor) {
            $vendor->makeHidden(['user_id', 'longitude', 'latitude', 'created_at', 'updated_at']);
            $vendor->user->makeHidden(['email', 'phone', 'approve', 'profile_setup', 'verify_code', 'code_expiry_date', 'email_verified_at', 'created_at', 'updated_at']);
            $vendor->average_rating = $vendor->reviews()->avg('rating') ?? 0;
            if ($vendor->user->profile_photo) $vendor->user->profile_photo = asset("storage/" . $vendor->user->profile_photo);

            return $vendor;
        });


        $testimonials = Testimonial::get()->map(function ($testimonial) {
            $testimonial->profile_photo = asset("storage/" . $testimonial->profile_photo);
            return $testimonial;
        });

        $faqs = Faq::get();

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'services' => $services,
                    'vendors' => $vendors,
                    'testimonials' => $testimonials,
                    'faqs' => $faqs,
                ],
            ]
        );
    }
}
