<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('customers')->group(function () {
        // Basic CRUD operations
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('customers.patch');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Soft delete operations
        Route::post('/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');

        // Bulk operations
        Route::delete('/bulk/delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
        Route::post('/bulk/restore', [CustomerController::class, 'bulkRestore'])->name('customers.bulk-restore');

        // Advanced search and filter operations
        Route::post('/advanced-search', [CustomerController::class, 'advancedSearch'])->name('customers.advanced-search');
        Route::get('/search/{query}', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/filter/status/{status}', [CustomerController::class, 'filterByStatus'])->name('customers.filter-status');
        Route::get('/filter/company/{companyId}', [CustomerController::class, 'filterByCompany'])->name('customers.filter-company');

        // Sorting and field-specific operations
        Route::get('/sort/{field}', [CustomerController::class, 'sortByField'])->name('customers.sort-by-field');
        Route::get('/field/{field}/{value}', [CustomerController::class, 'getByField'])->name('customers.get-by-field');
        Route::get('/field-values/{field}', [CustomerController::class, 'getFieldValues'])->name('customers.field-values');

        // Transaction-related operations
        Route::get('/with-transactions', [CustomerController::class, 'getWithLastTransaction'])->name('customers.with-transactions');

        // Statistics and reports
        Route::get('/stats/overview', [CustomerController::class, 'getStats'])->name('customers.stats');
        Route::get('/export/excel', [CustomerController::class, 'exportExcel'])->name('customers.export-excel');
        Route::post('/import/excel', [CustomerController::class, 'importExcel'])->name('customers.import-excel');
    });
});
