<?php

namespace App\Http\Controllers;

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
            $vendor->average_rating = $vendor->reviews()->avg('rating') ?? 0;
            $vendor->main_image = asset("storage/" . $vendor->main_image);
            return $vendor;
        });

        $testimonials = Testimonial::get()->map(function ($testimonial) {
            $testimonial->admin_id = asset("storage/" . $testimonial->admin_id);
            return $testimonial;
        });

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'services' => $services,
                    'vendors' => $vendors,
                    'testimonials' => $testimonials,
                ],
            ]
        );
    }
}
