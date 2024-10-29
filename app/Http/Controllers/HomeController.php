<?php

namespace App\Http\Controllers;

use App\Http\Resources\VendorResource;
use App\Models\Faq;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Vendor;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function getData()
    {

        $services = Service::limit(8)->get();

        // $services->load('images');

        $vendors = Vendor::with(['user', 'reviews'])->limit(4)->get();


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
