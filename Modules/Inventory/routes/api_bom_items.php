<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\BomItemController;

/*
|--------------------------------------------------------------------------
| ✅ Enhanced BOM Items API Routes
|--------------------------------------------------------------------------
|
| Complete BOM Items system with all Manufacturing Formula fields:
| - Item Number, Name (from Items table)
| - Component Item Number, Name, Description (from Items table)
| - Unit Name, Code (from Units table)  
| - Balance, Limits, Reorder Level (from Items table)
| - Selling Price (from Sales table)
| - Purchase Price (from Purchases table)
| - Date/Time (automatic on insert)
| - Consumed/Produced Quantities
| - Historical Prices (from invoices)
| - Labor/Operating/Waste Costs
| - Final Cost calculation
| - Component Type, Critical flags
| - Quality Control, Supplier Information
|
*/

Route::prefix('v1')->middleware(['api'])->group(function () {
    
    // ✅ Core BOM Items Operations
    Route::apiResource('bom-items', BomItemController::class);
    
    // ✅ Enhanced Features
    Route::prefix('bom-items')->group(function () {
        
        // ✅ Search and Filter Operations
        Route::get('filter-by-field', [BomItemController::class, 'filterByField']);
        Route::get('search', [BomItemController::class, 'index']); // Uses same index with search
        
        // ✅ First/Last Sorting Operations
        Route::get('first', [BomItemController::class, 'first']);
        Route::get('last', [BomItemController::class, 'last']);
        
        // ✅ BOM-specific Operations
        Route::get('by-item/{itemId}', [BomItemController::class, 'byItem']);
        Route::get('by-component/{componentId}', [BomItemController::class, 'byComponent']);
        Route::post('calculate-requirements', [BomItemController::class, 'calculateRequirements']);
        
        // ✅ Utility Operations
        Route::get('next-formula-number', [BomItemController::class, 'getNextFormulaNumber']);
    });
});

/*
|--------------------------------------------------------------------------
| ✅ Enhanced BOM Items API Endpoints Summary
|--------------------------------------------------------------------------
|
| Core Operations:
| POST   /api/v1/bom-items                    # Create BOM Item
| GET    /api/v1/bom-items                    # List/Search BOM Items
| GET    /api/v1/bom-items/{id}               # Show BOM Item (All Data)
| PUT    /api/v1/bom-items/{id}               # Update BOM Item
| DELETE /api/v1/bom-items/{id}               # Delete BOM Item
|
| Enhanced Features:
| GET    /api/v1/bom-items/filter-by-field    # Selection-based Display
| GET    /api/v1/bom-items/first              # First BOM Item (Sorting)
| GET    /api/v1/bom-items/last               # Last BOM Item (Sorting)
| GET    /api/v1/bom-items/by-item/{itemId}   # BOM for specific item
| GET    /api/v1/bom-items/by-component/{componentId} # Items using component
| POST   /api/v1/bom-items/calculate-requirements # Calculate material requirements
| GET    /api/v1/bom-items/next-formula-number # Get Next Formula Number
|
|--------------------------------------------------------------------------
| ✅ Usage Examples
|--------------------------------------------------------------------------
|
| 1. Create Enhanced BOM Item:
| POST /api/v1/bom-items
| {
|     "item_id": 10,
|     "component_id": 20,
|     "unit_id": 5,
|     "quantity": 2.5,
|     "required_quantity": 2.5,
|     "unit_cost": 15.00,
|     "component_type": "raw_material",
|     "is_critical": true,
|     "sequence_order": 1,
|     "formula_name": "معادلة إنتاج الخبز",
|     "preparation_notes": "يجب نخل الدقيق قبل الاستخدام",
|     "usage_instructions": "إضافة الدقيق تدريجياً مع الخلط",
|     "tolerance_percentage": 5.0,
|     "requires_inspection": true
| }
|
| 2. Search BOM Items:
| GET /api/v1/bom-items?search=دقيق&component_type=raw_material&critical_only=true
|
| 3. Filter by Field:
| GET /api/v1/bom-items/filter-by-field?field=component_type&value=raw_material
|
| 4. Get BOM for Item:
| GET /api/v1/bom-items/by-item/10
|
| 5. Calculate Requirements:
| POST /api/v1/bom-items/calculate-requirements
| {
|     "item_id": 10,
|     "production_quantity": 100
| }
|
|--------------------------------------------------------------------------
| ✅ All Required Fields Now Available in BOM Items
|--------------------------------------------------------------------------
|
| From Items Table:
| ✅ Item Number, Item Name, Component Item Number, Name, Description
| ✅ Balance, Minimum Limit, Maximum Limit, Minimum Reorder Level
|
| From Units Table:
| ✅ Unit Name, Unit Code
|
| From Sales Table:
| ✅ Selling Price, First/Second/Third Selling Price (from invoices)
|
| From Purchases Table:
| ✅ Purchase Price, First/Second/Third Purchase Price (from invoices)
|
| Automatic Fields:
| ✅ Date (added automatically on insert)
| ✅ Time (added automatically on insert)
|
| Manual Fields:
| ✅ Consumed Quantity, Produced Quantity
| ✅ Labor Cost, Operating Cost, Waste Cost, Final Cost
|
| Enhanced Features:
| ✅ Component Type (raw_material, semi_finished, packaging, consumable)
| ✅ Critical/Optional flags
| ✅ Quality Control (tolerance, inspection requirements)
| ✅ Supplier Information (preferred supplier, item codes, prices)
| ✅ Production Instructions (preparation notes, usage instructions)
| ✅ Sequence Order for production process
| ✅ Status and Effective Date management
|
| System Features:
| ✅ Search, Filter, Sort, Enhanced CRUD operations
| ✅ Cost calculations and material requirements
| ✅ API-only implementation (no view pages)
|
*/
