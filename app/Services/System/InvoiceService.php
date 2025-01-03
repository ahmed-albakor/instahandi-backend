<?php

namespace App\Services\System;

use App\Models\Invoice;
use App\Services\Helper\FilterService;


class InvoiceService 
{
    public function index()
    {
        $query = Invoice::query()->with(['order']);

        $requests = request()->all();
        $searchFields = ['code', 'description'];
        $numericFields = ['price'];  // price_min, price_max
        $dateFields = ['created_at', 'due_date', 'paid_at'];// created_at_from, created_at_to, due_date_from, due_date_to, paid_at_from, paid_at_to
        $exactMatchFields = ['status', 'order_id', 'client_id']; // sta
        $inFields = ['status'];

        // 

        $invoices = FilterService::applyFilters(
            $query,
            $requests,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $invoices;
    }

    public function show($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Invoice not found.',
                ], 404)
            );
        }

        return $invoice;
    }

    public function createInvoice($data)
    {
        $invoice = Invoice::create($data);

        return $invoice;
    }

    public function updateInvoice($id, $data)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Invoice not found.',
                ], 404)
            );
        }

        $invoice->update($data);

        return $invoice;
    }

    public function deleteInvoice($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'Invoice not found.',
                ], 404)
            );
        }

        $invoice->delete();

        return $invoice;
    }
}
