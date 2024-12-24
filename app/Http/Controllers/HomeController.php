<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Http\Resources\VendorResource;
use App\Models\Faq;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Vendor;
use App\Services\System\ServiceService;
use App\Services\System\VendorService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function getData()
    {

        $services = Service::limit(8)->get();

        $vendors = Vendor::with(['user.location', 'services'])
            ->whereHas('user', function ($query) {
                $query->where('approve', 1)
                    ->where('profile_setup', 1);
            })
            ->limit(6)
            ->get();


        $testimonials = Testimonial::get();

        $faqs = Faq::get();

        $Statistics = [];



        if (Auth::check()) {

            $user = Auth::user();

            if ($user->role == 'vendor') {
                $vendor = $user->vendor;
                $Statistics = [
                    'my_wallet' => $vendor->vendorPayments()->sum('amount'),
                    'all_requests' => $vendor->serviceRequests()->count(),
                    'cancelled_requests' => $vendor->serviceRequests()->where('status', 'canceled')->count(),
                    'completed_jobs' => $vendor->orders()->where('status', 'completed')->count(),
                ];
            }
        }



        return response()->json(
            [
                'success' => true,
                'data' => [
                    'services' => $services,
                    'vendors' => VendorResource::collection($vendors),
                    'testimonials' => $testimonials,
                    'faqs' => $faqs,
                    'statistics' =>  $Statistics,
                ],
            ]
        );
    }

    public function search()
    {
        $vendorService = new VendorService();
        $serviceService = new ServiceService();

        $vendors = $vendorService->index();
        $services = $serviceService->getAllServices();

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'vendors' => VendorResource::collection($vendors),
                    'services' => ServiceResource::collection($services),
                ],
            ]
        );
    }
}
