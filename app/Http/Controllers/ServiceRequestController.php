<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proposal\PlaceBidRequest;
use App\Http\Requests\ServiceRequest\CreateRequest;
use App\Http\Requests\ServiceRequest\HireVendorRequest;
use App\Http\Requests\ServiceRequest\UpdateRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Image;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Permissions\ServiceRequestPermission;
use App\Services\Helper\ResponseService;
use App\Services\Helper\StripeService;
use App\Services\System\OrderService;
use App\Services\System\ProposalService;
use App\Services\System\ServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
    protected $serviceRequestService;
    protected $proposalService;

    public function __construct(ServiceRequestService $serviceRequestService, ProposalService $proposalService)
    {
        $this->serviceRequestService = $serviceRequestService;
        $this->proposalService = $proposalService;
    }

    public function index()
    {
        $serviceRequests = $this->serviceRequestService->index();

        return response()->json([
            'success' => true,
            'data' => ServiceRequestResource::collection($serviceRequests->items()),
            'meta' => ResponseService::meta($serviceRequests),
        ]);
    }


    public function show($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        $serviceRequest->load(['location', 'client.user.location', 'proposals.vendor.user', 'proposals.vendor.services', 'images', 'service']);


        return response()->json([
            'success' => true,
            'data' => new ServiceRequestResource($serviceRequest),
        ]);
    }

    public function store(CreateRequest $request)
    {
        $validatedData = $request->validated();

        $serviceRequest = $this->serviceRequestService->create($validatedData);

        $serviceRequest->load(['location', 'images', 'service']);

        return response()->json([
            'success' => true,
            'message' => 'Service request created successfully',
            'data' => $serviceRequest,
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::update($serviceRequest);

        $validatedData = $request->validated();

        if (!empty($validatedData['status']) && $validatedData['status'] === 'canceled') {
            $restrictedStatuses = ['accepted', 'completed', 'rejected', 'canceled'];
            if (!in_array($serviceRequest->status, $restrictedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => "You cannot cancel the service request while it is in the '{$serviceRequest->status}' status.",
                ], 403);
            }
        }


        $serviceRequest = $this->serviceRequestService->update($serviceRequest, $validatedData);

        $serviceRequest->load(['location', 'images', 'service']);


        return response()->json([
            'success' => true,
            'message' => 'Service request updated successfully',
            'data' => $serviceRequest,
        ]);
    }

    public function destroy($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::destory($serviceRequest);


        if (!in_array($serviceRequest->status, ['canceled', 'rejected', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => "You cannot delete the service request in {$serviceRequest->status} status.",
            ], 403);
        }

        $this->serviceRequestService->delete($serviceRequest);

        return response()->json([
            'success' => true,
            'message' => 'Service request deleted successfully',
        ]);
    }


    public function uploadAdditionalImages(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::update($serviceRequest);


        $validator = Validator::make($request->all(), [
            'additional_images' => 'array|required',
            'additional_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:8096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->serviceRequestService->storeAdditionalImages($request->file('additional_images'), $serviceRequest->code);

        $new_images = Image::where('code', $serviceRequest->code)->get();

        return response()->json([
            'success' => true,
            'message' => 'Additional images uploaded successfully.',
            'data' => $new_images,
        ]);
    }

    public function deleteAdditionalImages(Request $request, $id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        ServiceRequestPermission::update($serviceRequest);

        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->serviceRequestService->deleteAdditionalImages($validator->validated()['image_ids'], $serviceRequest->code);

        return response()->json([
            'success' => true,
            'message' => 'Selected images deleted successfully.',
        ]);
    }


    ########## vendor ########## 

    public function acceptServiceRequset($id, OrderService $orderService)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if ($serviceRequest->status != 'pending') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You cannot accept the service request if it not in status pending',
                ]
            );
        }

        $order = $this->serviceRequestService->acceptServiceRequest($serviceRequest, $orderService);

        return response()->json([
            'success' => true,
            'message' => 'Jop Created Successfully',
            'data' => $order,
        ], 201);
    }

    public function hireVendor($id, OrderService $orderService, HireVendorRequest $request)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        // get payments for this service request and check if have payments pending , and check it stataus in stripe
        $payments = $serviceRequest->payments()->where('status', 'pending')->get();

        foreach ($payments as $payment) {
            $payment_data = json_decode(json: json_encode($payment->payment_data));

            $stripeService = new StripeService();

            $paymentIntent = $stripeService->retrievePaymentIntent($payment_data->id);

            if ($paymentIntent->status === 'succeeded') {
                $payment->update([
                    'status' => 'confirm',
                    'payment_data' => json_encode($paymentIntent),
                ]);

                $serviceRequest->can_job = 1;
                $serviceRequest->save();
            }
        }

        // if can_job is false then the client can't hire a vendor for this service request, first he should pay first payment 
        if (!$serviceRequest->can_job) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You cannot hire a vendor for this service request, first you should pay the first payment.',
                ],
                400
            );
        }

        $proposal = Proposal::find($request->proposal_id);

        ServiceRequestPermission::hireVendor($serviceRequest, $proposal);

        $acceptedProposal = $serviceRequest->proposals()
            ->where('status', 'accepted')
            ->exists();


        if ($acceptedProposal || $serviceRequest->status == 'accepted') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You have already hired a vendor or the request is still pending.',
                ],
                400
            );
        }

        $order = $this->serviceRequestService->hireVendor($serviceRequest, $orderService, $proposal);

        return response()->json([
            'success' => true,
            'message' => 'Vendor hired successfully, Service created.',
            'data' => $order,
        ], 201);
    }

    public function rejectVendor($id, HireVendorRequest $request)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        $proposal = Proposal::find($request->proposal_id);

        ServiceRequestPermission::hireVendor($serviceRequest, $proposal);

        $result = $this->serviceRequestService->rejectVendor($proposal);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'The proposal was successfully rejected.' : 'The proposal failed to be rejected.',
        ], 201);
    }




    public function placeBid($id)
    {
        $serviceRequest = $this->serviceRequestService->show($id);

        if ($serviceRequest->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => "You cannot place Bid a service request while it is {$serviceRequest->status}.",
            ], 422);
        }

        // $requests_data = $request->validated();


        $proposal_data = [
            'service_request_id' => $serviceRequest->id,
            // 'vendor_id' => $requests_data->vendor_id,
            'message' => "I am pleased to accept your offer as presented, including the description and the price you have set.",
            'price' => $serviceRequest->price,
            'estimated_hours' => $serviceRequest->estimated_hours,
            'payment_type' => $serviceRequest->payment_type,
        ];

        $proposal = $this->proposalService->createProposal($proposal_data);

        $proposal->load(['vendor.user', 'vendor.services',]);



        return response()->json([
            'success' => true,
            'message' => 'Place Bid successfully.',
            'data' => $proposal,
        ]);
    }
}
