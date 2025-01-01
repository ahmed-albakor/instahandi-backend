<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\CreateRequest;
use App\Http\Requests\Invoice\UpdateRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\Helper\ResponseService;
use App\Services\System\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        $invoices = $this->invoiceService->index();

        return response()->json([
            'success' => true,
            'data' => InvoiceResource::collection($invoices->items()),
            'meta' => ResponseService::meta($invoices),
        ]);
    }

    public function show($id)
    {
        $invoice = $this->invoiceService->show($id);

        return response()->json([
            'success' => true,
            'data' => new InvoiceResource($invoice),
        ]);
    }

    public function create(CreateRequest $request)
    {
        $invoice = $this->invoiceService->createInvoice($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully.',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    public function update($id, UpdateRequest $request)
    {
        $invoice = $this->invoiceService->updateInvoice($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully.',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    public function delete($id)
    {
        $this->invoiceService->deleteInvoice($id);

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully.',
        ]);
    }
}
