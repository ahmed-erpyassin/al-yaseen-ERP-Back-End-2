<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\app\Services\TaxRateService;
use Modules\FinancialAccounts\Http\Requests\TaxRateRequest;
use Modules\FinancialAccounts\Transformers\TaxRateResource;

class TaxRateController extends Controller
{
    protected $taxRateService;

    public function __construct(TaxRateService $taxRateService)
    {
        $this->taxRateService = $taxRateService;
    }

    public function index(Request $request)
    {
        $taxRates = $this->taxRateService->getTaxRates($request->user());
        return TaxRateResource::collection($taxRates);
    }

    public function store(TaxRateRequest $request)
    {
        $taxRate = $this->taxRateService->createTaxRate($request->validated(), $request->user());
        return new TaxRateResource($taxRate);
    }

    public function show($id)
    {
        $taxRate = $this->taxRateService->getTaxRateById($id);
        return new TaxRateResource($taxRate);
    }

    public function update(TaxRateRequest $request, $id)
    {
        $taxRate = $this->taxRateService->updateTaxRate($id, $request->validated());
        return new TaxRateResource($taxRate);
    }

    public function destroy(Request $request, $id)
    {
        $this->taxRateService->deleteTaxRate($id, $request->user()->id);
        return response()->json(['message' => 'Tax rate deleted successfully']);
    }
}
