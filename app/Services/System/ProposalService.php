<?php

namespace App\Services\System;

use App\Models\Proposal;
use App\Services\Helper\FilterService;
use Illuminate\Support\Facades\Auth;

class ProposalService
{
    public function index()
    {
        $query = Proposal::query()->with(['serviceRequest', 'vendor.user.location']);


        $user = Auth::user();
        if ($user->role == 'vendor') {
            $query->where('vendor_id', $user->vendor->id);
        }

        $searchFields = ['code', 'message'];
        $numericFields = ['price'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['payment_type', 'service_request_id', 'vendor_id'];
        $inFields = [];

        $proposal =  FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $proposal;
    }


    public function getProposalById($id)
    {
        $proposal = Proposal::find($id);

        if (!$proposal) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Proposal not found.',
                ], 404)
            );
        }

        return $proposal;
    }

    public function createProposal(array $data)
    {
        $user = Auth::user();

        if ($user->role == 'vendor') {
            $data['vendor_id'] = $user->vendor->id;
        }
        $data['code'] = 'PRP' . rand(100000, 999999);
        $proposal = Proposal::create($data);

        $proposal->update(['code' => 'PRP' . sprintf('%03d', $proposal->id)]);

        return $proposal;
    }

    public function updateProposal(Proposal $proposal, array $data)
    {
        $proposal->update($data);
        return $proposal;
    }

    public function deleteProposal(Proposal $proposal)
    {
        $proposal->delete();
    }

    public function restoreProposal(Proposal $proposal)
    {
        $proposal->restore();
    }

    public function canModifyProposal(Proposal $proposal)
    {
        return $proposal->serviceRequest->status === 'pending';
    }
}
