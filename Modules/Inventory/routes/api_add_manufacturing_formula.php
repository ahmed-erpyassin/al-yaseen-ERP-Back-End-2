<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\AddManufacturingFormulaController;

/*
|--------------------------------------------------------------------------
| ✅ Add Manufacturing Formula API Routes
|--------------------------------------------------------------------------
|
| Complete Add Manufacturing Formula system with:
| - Automatic item linking based on manufacturing formula number
| - Bidirectional dropdown functionality (item number ↔ item name)
| - Raw materials selection with availability checking
| - Missing materials detection and prevention
| - Calculation engine for inventory deduction/addition
| - Comprehensive validation and error handling
|
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {
    
    // ✅ Add Manufacturing Formula Operations
    Route::prefix('add-manufacturing-formula')->group(function () {
        
        // ✅ Manufacturing Formula Number Support (Dropdown)
        Route::get('/formula-numbers', [AddManufacturingFormulaController::class, 'getManufacturingFormulaNumbers'])
            ->name('add-manufacturing-formula.formula-numbers');
        
        // ✅ Automatic Item Linking (Formula → Item)
        Route::get('/item-by-formula', [AddManufacturingFormulaController::class, 'getItemByFormulaNumber'])
            ->name('add-manufacturing-formula.item-by-formula');
        
        // ✅ Item Number/Name Bidirectional Linking
        Route::get('/item-numbers', [AddManufacturingFormulaController::class, 'getItemNumbers'])
            ->name('add-manufacturing-formula.item-numbers');
        Route::get('/item-names', [AddManufacturingFormulaController::class, 'getItemNames'])
            ->name('add-manufacturing-formula.item-names');
        Route::get('/item-details', [AddManufacturingFormulaController::class, 'getItemDetails'])
            ->name('add-manufacturing-formula.item-details');
        
        // ✅ Warehouse Selection Support
        Route::get('/warehouses', [AddManufacturingFormulaController::class, 'getWarehouses'])
            ->name('add-manufacturing-formula.warehouses');
        
        // ✅ Raw Materials Management
        Route::get('/raw-materials-by-formula', [AddManufacturingFormulaController::class, 'getRawMaterialsByFormula'])
            ->name('add-manufacturing-formula.raw-materials-by-formula');
        Route::get('/raw-material-items', [AddManufacturingFormulaController::class, 'getRawMaterialItems'])
            ->name('add-manufacturing-formula.raw-material-items');
        Route::get('/check-raw-material-availability', [AddManufacturingFormulaController::class, 'checkRawMaterialAvailability'])
            ->name('add-manufacturing-formula.check-raw-material-availability');
        
        // ✅ Store Manufacturing Formula
        Route::post('/store', [AddManufacturingFormulaController::class, 'store'])
            ->name('add-manufacturing-formula.store');
        
        // ✅ Calculate Manufacturing Formula (Deduct Raw Materials & Add Finished Product)
        Route::post('/calculate', [AddManufacturingFormulaController::class, 'calculate'])
            ->name('add-manufacturing-formula.calculate');
    });
});

/*
|--------------------------------------------------------------------------
| ✅ Add Manufacturing Formula API Endpoints Summary
|--------------------------------------------------------------------------
|
| Manufacturing Formula Number Support:
| GET    /api/v1/add-manufacturing-formula/formula-numbers           # Get all formula numbers (dropdown)
|
| Automatic Item Linking (Formula → Item):
| GET    /api/v1/add-manufacturing-formula/item-by-formula          # Get item details by formula ID
|
| Item Number/Name Bidirectional Linking:
| GET    /api/v1/add-manufacturing-formula/item-numbers             # Get item numbers (dropdown)
| GET    /api/v1/add-manufacturing-formula/item-names               # Get item names (dropdown)
| GET    /api/v1/add-manufacturing-formula/item-details             # Get item details by ID/number/name
|
| Warehouse Selection Support:
| GET    /api/v1/add-manufacturing-formula/warehouses               # Get active warehouses (dropdown)
|
| Raw Materials Management:
| GET    /api/v1/add-manufacturing-formula/raw-materials-by-formula # Get raw materials for formula
| GET    /api/v1/add-manufacturing-formula/raw-material-items       # Get raw material items (dropdown)
| GET    /api/v1/add-manufacturing-formula/check-raw-material-availability # Check material availability
|
| Store & Calculate:
| POST   /api/v1/add-manufacturing-formula/store                    # Store new manufacturing formula
| POST   /api/v1/add-manufacturing-formula/calculate                # Calculate (deduct/add inventory)
|
|--------------------------------------------------------------------------
| ✅ Usage Examples
|--------------------------------------------------------------------------
|
| 1. Get Manufacturing Formula Numbers (Dropdown):
| GET /api/v1/add-manufacturing-formula/formula-numbers
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "id": 1,
|             "formula_number": "MF-001",
|             "formula_name": "Bread Manufacturing Formula",
|             "item_id": 5,
|             "display_text": "MF-001 - Bread Manufacturing Formula"
|         }
|     ],
|     "message": "Manufacturing formula numbers retrieved successfully"
| }
|
| 2. Get Item Details by Formula Number (Automatic Item Linking):
| GET /api/v1/add-manufacturing-formula/item-by-formula?formula_id=1
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
|     },
|     "message": "Item details retrieved successfully"
| }
|
| 3. Get Item Numbers (Bidirectional Linking):
| GET /api/v1/add-manufacturing-formula/item-numbers?formula_id=1
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
|     ],
|     "message": "Item numbers retrieved successfully"
| }
|
| 4. Get Item Names (Bidirectional Linking):
| GET /api/v1/add-manufacturing-formula/item-names?formula_id=1
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
|             "display_text": "White Bread - ITM-005"
|         }
|     ],
|     "message": "Item names retrieved successfully"
| }
|
| 5. Get Item Details (Bidirectional Linking):
| GET /api/v1/add-manufacturing-formula/item-details?item_id=5
| GET /api/v1/add-manufacturing-formula/item-details?item_number=ITM-005
| GET /api/v1/add-manufacturing-formula/item-details?item_name=White Bread
| Response:
| {
|     "success": true,
|     "data": {
|         "item_id": 5,
|         "item_number": "ITM-005",
|         "item_name": "White Bread",
|         "description": "High quality white bread",
|         "unit_id": 2,
|         "unit_name": "Piece"
|     },
|     "message": "Item details retrieved successfully"
| }
|
| 6. Get Warehouses (Dropdown):
| GET /api/v1/add-manufacturing-formula/warehouses
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
|     ],
|     "message": "Warehouses retrieved successfully"
| }
|
| 7. Get Raw Materials by Formula (with Availability Check):
| GET /api/v1/add-manufacturing-formula/raw-materials-by-formula?formula_id=1&warehouse_id=1
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
|             "shortage_quantity": 0,
|             "can_select": true,
|             "display_text": "ITM-010 - White Flour"
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
|             "shortage_quantity": 0.5,
|             "can_select": false,
|             "display_text": "ITM-011 - Yeast"
|         }
|     ],
|     "message": "Raw materials retrieved successfully"
| }
|
| 8. Get Raw Material Items (Dropdown with Availability):
| GET /api/v1/add-manufacturing-formula/raw-material-items?formula_id=1&warehouse_id=1
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
|             "available_quantity": 75.0,
|             "unit_cost": 2.50,
|             "is_available": true,
|             "can_select": true,
|             "display_text": "ITM-010 - White Flour (Available: 75.0)",
|             "status_text": "Available"
|         },
|         {
|             "item_id": 11,
|             "item_number": "ITM-011",
|             "item_name": "Yeast",
|             "description": "Active dry yeast",
|             "unit_id": 3,
|             "unit_name": "Kg",
|             "available_quantity": 1.5,
|             "unit_cost": 15.00,
|             "is_available": true,
|             "can_select": true,
|             "display_text": "ITM-011 - Yeast (Available: 1.5)",
|             "status_text": "Available"
|         }
|     ],
|     "message": "Raw material items retrieved successfully"
| }
|
| 9. Check Raw Material Availability:
| GET /api/v1/add-manufacturing-formula/check-raw-material-availability?item_id=11&warehouse_id=1&required_quantity=2.0
| Response:
| {
|     "success": true,
|     "data": {
|         "item_id": 11,
|         "item_number": "ITM-011",
|         "item_name": "Yeast",
|         "required_quantity": 2.0,
|         "available_quantity": 1.5,
|         "is_available": false,
|         "shortage_quantity": 0.5,
|         "can_select": false,
|         "status": "Insufficient",
|         "message": "Insufficient materials - shortage of 0.5 units"
|     },
|     "message": "Availability check completed"
| }
|
| 10. Store Manufacturing Formula:
| POST /api/v1/add-manufacturing-formula/store
| {
|     "item_id": 5,
|     "formula_name": "White Bread Production Formula",
|     "formula_description": "Standard white bread manufacturing process",
|     "manufacturing_duration": "2",
|     "manufacturing_duration_unit": "days",
|     "produced_quantity": 100,
|     "labor_cost": 50.00,
|     "overhead_cost": 25.00,
|     "raw_materials": [
|         {
|             "item_id": 10,
|             "consumed_quantity": 50.0,
|             "unit_cost": 2.50
|         },
|         {
|             "item_id": 11,
|             "consumed_quantity": 2.0,
|             "unit_cost": 15.00
|         }
|     ]
| }
|
| 11. Calculate Manufacturing Formula (Deduct Raw Materials & Add Finished Product):
| POST /api/v1/add-manufacturing-formula/calculate
| {
|     "formula_id": 1,
|     "raw_materials_warehouse_id": 1,
|     "finished_product_warehouse_id": 2,
|     "produced_quantity": 100
| }
| Response:
| {
|     "success": true,
|     "data": {
|         "formula_id": 1,
|         "formula_number": "MF-001",
|         "produced_quantity": 100,
|         "total_raw_material_cost": 155.00,
|         "cost_per_unit": 1.55
|     },
|     "message": "Manufacturing formula calculated successfully",
|     "calculation_summary": {
|         "raw_materials_deducted": true,
|         "finished_products_added": 100,
|         "total_cost": 155.00,
|         "cost_per_unit": 1.55
|     }
| }
|
*/
