<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectsManagment\Http\Controllers\ProjectsManagmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Project Management Routes
    Route::prefix('projects')->group(function () {
        // Main CRUD operations
        Route::get('/', [ProjectsManagmentController::class, 'index'])->name('projects.index');
        Route::post('/', [ProjectsManagmentController::class, 'store'])->name('projects.store');
        Route::get('/{id}', [ProjectsManagmentController::class, 'show'])->name('projects.show');
        Route::put('/{id}', [ProjectsManagmentController::class, 'update'])->name('projects.update');
        Route::delete('/{id}', [ProjectsManagmentController::class, 'destroy'])->name('projects.destroy');

        // Advanced search and filtering
        Route::post('/search', [ProjectsManagmentController::class, 'search'])->name('projects.search');
        Route::get('/filter/by-field', [ProjectsManagmentController::class, 'getProjectsByField'])->name('projects.filter-by-field');
        Route::get('/fields/values', [ProjectsManagmentController::class, 'getFieldValues'])->name('projects.field-values');
        Route::get('/fields/sortable', [ProjectsManagmentController::class, 'getSortableFields'])->name('projects.sortable-fields');
        Route::post('/sort', [ProjectsManagmentController::class, 'sortProjects'])->name('projects.sort');

        // Soft delete management
        Route::post('/{id}/restore', [ProjectsManagmentController::class, 'restore'])->name('projects.restore');
        Route::delete('/{id}/force-delete', [ProjectsManagmentController::class, 'forceDelete'])->name('projects.force-delete');
        Route::get('/trashed/list', [ProjectsManagmentController::class, 'getTrashed'])->name('projects.trashed');

        // Helper endpoints for dropdown data
        Route::get('/customers/list', [ProjectsManagmentController::class, 'getCustomers'])->name('projects.customers');
        Route::get('/customers/{customerId}/data', [ProjectsManagmentController::class, 'getCustomerData'])->name('projects.customer-data');
        Route::get('/currencies/list', [ProjectsManagmentController::class, 'getCurrencies'])->name('projects.currencies');
        Route::get('/employees/list', [ProjectsManagmentController::class, 'getEmployees'])->name('projects.employees');
        Route::get('/countries/list', [ProjectsManagmentController::class, 'getCountries'])->name('projects.countries');
        Route::get('/statuses/list', [ProjectsManagmentController::class, 'getProjectStatuses'])->name('projects.statuses');

        // Utility endpoints
        Route::post('/calculate-vat', [ProjectsManagmentController::class, 'calculateVAT'])->name('projects.calculate-vat');
        Route::get('/generate-code', [ProjectsManagmentController::class, 'generateProjectCode'])->name('projects.generate-code');
    });
});
