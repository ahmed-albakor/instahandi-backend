<?php

namespace App\Services\System;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;

class ProposalService
{
    public function index($search = null, $limit = 20, $sortField = 'created_at', $sortOrder = 'desc')
    {
        $user = Auth::user();

        $query = Proposal::query()->with(['serviceRequest', 'vendor.user.location']);

        $query->when($search, function ($query, $search) {
            return $query->where('code', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%");
        });

        if (request('payment_type')) {
            $query->where('payment_type', request('payment_type'));
        }

        $query->when(request('price_min'), function ($query, $price_min) {
            return $query->where('price', '>=', $price_min);
        });

        $query->when(request('price_max'), function ($query, $price_max) {
            return $query->where('price', '<=', $price_max);
        });

        if (request('service_request_id')) {
            $query->where('service_request_id', request('service_request_id'));
        }

        if ($user->role == 'vendor') {
            $query->where('vendor_id', $user->vendor->id);
        } elseif ($user->role == 'admin' && request('vendor_id')) {
            $query->where('vendor_id', request('vendor_id'));
        }

        $query->when(request('created_at_from'), function ($query, $created_at_from) {
            return $query->whereDate('created_at', '>=', $created_at_from);
        });

        $query->when(request('created_at_to'), function ($query, $created_at_to) {
            return $query->whereDate('created_at', '<=', $created_at_to);
        });

        $allowedSortFields = ['code', 'price', 'created_at', 'vendor_id'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($limit);
    }


    public function getProposalById($id)
    {
        return Proposal::find($id);
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


    public function checkPermission(Proposal $proposal)
    {
        $user = Auth::user();

        if ($user->role == 'vendor') {
            if ($user->vendor->id != $proposal->vendor_id) {
                return false;
            }
        }

        return true;
    }
}
