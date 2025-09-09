<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\app\Services\ExchangeRateService;
use Modules\FinancialAccounts\Http\Requests\ExchangeRateRequest;
use Modules\FinancialAccounts\Transformers\ExchangeRateResource;

class ExchangeRatesController extends Controller
{
    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function index(Request $request)
    {
        $rates = $this->exchangeRateService->getExchangeRates($request->user());
        return ExchangeRateResource::collection($rates);
    }

    public function store(ExchangeRateRequest $request)
    {
        $rate = $this->exchangeRateService->createExchangeRate($request->validated(), $request->user());
        return new ExchangeRateResource($rate);
    }

    public function show($id)
    {
        $rate = $this->exchangeRateService->getExchangeRateById($id);
        return new ExchangeRateResource($rate);
    }

    public function update(ExchangeRateRequest $request, $id)
    {
        $rate = $this->exchangeRateService->updateExchangeRate($id, $request->validated());
        return new ExchangeRateResource($rate);
    }

    public function destroy(Request $request, $id)
    {
        $this->exchangeRateService->deleteExchangeRate($id, $request->user()->id);
        return response()->json(['message' => 'Exchange Rate deleted successfully']);
    }
}
