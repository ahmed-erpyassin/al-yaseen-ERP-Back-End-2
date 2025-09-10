<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\InvoiceRequest;
use Modules\Billing\Services\InvoiceService;
use Modules\Billing\Transformers\InvoiceResource;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $service)
    {
        $this->invoiceService = $service;
    }

    public function index(Request $request)
    {
        $invoices = $this->invoiceService->getInvoices($request->user());
        return InvoiceResource::collection($invoices);
    }

    public function show($id)
    {
        $invoice = $this->invoiceService->getInvoiceById($id);
        return new InvoiceResource($invoice);
    }

    public function store(InvoiceRequest $request)
    {
        $invoice = $this->invoiceService->createInvoice($request->validated(), $request->user());
        return new InvoiceResource($invoice);
    }

    public function update(InvoiceRequest $request, $id)
    {
        $invoice = $this->invoiceService->updateInvoice($id, $request->validated());
        return new InvoiceResource($invoice);
    }

    public function destroy(Request $request, $id)
    {
        $this->invoiceService->deleteInvoice($id, $request->user()->id);
        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    public function approve(Request $request, $id)
    {
        $invoice = $this->invoiceService->getInvoiceById($id);
        // Logic to approve the invoice
        return response()->json(['message' => 'Invoice approved successfully']);
    }
}
