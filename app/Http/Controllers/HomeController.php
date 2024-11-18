<?php

namespace App\Http\Controllers;

use App\Http\Resources\VendorResource;
use App\Models\Faq;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Vendor;

class HomeController extends Controller
{
    public function getData()
    {

        $services = Service::limit(8)->get();

        $vendors = Vendor::with(['user.location', 'vendorServices.service', 'reviews', 'services'])
            ->whereHas('user', function ($query) {
                $query->where('approve', 1)
                    ->where('profile_setup', 1);
            })
            ->limit(6)
            ->get();


        $testimonials = Testimonial::get();

        $faqs = Faq::get();

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'services' => $services,
                    'vendors' => VendorResource::collection($vendors),
                    'testimonials' => $testimonials,
                    'faqs' => $faqs,
                ],
            ]
        );
    }
}
