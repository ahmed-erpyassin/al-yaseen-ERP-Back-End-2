<?php

use Illuminate\Support\Facades\Route;
use Modules\FinancialAccounts\Http\Controllers\FinancialAccountsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('financialaccounts', FinancialAccountsController::class)->names('financialaccounts');
});
