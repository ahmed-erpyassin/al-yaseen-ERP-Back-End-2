<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\ManufacturingProcessController;

/*
|--------------------------------------------------------------------------
| ✅ Manufacturing Process API Routes
|--------------------------------------------------------------------------
|
| Complete Manufacturing Process system with:
| - Automatic item linking based on manufacturing formula
| - Bidirectional dropdown functionality
| - Warehouse management (raw materials & finished products)
| - Raw materials availability checking
| - Calculation engine for inventory deduction/addition
| - Comprehensive validation and error handling
|
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {
    
    // ✅ Manufacturing Process Operations
    Route::prefix('manufacturing-processes')->group(function () {
        
        // ✅ Core CRUD Operations
        Route::get('/', [ManufacturingProcessController::class, 'index'])->name('manufacturing-processes.index');
        Route::post('/', [ManufacturingProcessController::class, 'store'])->name('manufacturing-processes.store');
        Route::get('/{id}', [ManufacturingProcessController::class, 'show'])->name('manufacturing-processes.show');
        Route::put('/{id}', [ManufacturingProcessController::class, 'update'])->name('manufacturing-processes.update');
        Route::delete('/{id}', [ManufacturingProcessController::class, 'destroy'])->name('manufacturing-processes.destroy');
        
        // ✅ Manufacturing Formula Support (Automatic Item Linking)
        Route::get('/formulas/list', [ManufacturingProcessController::class, 'getManufacturingFormulas'])->name('manufacturing-processes.formulas');
        Route::get('/formulas/item-details', [ManufacturingProcessController::class, 'getItemByFormula'])->name('manufacturing-processes.formula-item');
        
        // ✅ Item Selection Support (Bidirectional Linking)
        Route::get('/items/list', [ManufacturingProcessController::class, 'getItems'])->name('manufacturing-processes.items');
        
        // ✅ Warehouse Management Support
        Route::get('/warehouses/list', [ManufacturingProcessController::class, 'getWarehouses'])->name('manufacturing-processes.warehouses');
        
        // ✅ Raw Materials Management
        Route::get('/raw-materials/by-formula', [ManufacturingProcessController::class, 'getRawMaterialsByFormula'])->name('manufacturing-processes.raw-materials');
        
        // ✅ Calculation Engine
        Route::post('/{id}/calculate', [ManufacturingProcessController::class, 'calculate'])->name('manufacturing-processes.calculate');
        Route::get('/{id}/check-availability', [ManufacturingProcessController::class, 'checkAvailability'])->name('manufacturing-processes.check-availability');
    });
});

/*
|--------------------------------------------------------------------------
| ✅ Manufacturing Process API Endpoints Summary
|--------------------------------------------------------------------------
|
| Core CRUD Operations:
| GET    /api/v1/manufacturing-processes              # List all manufacturing processes
| POST   /api/v1/manufacturing-processes              # Create new manufacturing process
| GET    /api/v1/manufacturing-processes/{id}         # Show specific manufacturing process
| PUT    /api/v1/manufacturing-processes/{id}         # Update manufacturing process
| DELETE /api/v1/manufacturing-processes/{id}         # Delete manufacturing process
|
| Manufacturing Formula Support (Automatic Item Linking):
| GET    /api/v1/manufacturing-processes/formulas/list        # Get all manufacturing formulas (dropdown)
| GET    /api/v1/manufacturing-processes/formulas/item-details # Get item details by formula ID
|
| Item Selection Support (Bidirectional Linking):
| GET    /api/v1/manufacturing-processes/items/list           # Get items (with formula filtering)
|
| Warehouse Management Support:
| GET    /api/v1/manufacturing-processes/warehouses/list      # Get active warehouses (dropdown)
|
| Raw Materials Management:
| GET    /api/v1/manufacturing-processes/raw-materials/by-formula # Get raw materials for formula
|
| Calculation Engine:
| POST   /api/v1/manufacturing-processes/{id}/calculate       # Calculate process (deduct/add inventory)
| GET    /api/v1/manufacturing-processes/{id}/check-availability # Check raw material availability
|
|--------------------------------------------------------------------------
| ✅ Usage Examples
|--------------------------------------------------------------------------
|
| 1. Get Manufacturing Formulas (Dropdown):
| GET /api/v1/manufacturing-processes/formulas/list
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "id": 1,
|             "formula_number": "MF-001",
|             "formula_name": "Bread Manufacturing Formula",
|             "item_id": 5,
|             "item_number": "ITM-005",
|             "item_name": "White Bread",
|             "produced_quantity": 100,
|             "display_text": "MF-001 - Bread Manufacturing Formula"
|         }
|     ]
| }
|
| 2. Get Item Details by Formula (Automatic Item Linking):
| GET /api/v1/manufacturing-processes/formulas/item-details?formula_id=1
| Response:
| {
|     "success": true,
|     "data": {
|         "formula": {
|             "id": 1,
|             "formula_number": "MF-001",
|             "formula_name": "Bread Manufacturing Formula",
|             "produced_quantity": 100
|         },
|         "item": {
|             "item_id": 5,
|             "item_number": "ITM-005",
|             "item_name": "White Bread",
|             "description": "High quality white bread",
|             "unit_id": 2,
|             "unit_name": "Piece"
|         }
|     }
| }
|
| 3. Get Items (Bidirectional Linking):
| GET /api/v1/manufacturing-processes/items/list?formula_id=1
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "id": 5,
|             "item_number": "ITM-005",
|             "item_name": "White Bread",
|             "description": "High quality white bread",
|             "unit_id": 2,
|             "unit_name": "Piece",
|             "display_text": "ITM-005 - White Bread"
|         }
|     ]
| }
|
| 4. Get Warehouses (Dropdown):
| GET /api/v1/manufacturing-processes/warehouses/list
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "id": 1,
|             "warehouse_number": "WH-001",
|             "warehouse_name": "Main Raw Materials Warehouse",
|             "address": "Industrial Area",
|             "display_text": "WH-001 - Main Raw Materials Warehouse"
|         },
|         {
|             "id": 2,
|             "warehouse_number": "WH-002",
|             "warehouse_name": "Finished Products Warehouse",
|             "address": "Distribution Center",
|             "display_text": "WH-002 - Finished Products Warehouse"
|         }
|     ]
| }
|
| 5. Get Raw Materials by Formula (with Availability Check):
| GET /api/v1/manufacturing-processes/raw-materials/by-formula?formula_id=1&warehouse_id=1
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "item_id": 10,
|             "item_number": "ITM-010",
|             "item_name": "White Flour",
|             "description": "High quality white flour",
|             "unit_id": 3,
|             "unit_name": "Kg",
|             "consumed_quantity": 50.0,
|             "available_quantity": 75.0,
|             "unit_cost": 2.50,
|             "is_available": true,
|             "shortage_quantity": 0
|         },
|         {
|             "item_id": 11,
|             "item_number": "ITM-011",
|             "item_name": "Yeast",
|             "description": "Active dry yeast",
|             "unit_id": 3,
|             "unit_name": "Kg",
|             "consumed_quantity": 2.0,
|             "available_quantity": 1.5,
|             "unit_cost": 15.00,
|             "is_available": false,
|             "shortage_quantity": 0.5
|         }
|     ]
| }
|
| 6. Check Raw Material Availability:
| GET /api/v1/manufacturing-processes/1/check-availability
| Response:
| {
|     "success": true,
|     "data": {
|         "all_materials_available": false,
|         "can_proceed": false,
|         "materials_check": [
|             {
|                 "item_id": 10,
|                 "item_number": "ITM-010",
|                 "item_name": "White Flour",
|                 "required_quantity": 50.0,
|                 "available_quantity": 75.0,
|                 "is_available": true,
|                 "shortage_quantity": 0,
|                 "unit_name": "Kg"
|             },
|             {
|                 "item_id": 11,
|                 "item_number": "ITM-011",
|                 "item_name": "Yeast",
|                 "required_quantity": 2.0,
|                 "available_quantity": 1.5,
|                 "is_available": false,
|                 "shortage_quantity": 0.5,
|                 "unit_name": "Kg"
|             }
|         ],
|         "total_materials": 2,
|         "available_materials": 1,
|         "missing_materials": 1
|     },
|     "message": "Some materials are insufficient"
| }
|
| 7. Create Manufacturing Process:
| POST /api/v1/manufacturing-processes
| {
|     "manufacturing_formula_id": 1,
|     "item_id": 5,
|     "manufacturing_duration": "2",
|     "manufacturing_duration_unit": "days",
|     "produced_quantity": 100,
|     "raw_materials_warehouse_id": 1,
|     "finished_product_warehouse_id": 2,
|     "labor_cost": 50.00,
|     "overhead_cost": 25.00,
|     "notes": "Standard bread production batch",
|     "batch_number": "BATCH-001",
|     "raw_materials": [
|         {
|             "item_id": 10,
|             "consumed_quantity": 50.0,
|             "unit_cost": 2.50,
|             "unit_id": 3
|         },
|         {
|             "item_id": 11,
|             "consumed_quantity": 2.0,
|             "unit_cost": 15.00,
|             "unit_id": 3
|         }
|     ]
| }
|
| 8. Calculate Manufacturing Process (Deduct Raw Materials & Add Finished Product):
| POST /api/v1/manufacturing-processes/1/calculate
| Response:
| {
|     "success": true,
|     "data": {
|         // Full manufacturing process resource data
|     },
|     "message": "Manufacturing process calculated successfully",
|     "calculation_summary": {
|         "raw_materials_deducted": 2,
|         "total_raw_material_cost": 155.00,
|         "finished_products_added": 100,
|         "total_manufacturing_cost": 230.00,
|         "cost_per_unit": 2.30
|     }
| }
|
*/
