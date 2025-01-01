<?php

namespace App\Services\System;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Helper\FilterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function index()
    {

        $user = Auth::user();

        $query = Order::query()->with([
            'serviceRequest.client.user',
            'serviceRequest.service',
            'vendor.user',
            'proposal',
            'workLocation',
        ]);

        if ($user->role == 'vendor') {
            $query->where('vendor_id', $user->vendor->id);
        }

        if ($user->role == 'client') {
            $query->whereHas('serviceRequest.client', function ($subQuery) use ($user) {
                $subQuery->where('id', $user->client->id);
            });
        }

        $searchFields = ['code', 'title', 'description', 'estimated_hours'];
        $numericFields = ['price', 'works_hours'];
        $dateFields = ['created_at', 'start_date', 'completion_date'];
        $exactMatchFields = ['service_request_id', 'vendor_id', 'proposal_id', 'status', 'payment_type'];
        $inFields = ['status'];

        $orders =  FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return  $orders;
    }

    public function getOrderById($id, $relationships = [])
    {
        $order = Order::find($id);

        if (!$order) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404)
            );
        }

        $order->load($relationships);

        return $order;
    }

    public function createOrder(array $data)
    {
        $data['code'] = 'ORD' . rand(100000, 999999);
        $order = Order::create($data);
        $order->update(['code' => 'ORD' . sprintf('%03d', $order->id)]);
        return $order;
    }

    public function updateOrder(Order $order, array $data)
    {
        $order->update($data);
        return $order;
    }


    public function updateOrderStatus(Order $order, $status)
    {

        $data = [];
        if ($status == 'execute') {
            $data['start_date'] = Carbon::now();
        } elseif ($status == 'completed') {
            $data['completion_date'] = Carbon::now();
        }

        $data['status'] = $status;


        $order->update($data);
        return $order;
    }

    public function deleteOrder(Order $order)
    {
        $order->delete();
    }

    //createInvoice
    public function createInvoice(Order $order)
    {
        $invoiceService = new InvoiceService();
        $price = $order->payment_type == 'flat_rate' ? $order->price : $order->works_hours * $order->price;
        $invoice =    $invoiceService->createInvoice(
            [
                'code' => 'INV' . rand(100000, 999999),
                'order_id' => $order->id,
                'client_id' => $order->serviceRequest->client_id,
                'price' => $price,
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(7),
                'paid_at' => null,
                'description' => 'Invoice for order ' . $order->code,
            ]
        );

        return $invoice;
    }
}
