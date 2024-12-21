<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proposal\CreateProposalRequest;
use App\Http\Requests\Proposal\PlaceBidRequest;
use App\Http\Requests\Proposal\UpdateProposalRequest;
use App\Http\Requests\ProposalRequest;
use App\Http\Resources\ProposalResource;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Permissions\ProposalPermission;
use App\Services\Helper\ResponseService;
use App\Services\System\ProposalService;

class ProposalController extends Controller
{
    protected $proposalService;

    public function __construct(ProposalService $proposalService)
    {
        $this->proposalService = $proposalService;
    }

    public function index()
    {
        $proposals = $this->proposalService->index();

        return response()->json(
            [
                'success' => true,
                'data' => ProposalResource::collection($proposals->items()),
                'meta' => ResponseService::meta($proposals)
            ]
        );
    }


    public function show($id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        $proposal->load(['serviceRequest', 'vendor.user.location']);


        return response()->json([
            'success' => true,
            'data' => new ProposalResource($proposal),
        ]);
    }

    public function create(CreateProposalRequest $request)
    {
        $service_requests = ServiceRequest::find($request->service_request_id);

        if ($service_requests->status != 'pending') {
            return response()->json([
                'success' => false,
                'errors' => "You cannot add a proposal to a service request while it is {$service_requests->status}.",
            ], 422);
        }

        $proposal = $this->proposalService->createProposal($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Proposal created successfully.',
            'data' => $proposal,
        ]);
    }


    public function update($id, UpdateProposalRequest $request)
    {
        $proposal = $this->proposalService->getProposalById($id);

        ProposalPermission::update($proposal);

        if (!$this->proposalService->canModifyProposal($proposal)) {
            return response()->json([
                'success' => false,
                'errors' => "You cannot modify a proposal when the service request is in {$proposal->serviceRequest->status} status.",
            ], 422);
        }

        $proposal = $this->proposalService->updateProposal($proposal, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Proposal updated successfully.',
            'data' => $proposal,
        ]);
    }

    public function destroy($id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        ProposalPermission::destory($proposal);


        if ($proposal->order) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete the proposal because it has been approved and you have been hired for this service.',
            ], 404);
        }

        $this->proposalService->deleteProposal($proposal);

        return response()->json([
            'success' => true,
            'message' => 'Proposal deleted successfully.',
        ]);
    }


    public function rejectProposal($id)
    {

        $proposal = $this->proposalService->getProposalById($id);

        ProposalPermission::reject($proposal);

        $proposal = $this->proposalService->updateProposal($proposal, [
            'status' => 'reject',
        ]);

        // TODO : Send Notification to Vendor for tell him


        return response()->json([
            'success' => true,
            'message' => 'Proposal reject successfully.',
            'data' => $proposal,
        ]);
    }
}
