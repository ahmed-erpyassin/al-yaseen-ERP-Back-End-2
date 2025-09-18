<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\app\Services\FiscalYearService;
use Modules\FinancialAccounts\Http\Requests\FiscalYearRequest;
use Modules\FinancialAccounts\Transformers\FiscalYearResource;

class FiscalYearsController extends Controller
{
    protected $fiscalYearService;

    public function __construct(FiscalYearService $fiscalYearService)
    {
        $this->fiscalYearService = $fiscalYearService;
    }

    public function index(Request $request)
    {
        $years = $this->fiscalYearService->getFiscalYears($request->user());
        return FiscalYearResource::collection($years);
    }

    public function store(FiscalYearRequest $request)
    {
        $year = $this->fiscalYearService->createFiscalYear($request->validated(), $request->user());
        return new FiscalYearResource($year);
    }

    public function show($id)
    {
        $year = $this->fiscalYearService->getFiscalYearById($id);
        return new FiscalYearResource($year);
    }

    public function update(FiscalYearRequest $request, $id)
    {
        $year = $this->fiscalYearService->updateFiscalYear($id, $request->validated());
        return new FiscalYearResource($year);
    }

    public function destroy(Request $request, $id)
    {
        $this->fiscalYearService->deleteFiscalYear($id, $request->user()->id);
        return response()->json(['message' => 'Fiscal Year deleted successfully']);
    }
}
