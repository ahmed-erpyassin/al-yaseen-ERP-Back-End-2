<?php

use Illuminate\Support\Facades\Route;
use Modules\FinancialAccounts\Http\Controllers\CurrenciesController;
use Modules\FinancialAccounts\Http\Controllers\ExchangeRatesController;
use Modules\FinancialAccounts\Http\Controllers\FiscalYearsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('currencies', CurrenciesController::class)->names('currencies');
    Route::apiResource('exchange-rates', ExchangeRatesController::class)->names('exchange_rates');
    Route::apiResource('fiscal-years', FiscalYearsController::class)->names('fiscal_years');
});
