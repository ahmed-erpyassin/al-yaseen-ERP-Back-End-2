<?php

use Illuminate\Support\Facades\Route;
use Modules\FinancialAccounts\Http\Controllers\AccountController;
use Modules\FinancialAccounts\Http\Controllers\AccountGroupController;
use Modules\FinancialAccounts\Http\Controllers\BudgetController;
use Modules\FinancialAccounts\Http\Controllers\CostCenterController;
use Modules\FinancialAccounts\Http\Controllers\CurrenciesController;
use Modules\FinancialAccounts\Http\Controllers\ExchangeRatesController;
use Modules\FinancialAccounts\Http\Controllers\FaAttachmentController;
use Modules\FinancialAccounts\Http\Controllers\FiscalYearsController;
use Modules\FinancialAccounts\Http\Controllers\JournalEntriesController;
use Modules\FinancialAccounts\Http\Controllers\JournalEntriesLinesController;
use Modules\FinancialAccounts\Http\Controllers\JournalFinancialController;
use Modules\FinancialAccounts\Http\Controllers\TaxRateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::prefix('currencies')->group(function () {
        Route::apiResource('', CurrenciesController::class);
    });

    Route::prefix('exchange-rates')->group(function () {
        Route::apiResource('', ExchangeRatesController::class);
    });

    Route::prefix('fiscal-years')->group(function () {
        Route::apiResource('', FiscalYearsController::class);
    });

    Route::prefix('accounts')->group(function () {
        Route::apiResource('', AccountController::class);
    });

    Route::prefix('account-groups')->group(function () {
        Route::apiResource('', AccountGroupController::class);
    });

    Route::prefix('cost-centers')->group(function () {
        Route::apiResource('', CostCenterController::class);
    });

    Route::prefix('budgets')->group(function () {
        Route::apiResource('', BudgetController::class);
    });

    Route::prefix('tax-rates')->group(function () {
        Route::apiResource('', TaxRateController::class);
    });

    Route::prefix('journals-entries-lines')->group(function () {
        Route::apiResource('', JournalEntriesLinesController::class);
    });

    Route::prefix('journals-entries')->group(function () {
        Route::apiResource('', JournalEntriesController::class);
    });

    Route::prefix('journals-financial')->group(function () {
        Route::apiResource('', JournalFinancialController::class);
    });

    Route::prefix('fa-attachments')->group(function () {
        Route::apiResource('', FaAttachmentController::class);
    });
});
