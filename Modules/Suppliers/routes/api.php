<?php

use Illuminate\Support\Facades\Route;
use Modules\Suppliers\Http\Controllers\SupplierController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::prefix('suppliers')->group(function () {
        // Basic CRUD operations
        Route::get('/list', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/create', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/details/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::put('/update/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/delete/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::post('/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

        // Search and filtering
        Route::get('/search/advanced', [SupplierController::class, 'search'])->name('suppliers.search.advanced');

        // Helper endpoints
        Route::get('/helpers/form-data', [SupplierController::class, 'getFormData'])->name('suppliers.helpers.form-data');
        Route::get('/helpers/search-form-data', [SupplierController::class, 'getSearchFormData'])->name('suppliers.helpers.search-form-data');
        Route::get('/helpers/sortable-fields', [SupplierController::class, 'getSortableFields'])->name('suppliers.helpers.sortable-fields');

        // Deleted suppliers management
        Route::get('/deleted/list', [SupplierController::class, 'getDeleted'])->name('suppliers.deleted.list');
        Route::delete('/deleted/{id}/force-delete', [SupplierController::class, 'forceDelete'])->name('suppliers.deleted.force-delete');
    });

});
