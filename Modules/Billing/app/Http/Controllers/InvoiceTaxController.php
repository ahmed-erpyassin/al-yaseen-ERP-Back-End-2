<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\InvoiceTaxRequest;
use Modules\Billing\Services\InvoiceTaxService;
use Modules\Billing\Transformers\InvoiceTaxResource;

class InvoiceTaxController extends Controller
{
    protected $invoiceTaxService;

    public function __construct(InvoiceTaxService $invoiceTaxService)
    {
        $this->invoiceTaxService = $invoiceTaxService;
    }

    public function index(Request $request)
    {
        $invoiceTaxes = $this->invoiceTaxService->getInvoiceTaxes($request->user());
        return InvoiceTaxResource::collection($invoiceTaxes);
    }

    public function show($id)
    {
        $invoiceTax = $this->invoiceTaxService->getInvoiceTaxById($id);
        return new InvoiceTaxResource($invoiceTax);
    }

    public function store(InvoiceTaxRequest $request)
    {
        $invoiceTax = $this->invoiceTaxService->createInvoiceTax($request->validated(), $request->user());
        return new InvoiceTaxResource($invoiceTax);
    }

    public function update(InvoiceTaxRequest $request, $id)
    {
        $invoiceTax = $this->invoiceTaxService->updateInvoiceTax($id, $request->validated());
        return new InvoiceTaxResource($invoiceTax);
    }

    public function destroy(Request $request, $id)
    {
        $this->invoiceTaxService->deleteInvoiceTax($id);
        return response()->json(['message' => 'Invoice tax deleted successfully']);
    }
}
