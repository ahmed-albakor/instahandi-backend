<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Services\System\ProposalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasHiddenFields;

class ProposalController extends Controller
{
    protected $proposalService;

    public function __construct(ProposalService $proposalService)
    {
        $this->proposalService = $proposalService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 20);

        $proposals = $this->proposalService->index($search, $limit);

        $proposals->getCollection()->transform(function ($proposal) {

            $proposal->vendor->user->makeHidden(HasHiddenFields::getUserHiddenFields());

            $proposal->vendor->user->profile_photo = $proposal->vendor->user->getProfilePhoto();

            return $proposal;
        });

        return response()->json([
            'success' => true,
            'data' => $proposals->items(),
            'meta' => [
                'current_page' => $proposals->currentPage(),
                'last_page' => $proposals->lastPage(),
                'per_page' => $proposals->perPage(),
                'total' => $proposals->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        if (!$proposal) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found.',
            ], 404);
        }

        $proposal->load(['serviceRequest', 'vendor.user.location']);

        $proposal->vendor->user->makeHidden(HasHiddenFields::getUserHiddenFields());
        $proposal->vendor->user->profile_photo = $proposal->vendor->user->getProfilePhoto();



        return response()->json([
            'success' => true,
            'data' => $proposal,
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id,deleted_at,NULL',
            'vendor_id' => $user->role == 'vendor' ? '' : 'required|exists:vendors,id,deleted_at,NULL',
            'message' => 'required|string',
            'price' => 'required|numeric|min:0',
            'payment_type' => 'required|in:flat_rate,hourly_rate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $service_requests = ServiceRequest::find($request->service_request_id);

        if ($service_requests->status != 'pending') {
            return response()->json([
                'success' => false,
                'errors' => "You cannot add a proposal to a service request while it is {$service_requests->status}.",
            ], 422);
        }

        $proposal = $this->proposalService->createProposal($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Proposal created successfully.',
            'data' => $proposal,
        ]);
    }

    public function update(Request $request, $id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        if (!$proposal) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found.',
            ], 404);
        }

        $permission = $this->proposalService->checkPermission($proposal);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
            ], 403);
        }


        if (!$this->proposalService->canModifyProposal($proposal)) {
            return response()->json([
                'success' => false,
                'errors' => "You cannot modify a proposal when the service request is in {$proposal->serviceRequest->status} status.",
            ], 422);
        }


        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|in:flat_rate,hourly_rate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $proposal = $this->proposalService->updateProposal($proposal, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Proposal updated successfully.',
            'data' => $proposal,
        ]);
    }

    public function destroy($id)
    {
        $proposal = $this->proposalService->getProposalById($id);


        if (!$proposal) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found.',
            ], 404);
        }


        $permission = $this->proposalService->checkPermission($proposal);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions error.',
            ], 403);
        }

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

    public function restore($id)
    {
        $proposal = Proposal::withTrashed()->find($id);

        if (!$proposal) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found in archive.',
            ], 404);
        }

        $this->proposalService->restoreProposal($proposal);

        return response()->json([
            'success' => true,
            'message' => 'Proposal restored successfully.',
            'data' => $proposal,
        ]);
    }
}
