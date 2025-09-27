<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| Customers Module API Routes
|--------------------------------------------------------------------------
| All routes are prefixed with 'api/v1/customers-management'
| All routes require authentication via Sanctum
*/

Route::middleware(['auth:sanctum'])->prefix('v1/customers-management')->group(function () {
    Route::prefix('customers')->name('customers-management.')->group(function () {

        // ========================================
        // BASIC CRUD OPERATIONS
        // ========================================

        // List all customers with search, filter, sort, pagination
        Route::get('/list-all', [CustomerController::class, 'index'])
            ->name('customers.list-all');

        // Create new customer
        Route::post('/create-new', [CustomerController::class, 'store'])
            ->name('customers.create-new');

        // Show specific customer details
        Route::get('/show-details/{customer}', [CustomerController::class, 'show'])
            ->name('customers.show-details');

        // Update customer (full update)
        Route::put('/update-customer/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update-customer');

        // Update customer (partial update)
        Route::patch('/patch-customer/{customer}', [CustomerController::class, 'update'])
            ->name('customers.patch-customer');

        // Delete customer (soft delete)
        Route::delete('/delete-customer/{customer}', [CustomerController::class, 'destroy'])
            ->name('customers.delete-customer');

        // ========================================
        // SOFT DELETE OPERATIONS
        // ========================================

        // Restore soft deleted customer
        Route::post('/restore-customer/{customer}', [CustomerController::class, 'restore'])
            ->name('customers.restore-customer');

        // ========================================
        // BULK OPERATIONS
        // ========================================

        // Bulk delete customers
        Route::delete('/bulk-operations/delete-multiple', [CustomerController::class, 'bulkDelete'])
            ->name('customers.bulk-delete-multiple');

        // Bulk restore customers
        Route::post('/bulk-operations/restore-multiple', [CustomerController::class, 'bulkRestore'])
            ->name('customers.bulk-restore-multiple');

        // ========================================
        // SEARCH AND FILTER OPERATIONS
        // ========================================

        // Advanced search with multiple criteria
        Route::post('/search/advanced-search', [CustomerController::class, 'advancedSearch'])
            ->name('customers.advanced-search');

        // Simple search by query
        Route::get('/search/find-by-query/{query}', [CustomerController::class, 'search'])
            ->name('customers.find-by-query');

        // Filter by status
        Route::get('/filter/filter-by-status/{status}', [CustomerController::class, 'filterByStatus'])
            ->name('customers.filter-by-status');

        // Filter by company
        Route::get('/filter/filter-by-company/{companyId}', [CustomerController::class, 'filterByCompany'])
            ->name('customers.filter-by-company');

        // ========================================
        // SORTING AND FIELD OPERATIONS
        // ========================================

        // Sort by specific field
        Route::get('/sort/sort-by-field/{field}', [CustomerController::class, 'sortByField'])
            ->name('customers.sort-by-field');

        // Get customers by specific field value
        Route::get('/field/get-by-field/{field}/value/{value}', [CustomerController::class, 'getByField'])
            ->name('customers.get-by-field-value');

        // Get unique values for a specific field
        Route::get('/field/get-field-values/{field}', [CustomerController::class, 'getFieldValues'])
            ->name('customers.get-field-values');

        // ========================================
        // TRANSACTION OPERATIONS
        // ========================================

        // Get customers with their last transaction
        Route::get('/transactions/get-with-last-transaction', [CustomerController::class, 'getWithLastTransaction'])
            ->name('customers.get-with-last-transaction');

        // ========================================
        // STATISTICS AND REPORTS
        // ========================================

        // Get customer statistics overview
        Route::get('/reports/get-statistics', [CustomerController::class, 'getStats'])
            ->name('customers.get-statistics');

        // Export customers to Excel
        Route::get('/export/export-to-excel', [CustomerController::class, 'exportExcel'])
            ->name('customers.export-to-excel');

        // Import customers from Excel
        Route::post('/import/import-from-excel', [CustomerController::class, 'importExcel'])
            ->name('customers.import-from-excel');

        // ========================================
        // FORM DATA AND DROPDOWN ENDPOINTS
        // ========================================

        // Get form data for create/edit forms
        Route::get('/form-data/get-complete-form-data', [CustomerController::class, 'getFormData'])
            ->name('customers.get-complete-form-data');

        // Get next available customer number
        Route::get('/form-data/get-next-customer-number', [CustomerController::class, 'getNextCustomerNumber'])
            ->name('customers.get-next-customer-number');

        // Get sales representatives list
        Route::get('/form-data/get-sales-representatives', [CustomerController::class, 'getSalesRepresentatives'])
            ->name('customers.get-sales-representatives');
    });
});
