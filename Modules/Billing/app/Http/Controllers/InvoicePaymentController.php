<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\InvoicePaymentRequest;
use Modules\Billing\Services\InvoicePaymentService;
use Modules\Billing\Transformers\InvoicePaymentResource;

class InvoicePaymentController extends Controller
{
    protected $invoicePaymentService;

    public function __construct(InvoicePaymentService $invoicePaymentService)
    {
        $this->invoicePaymentService = $invoicePaymentService;
    }

    public function index(Request $request)
    {
        $invoicePayments = $this->invoicePaymentService->getInvoicePayments($request->user());
        return InvoicePaymentResource::collection($invoicePayments);
    }

    public function show($id)
    {
        $invoicePayment = $this->invoicePaymentService->getInvoicePaymentById($id);
        return new InvoicePaymentResource($invoicePayment);
    }

    public function store(InvoicePaymentRequest $request)
    {
        $invoicePayment = $this->invoicePaymentService->createInvoicePayment($request->validated(), $request->user());
        return new InvoicePaymentResource($invoicePayment);
    }

    public function update(InvoicePaymentRequest $request, $id)
    {
        $invoicePayment = $this->invoicePaymentService->updateInvoicePayment($id, $request->validated());
        return new InvoicePaymentResource($invoicePayment);
    }

    public function destroy(Request $request, $id)
    {
        $this->invoicePaymentService->deleteInvoicePayment($id);
        return response()->json(['message' => 'Invoice payment deleted successfully']);
    }
}
