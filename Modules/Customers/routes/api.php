<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::prefix('customers')->group(function () {
        // CRUD operations
        Route::get('/list', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/create', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/details/{id}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('/patch/{id}', [CustomerController::class, 'update'])->name('customers.patch');
        Route::delete('/delete/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Soft delete operations
        Route::get('/deleted/list', [CustomerController::class, 'getDeleted'])->name('customers.deleted.list');
        Route::post('/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');

        // Bulk operations
        Route::delete('/bulk/delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk.delete');
        Route::post('/bulk/restore', [CustomerController::class, 'bulkRestore'])->name('customers.bulk.restore');

        // Search and filter operations
        Route::get('/search/advanced', [CustomerController::class, 'search'])->name('customers.search.advanced');
        Route::get('/filter/status/{status}', [CustomerController::class, 'filterByStatus'])->name('customers.filter.status');
        Route::get('/filter/company/{companyId}', [CustomerController::class, 'filterByCompany'])->name('customers.filter.company');

        // Helper endpoints
        Route::get('/helpers/form-data', [CustomerController::class, 'getFormData'])->name('customers.helpers.form-data');
        Route::get('/helpers/search-form-data', [CustomerController::class, 'getSearchFormData'])->name('customers.helpers.search-form-data');
        Route::get('/helpers/sortable-fields', [CustomerController::class, 'getSortableFields'])->name('customers.helpers.sortable-fields');
        Route::get('/helpers/countries', [CustomerController::class, 'getCountries'])->name('customers.helpers.countries');
        Route::get('/helpers/regions/{countryId}', [CustomerController::class, 'getRegions'])->name('customers.helpers.regions');
        Route::get('/helpers/cities/{regionId}', [CustomerController::class, 'getCities'])->name('customers.helpers.cities');
        Route::get('/helpers/currencies', [CustomerController::class, 'getCurrencies'])->name('customers.helpers.currencies');
        Route::get('/helpers/employees', [CustomerController::class, 'getEmployees'])->name('customers.helpers.employees');

        // Statistics and reports
        Route::get('/stats/overview', [CustomerController::class, 'getStats'])->name('customers.stats.overview');
        Route::get('/stats/by-status', [CustomerController::class, 'getStatsByStatus'])->name('customers.stats.by-status');
        Route::get('/stats/by-region', [CustomerController::class, 'getStatsByRegion'])->name('customers.stats.by-region');

        // Import/Export operations
        Route::get('/export/excel', [CustomerController::class, 'exportExcel'])->name('customers.export.excel');
        Route::get('/export/pdf', [CustomerController::class, 'exportPdf'])->name('customers.export.pdf');
        Route::post('/import/excel', [CustomerController::class, 'importExcel'])->name('customers.import.excel');
        Route::get('/import/template', [CustomerController::class, 'downloadImportTemplate'])->name('customers.import.template');
    });
});
