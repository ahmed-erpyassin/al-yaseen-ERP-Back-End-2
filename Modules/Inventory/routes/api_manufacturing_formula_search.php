<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\ManufacturingFormulaController;

/*
|--------------------------------------------------------------------------
| ✅ Manufacturing Formula Search & Management API Routes
|--------------------------------------------------------------------------
|
| Complete Manufacturing Formula search and management system with:
| - Advanced search by formula number, item details, duration, quantity, dates
| - Date range filtering (exact date + from/to date ranges)
| - Full update functionality with comprehensive validation
| - Data display with field-based selection and dynamic data display
| - Universal ascending/descending sorting for all fields
| - Proper soft delete management with audit trails
|
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // ✅ Manufacturing Formula Search & Management Operations
    Route::prefix('manufacturing-formulas')->group(function () {

        // ✅ Core CRUD Operations
        Route::get('/', [ManufacturingFormulaController::class, 'index'])->name('manufacturing-formulas.index');
        Route::post('/', [ManufacturingFormulaController::class, 'store'])->name('manufacturing-formulas.store');
        Route::get('/{id}', [ManufacturingFormulaController::class, 'show'])->name('manufacturing-formulas.show');
        Route::put('/{id}', [ManufacturingFormulaController::class, 'update'])->name('manufacturing-formulas.update');
        Route::delete('/{id}', [ManufacturingFormulaController::class, 'destroy'])->name('manufacturing-formulas.destroy');

        // ✅ Data Display and Field Selection Support
        Route::get('/fields/available', [ManufacturingFormulaController::class, 'getAvailableFields'])->name('manufacturing-formulas.available-fields');
        Route::get('/fields/values', [ManufacturingFormulaController::class, 'getFieldValues'])->name('manufacturing-formulas.field-values');

        // ✅ Soft Delete Management
        Route::get('/trashed/list', [ManufacturingFormulaController::class, 'trashed'])->name('manufacturing-formulas.trashed');
        Route::post('/{id}/restore', [ManufacturingFormulaController::class, 'restore'])->name('manufacturing-formulas.restore');
        Route::delete('/{id}/force-delete', [ManufacturingFormulaController::class, 'forceDelete'])->name('manufacturing-formulas.force-delete');

        // ✅ Helper Endpoints (from existing functionality)
        Route::get('/items/numbers', [ManufacturingFormulaController::class, 'getItemNumbers'])->name('manufacturing-formulas.item-numbers');
        Route::get('/items/details', [ManufacturingFormulaController::class, 'getItemDetails'])->name('manufacturing-formulas.item-details');
        Route::post('/cost/calculate', [ManufacturingFormulaController::class, 'calculateCost'])->name('manufacturing-formulas.calculate-cost');

        // ✅ NEW: Field-Based Data Display System
        Route::get('/field-based-data', [ManufacturingFormulaController::class, 'getFieldBasedData'])->name('manufacturing-formulas.field-based-data');
        Route::get('/selectable-fields', [ManufacturingFormulaController::class, 'getSelectableFields'])->name('manufacturing-formulas.selectable-fields');

        // ✅ NEW: Additional Helper Endpoints
        Route::get('/formula-numbers', [ManufacturingFormulaController::class, 'getManufacturingFormulaNumbers'])->name('manufacturing-formulas.formula-numbers');
        Route::get('/item-by-formula', [ManufacturingFormulaController::class, 'getItemByFormulaNumber'])->name('manufacturing-formulas.item-by-formula');
        Route::get('/warehouses', [ManufacturingFormulaController::class, 'getWarehouses'])->name('manufacturing-formulas.warehouses');

        // ✅ NEW: Pricing Management from Suppliers Table
        Route::post('/{id}/update-prices-from-suppliers', [ManufacturingFormulaController::class, 'updatePricesFromSuppliers'])->name('manufacturing-formulas.update-prices-from-suppliers');
        Route::post('/update-all-prices-from-suppliers', [ManufacturingFormulaController::class, 'updateAllPricesFromSuppliers'])->name('manufacturing-formulas.update-all-prices-from-suppliers');
    });
});

/*
|--------------------------------------------------------------------------
| ✅ Manufacturing Formula Search & Management API Endpoints Summary
|--------------------------------------------------------------------------
|
| Core CRUD Operations:
| GET    /api/v1/manufacturing-formulas              # List/search manufacturing formulas
| POST   /api/v1/manufacturing-formulas              # Create new manufacturing formula
| GET    /api/v1/manufacturing-formulas/{id}         # Show specific manufacturing formula
| PUT    /api/v1/manufacturing-formulas/{id}         # Update manufacturing formula
| DELETE /api/v1/manufacturing-formulas/{id}         # Delete manufacturing formula (soft delete)
|
| Data Display and Field Selection:
| GET    /api/v1/manufacturing-formulas/fields/available    # Get all available fields for display
| GET    /api/v1/manufacturing-formulas/fields/values       # Get field values for dropdown filtering
|
| Soft Delete Management:
| GET    /api/v1/manufacturing-formulas/trashed/list        # Get trashed (deleted) formulas
| POST   /api/v1/manufacturing-formulas/{id}/restore        # Restore soft deleted formula
| DELETE /api/v1/manufacturing-formulas/{id}/force-delete   # Permanently delete formula
|
| Helper Endpoints:
| GET    /api/v1/manufacturing-formulas/items/numbers       # Get item numbers for dropdown
| GET    /api/v1/manufacturing-formulas/items/details       # Get item details by number/name
| POST   /api/v1/manufacturing-formulas/cost/calculate      # Calculate formula costs
|
|--------------------------------------------------------------------------
| ✅ Advanced Search Parameters
|--------------------------------------------------------------------------
|
| Manufacturing Formula Number Search:
| ?formula_number=MF-001                    # Search by formula number (partial match)
|
| Item Details Search:
| ?item_number=ITM-005                      # Search by item number (partial match)
| ?item_name=White Bread                    # Search by item name (partial match)
|
| Manufacturing Duration Search:
| ?manufacturing_duration=2 days            # Search by duration (partial match)
|
| Produced Quantity Search:
| ?produced_quantity=100                    # Search by exact produced quantity
| ?produced_quantity_from=50                # Search produced quantity from value
| ?produced_quantity_to=200                 # Search produced quantity to value
|
| Date Search (Exact Date):
| ?date=2024-01-15                          # Search by exact creation date
| ?formula_date=2024-01-15                  # Search by exact formula date
|
| Date Range Search (From/To):
| ?date_from=2024-01-01                     # Search from creation date
| ?date_to=2024-01-31                       # Search to creation date
| ?formula_date_from=2024-01-01             # Search from formula date
| ?formula_date_to=2024-01-31               # Search to formula date
|
| Status and Activity Search:
| ?status=active                            # Search by status (draft/active/inactive/archived)
| ?is_active=true                           # Search by active status (true/false)
|
| Cost Range Search:
| ?labor_cost_from=10                       # Search labor cost from value
| ?labor_cost_to=100                        # Search labor cost to value
| ?total_cost_from=50                       # Search total cost from value
| ?total_cost_to=500                        # Search total cost to value
|
| General Search:
| ?search=bread                             # Search across multiple fields (formula number, name, description, item details)
|
| Pagination:
| ?page=1                                   # Page number
| ?per_page=15                              # Items per page (default: 15)
|
| Universal Sorting (All Fields):
| ?sort_by=formula_number                   # Sort by field name
| ?sort_direction=asc                       # Sort direction (asc/desc, default: desc)
|
| Available sort fields:
| - id, formula_number, formula_name, formula_description
| - item_number, item_name, manufacturing_duration
| - produced_quantity, consumed_quantity, labor_cost
| - operating_cost, waste_cost, total_production_cost
| - cost_per_unit, status, is_active, effective_from
| - effective_to, created_at, updated_at
|
|--------------------------------------------------------------------------
| ✅ Usage Examples
|--------------------------------------------------------------------------
|
| 1. Search Manufacturing Formulas with Advanced Filtering:
| GET /api/v1/manufacturing-formulas?formula_number=MF&item_name=bread&date_from=2024-01-01&sort_by=created_at&sort_direction=desc
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "id": 1,
|             "formula_number": "MF-202401-0001",
|             "formula_name": "White Bread Manufacturing Formula",
|             "item_number": "ITM-005",
|             "item_name": "White Bread",
|             "manufacturing_duration": "2 days",
|             "produced_quantity": 100,
|             "total_production_cost": 155.50,
|             "cost_per_unit": 1.56,
|             "status": "active",
|             "is_active": true,
|             "created_at": "2024-01-15 10:30:00",
|             "updated_at": "2024-01-15 10:30:00"
|         }
|     ],
|     "pagination": {
|         "current_page": 1,
|         "last_page": 1,
|         "per_page": 15,
|         "total": 1,
|         "from": 1,
|         "to": 1
|     },
|     "message": "Manufacturing formulas retrieved successfully"
| }
|
| 2. Get Available Fields for Dynamic Display:
| GET /api/v1/manufacturing-formulas/fields/available
| Response:
| {
|     "success": true,
|     "data": {
|         "formula_number": {
|             "label": "Formula Number",
|             "type": "string",
|             "sortable": true,
|             "searchable": true
|         },
|         "item_name": {
|             "label": "Item Name",
|             "type": "string",
|             "sortable": true,
|             "searchable": true
|         },
|         "produced_quantity": {
|             "label": "Produced Quantity",
|             "type": "number",
|             "sortable": true,
|             "searchable": true
|         }
|     },
|     "message": "Available fields retrieved successfully"
| }
|
| 3. Get Field Values for Dropdown Filtering:
| GET /api/v1/manufacturing-formulas/fields/values?field=status
| Response:
| {
|     "success": true,
|     "data": ["draft", "active", "inactive", "archived"],
|     "message": "Field values retrieved successfully"
| }
|
| 4. Update Manufacturing Formula:
| PUT /api/v1/manufacturing-formulas/1
| {
|     "formula_name": "Updated White Bread Formula",
|     "produced_quantity": 120,
|     "labor_cost": 60.00,
|     "status": "active"
| }
| Response:
| {
|     "success": true,
|     "data": {
|         // Full manufacturing formula resource data
|     },
|     "message": "Manufacturing formula updated successfully"
| }
|
| 5. Soft Delete Manufacturing Formula:
| DELETE /api/v1/manufacturing-formulas/1
| Response:
| {
|     "success": true,
|     "message": "Manufacturing formula deleted successfully"
| }
|
| 6. Get Trashed (Deleted) Formulas:
| GET /api/v1/manufacturing-formulas/trashed/list
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             // Deleted formula data with deleted_at timestamp
|         }
|     ],
|     "message": "Trashed manufacturing formulas retrieved successfully"
| }
|
| 7. Restore Soft Deleted Formula:
| POST /api/v1/manufacturing-formulas/1/restore
| Response:
| {
|     "success": true,
|     "data": {
|         // Restored formula data
|     },
|     "message": "Manufacturing formula restored successfully"
| }
|
| 8. Search with Date Ranges and Sorting:
| GET /api/v1/manufacturing-formulas?date_from=2024-01-01&date_to=2024-01-31&sort_by=total_production_cost&sort_direction=desc&per_page=10
| Response:
| {
|     "success": true,
|     "data": [
|         // Formulas sorted by total production cost (highest first)
|     ],
|     "pagination": {
|         "current_page": 1,
|         "per_page": 10,
|         "total": 25
|     },
|     "message": "Manufacturing formulas retrieved successfully"
| }
|
*/
