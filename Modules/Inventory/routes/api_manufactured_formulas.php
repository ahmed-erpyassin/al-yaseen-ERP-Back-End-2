<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\ManufacturedFormulaController;

/*
|--------------------------------------------------------------------------
| ✅ Manufactured Formulas API Routes
|--------------------------------------------------------------------------
|
| Complete Manufactured Formulas system with:
| - CRUD operations for manufactured formulas
| - Raw materials management
| - Field-based data display system
| - Advanced search and filtering
| - Universal sorting capabilities
| - Soft delete management
| - Cost calculation and inventory management
|
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {
    
    // ✅ Manufactured Formulas Operations
    Route::prefix('manufactured-formulas')->group(function () {
        
        // ✅ Core CRUD Operations
        Route::get('/', [ManufacturedFormulaController::class, 'index'])->name('manufactured-formulas.index');
        Route::post('/', [ManufacturedFormulaController::class, 'store'])->name('manufactured-formulas.store');
        Route::get('/{id}', [ManufacturedFormulaController::class, 'show'])->name('manufactured-formulas.show');
        Route::put('/{id}', [ManufacturedFormulaController::class, 'update'])->name('manufactured-formulas.update');
        Route::delete('/{id}', [ManufacturedFormulaController::class, 'destroy'])->name('manufactured-formulas.destroy');
        
        // ✅ Advanced Search & Filtering
        Route::get('/search/advanced', [ManufacturedFormulaController::class, 'advancedSearch'])->name('manufactured-formulas.advanced-search');
        Route::get('/filter/by-field', [ManufacturedFormulaController::class, 'filterByField'])->name('manufactured-formulas.filter-by-field');
        
        // ✅ Soft Delete Management
        Route::get('/trashed/list', [ManufacturedFormulaController::class, 'trashed'])->name('manufactured-formulas.trashed');
        Route::post('/{id}/restore', [ManufacturedFormulaController::class, 'restore'])->name('manufactured-formulas.restore');
        Route::delete('/{id}/force-delete', [ManufacturedFormulaController::class, 'forceDelete'])->name('manufactured-formulas.force-delete');
        
        // ✅ Field-Based Data Display System
        Route::get('/field-based-data', [ManufacturedFormulaController::class, 'getFieldBasedData'])->name('manufactured-formulas.field-based-data');
        Route::get('/selectable-fields', [ManufacturedFormulaController::class, 'getSelectableFields'])->name('manufactured-formulas.selectable-fields');
        
        // ✅ Helper Endpoints
        Route::get('/formula-numbers', [ManufacturedFormulaController::class, 'getFormulaNumbers'])->name('manufactured-formulas.formula-numbers');
        Route::get('/items/list', [ManufacturedFormulaController::class, 'getItems'])->name('manufactured-formulas.items');
        Route::get('/warehouses/list', [ManufacturedFormulaController::class, 'getWarehouses'])->name('manufactured-formulas.warehouses');
        Route::get('/units/list', [ManufacturedFormulaController::class, 'getUnits'])->name('manufactured-formulas.units');
        
        // ✅ Cost Calculation & Manufacturing
        Route::post('/{id}/calculate-cost', [ManufacturedFormulaController::class, 'calculateCost'])->name('manufactured-formulas.calculate-cost');
        Route::post('/{id}/manufacture', [ManufacturedFormulaController::class, 'manufacture'])->name('manufactured-formulas.manufacture');
        Route::get('/{id}/check-availability', [ManufacturedFormulaController::class, 'checkMaterialsAvailability'])->name('manufactured-formulas.check-availability');
        
        // ✅ Raw Materials Management
        Route::get('/{id}/raw-materials', [ManufacturedFormulaController::class, 'getRawMaterials'])->name('manufactured-formulas.raw-materials');
        Route::post('/{id}/raw-materials', [ManufacturedFormulaController::class, 'addRawMaterial'])->name('manufactured-formulas.add-raw-material');
        Route::put('/{id}/raw-materials/{rawMaterialId}', [ManufacturedFormulaController::class, 'updateRawMaterial'])->name('manufactured-formulas.update-raw-material');
        Route::delete('/{id}/raw-materials/{rawMaterialId}', [ManufacturedFormulaController::class, 'removeRawMaterial'])->name('manufactured-formulas.remove-raw-material');
        
        // ✅ Reports & Statistics
        Route::get('/reports/summary', [ManufacturedFormulaController::class, 'getSummaryReport'])->name('manufactured-formulas.summary-report');
        Route::get('/reports/cost-analysis', [ManufacturedFormulaController::class, 'getCostAnalysisReport'])->name('manufactured-formulas.cost-analysis');
        Route::get('/statistics/dashboard', [ManufacturedFormulaController::class, 'getDashboardStatistics'])->name('manufactured-formulas.dashboard-stats');
    });
});

/*
|--------------------------------------------------------------------------
| ✅ Manufactured Formulas API Endpoints Summary
|--------------------------------------------------------------------------
|
| Core CRUD Operations:
| GET    /api/v1/manufactured-formulas                    # List/Search manufactured formulas
| POST   /api/v1/manufactured-formulas                    # Create new manufactured formula
| GET    /api/v1/manufactured-formulas/{id}               # Show specific manufactured formula
| PUT    /api/v1/manufactured-formulas/{id}               # Update manufactured formula
| DELETE /api/v1/manufactured-formulas/{id}               # Delete manufactured formula (soft delete)
|
| Advanced Search & Filtering:
| GET    /api/v1/manufactured-formulas/search/advanced    # Advanced search with multiple criteria
| GET    /api/v1/manufactured-formulas/filter/by-field    # Filter by specific field values
|
| Soft Delete Management:
| GET    /api/v1/manufactured-formulas/trashed/list       # List deleted manufactured formulas
| POST   /api/v1/manufactured-formulas/{id}/restore       # Restore deleted manufactured formula
| DELETE /api/v1/manufactured-formulas/{id}/force-delete  # Permanently delete manufactured formula
|
| Field-Based Data Display System:
| GET    /api/v1/manufactured-formulas/field-based-data   # Get data based on selected field
| GET    /api/v1/manufactured-formulas/selectable-fields  # Get all available fields for selection
|
| Helper Endpoints:
| GET    /api/v1/manufactured-formulas/formula-numbers    # Get formula numbers for dropdown
| GET    /api/v1/manufactured-formulas/items/list         # Get items for dropdown
| GET    /api/v1/manufactured-formulas/warehouses/list    # Get warehouses for dropdown
| GET    /api/v1/manufactured-formulas/units/list         # Get units for dropdown
|
| Cost Calculation & Manufacturing:
| POST   /api/v1/manufactured-formulas/{id}/calculate-cost      # Calculate total manufacturing cost
| POST   /api/v1/manufactured-formulas/{id}/manufacture         # Execute manufacturing process
| GET    /api/v1/manufactured-formulas/{id}/check-availability  # Check raw materials availability
|
| Raw Materials Management:
| GET    /api/v1/manufactured-formulas/{id}/raw-materials                    # Get raw materials for formula
| POST   /api/v1/manufactured-formulas/{id}/raw-materials                    # Add raw material to formula
| PUT    /api/v1/manufactured-formulas/{id}/raw-materials/{rawMaterialId}    # Update raw material
| DELETE /api/v1/manufactured-formulas/{id}/raw-materials/{rawMaterialId}    # Remove raw material
|
| Reports & Statistics:
| GET    /api/v1/manufactured-formulas/reports/summary           # Get summary report
| GET    /api/v1/manufactured-formulas/reports/cost-analysis    # Get cost analysis report
| GET    /api/v1/manufactured-formulas/statistics/dashboard     # Get dashboard statistics
|
|--------------------------------------------------------------------------
| ✅ Usage Examples
|--------------------------------------------------------------------------
|
| 1. Create Manufactured Formula:
| POST /api/v1/manufactured-formulas
| {
|     "item_id": 10,
|     "formula_name": "معادلة إنتاج الخبز",
|     "formula_description": "معادلة لإنتاج الخبز الأبيض",
|     "manufacturing_duration": "4 hours",
|     "manufacturing_duration_unit": "hours",
|     "produced_quantity": 100,
|     "raw_materials_warehouse_id": 1,
|     "finished_product_warehouse_id": 2,
|     "labor_cost": 50.00,
|     "operating_cost": 30.00,
|     "overhead_cost": 20.00,
|     "raw_materials": [
|         {
|             "item_id": 20,
|             "unit_id": 3,
|             "warehouse_id": 1,
|             "consumed_quantity": 50.0,
|             "unit_cost": 2.50,
|             "material_type": "raw_material",
|             "is_critical": true
|         }
|     ]
| }
|
| 2. Search Manufactured Formulas:
| GET /api/v1/manufactured-formulas?search=خبز&status=active&date_from=2025-01-01
|
| 3. Field-Based Data Display:
| GET /api/v1/manufactured-formulas/field-based-data?field=status&value=active
|
| 4. Check Materials Availability:
| GET /api/v1/manufactured-formulas/1/check-availability
|
| 5. Execute Manufacturing:
| POST /api/v1/manufactured-formulas/1/manufacture
|
|--------------------------------------------------------------------------
*/
