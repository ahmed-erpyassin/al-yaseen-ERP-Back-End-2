<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\app\Services\CurrencyService;
use Modules\FinancialAccounts\Http\Requests\CurrencyRequest;
use Modules\FinancialAccounts\Transformers\CurrencyResource;

class CurrenciesController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $currencies = $this->currencyService->getCurrencies($request->user());
        return CurrencyResource::collection($currencies);
    }

    public function store(CurrencyRequest $request)
    {
        $currency = $this->currencyService->createCurrency($request->validated(), $request->user());
        return new CurrencyResource($currency);
    }

    public function show($id)
    {
        dd($id);
        $currency = $this->currencyService->getCurrencyById($id);
        return new CurrencyResource($currency);
    }

    public function update(CurrencyRequest $request, $id)
    {
        $currency = $this->currencyService->updateCurrency($id, $request->validated());
        return new CurrencyResource($currency);
    }

    public function destroy(Request $request, $id)
    {
        $this->currencyService->deleteCurrency($id, $request->user()->id);
        return response()->json(['message' => 'Currency deleted successfully']);
    }
}
