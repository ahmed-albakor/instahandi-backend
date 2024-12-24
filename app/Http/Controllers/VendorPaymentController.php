<?php

namespace App\Http\Controllers;

use App\Http\Resources\VendorPaymentResource;
use App\Permissions\VendorPaymentPermission;
use App\Services\Helper\ResponseService;
use App\Services\Helper\StripeService;
use App\Services\System\VendorPaymentService;

class VendorPaymentController extends Controller
{
    protected $stripeService;
    protected $vendorPaymentService;

    public function __construct(StripeService $stripeService, VendorPaymentService $vendorPaymentService)
    {
        $this->stripeService = $stripeService;
        $this->vendorPaymentService = $vendorPaymentService;
    }

    public function index()
    {
        $payments = $this->vendorPaymentService->getAllPayments();

        return response()->json([
            'success' => true,
            'data' => VendorPaymentResource::collection($payments->items()),
            'meta' => ResponseService::meta($payments)

        ]);
    }

    public function show($id)
    {
        $payment = $this->vendorPaymentService->getPaymentById($id);
        VendorPaymentPermission::view($payment);

        return response()->json([
            'success' => true,
            'data' => new VendorPaymentResource($payment),
        ]);
    }
}
