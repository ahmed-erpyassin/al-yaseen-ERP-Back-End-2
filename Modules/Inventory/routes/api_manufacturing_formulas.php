<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\ManufacturingFormulaController;

/*
|--------------------------------------------------------------------------
| ✅ Manufacturing Formula API Routes
|--------------------------------------------------------------------------
|
| Complete Manufacturing Formula system using enhanced BOM Items table:
| - Item selection and auto-fill functionality
| - Automatic date/time insertion
| - Purchase/selling prices from invoices (3 levels each)
| - Manual cost input (labor, operating, waste)
| - Final cost calculation
| - Complete validation and error handling
|
*/

Route::prefix('v1')->middleware(['api'])->group(function () {
    
    // ✅ Manufacturing Formula Operations
    Route::prefix('manufacturing-formulas')->group(function () {
        
        // ✅ Core Manufacturing Formula Operations
        Route::post('/', [ManufacturingFormulaController::class, 'store']);
        
        // ✅ Item Selection Support
        Route::get('item-numbers', [ManufacturingFormulaController::class, 'getItemNumbers']);
        Route::get('item-details', [ManufacturingFormulaController::class, 'getItemDetails']);
        
        // ✅ Cost Calculation Support
        Route::post('calculate-cost', [ManufacturingFormulaController::class, 'calculateCost']);
    });
});

/*
|--------------------------------------------------------------------------
| ✅ Manufacturing Formula API Endpoints Summary
|--------------------------------------------------------------------------
|
| Core Operations:
| POST   /api/v1/manufacturing-formulas           # Create Manufacturing Formula
|
| Item Selection Support:
| GET    /api/v1/manufacturing-formulas/item-numbers    # Get all Item Numbers (dropdown)
| GET    /api/v1/manufacturing-formulas/item-details    # Get Item details by number/name
|
| Cost Calculation Support:
| POST   /api/v1/manufacturing-formulas/calculate-cost  # Calculate Final Cost
|
|--------------------------------------------------------------------------
| ✅ Usage Examples
|--------------------------------------------------------------------------
|
| 1. Get Item Numbers for Dropdown:
| GET /api/v1/manufacturing-formulas/item-numbers
| Response:
| {
|     "success": true,
|     "data": [
|         {
|             "id": 1,
|             "item_number": "ITM-001",
|             "item_name": "خبز أبيض",
|             "description": "خبز أبيض عالي الجودة"
|         },
|         {
|             "id": 2,
|             "item_number": "ITM-002", 
|             "item_name": "دقيق أبيض",
|             "description": "دقيق أبيض للخبز"
|         }
|     ]
| }
|
| 2. Get Item Details by Item Number:
| GET /api/v1/manufacturing-formulas/item-details?item_number=ITM-001
| Response:
| {
|     "success": true,
|     "data": {
|         "id": 1,
|         "item_number": "ITM-001",
|         "item_name": "خبز أبيض",
|         "description": "خبز أبيض عالي الجودة",
|         "balance": 50.0,
|         "minimum_limit": 10.0,
|         "maximum_limit": 200.0,
|         "minimum_reorder_level": 20.0,
|         "current_selling_price": 3.50,
|         "current_purchase_price": 2.80,
|         "first_purchase_price": 25.50,    // Latest from invoices
|         "second_purchase_price": 24.75,   // Median from invoices
|         "third_purchase_price": 23.00,    // Earliest from invoices
|         "first_selling_price": 35.00,     // Latest from invoices
|         "second_selling_price": 34.25,    // Median from invoices
|         "third_selling_price": 33.50      // Earliest from invoices
|     }
| }
|
| 3. Get Item Details by Item Name:
| GET /api/v1/manufacturing-formulas/item-details?item_name=خبز أبيض
| (Same response as above)
|
| 4. Calculate Final Cost:
| POST /api/v1/manufacturing-formulas/calculate-cost
| {
|     "labor_cost": 10.00,
|     "operating_cost": 5.00,
|     "waste_cost": 2.50,
|     "selected_purchase_price": 25.50
| }
| Response:
| {
|     "success": true,
|     "data": {
|         "labor_cost": 10.00,
|         "operating_cost": 5.00,
|         "waste_cost": 2.50,
|         "selected_purchase_price": 25.50,
|         "final_cost": 43.00,
|         "formula": "Final Cost = Labor Cost + Operating Cost + Waste Cost + Selected Purchase Price"
|     }
| }
|
| 5. Create Manufacturing Formula:
| POST /api/v1/manufacturing-formulas
| {
|     "item_id": 1,
|     "unit_id": 5,
|     "consumed_quantity": 100.0,
|     "produced_quantity": 80.0,
|     "labor_cost": 10.00,
|     "operating_cost": 5.00,
|     "waste_cost": 2.50,
|     "selected_purchase_price_type": "first",
|     "formula_name": "معادلة إنتاج الخبز الأبيض",
|     "formula_description": "معادلة لإنتاج الخبز الأبيض عالي الجودة",
|     "batch_size": 100,
|     "production_time_minutes": 120,
|     "preparation_time_minutes": 30,
|     "production_notes": "يجب مراقبة درجة الحرارة أثناء الخبز",
|     "preparation_notes": "نخل الدقيق قبل الاستخدام",
|     "usage_instructions": "اتبع التسلسل المحدد للمكونات",
|     "tolerance_percentage": 5.0,
|     "quality_requirements": "يجب أن يكون الخبز ذهبي اللون",
|     "requires_inspection": true,
|     "status": "active",
|     "is_active": true
| }
|
| Response:
| {
|     "success": true,
|     "data": {
|         "id": 1,
|         "formula_number": "MF-202501-0001",        // Auto-generated
|         "formula_name": "معادلة إنتاج الخبز الأبيض",
|         "formula_description": "معادلة لإنتاج الخبز الأبيض عالي الجودة",
|         
|         // Item information (auto-filled)
|         "item_id": 1,
|         "item_number": "ITM-001",
|         "item_name": "خبز أبيض",
|         "balance": 50.0,
|         "minimum_limit": 10.0,
|         "maximum_limit": 200.0,
|         "minimum_reorder_level": 20.0,
|         
|         // Unit information (auto-filled)
|         "unit_id": 5,
|         "unit_name": "كيلوجرام",
|         "unit_code": "KG",
|         
|         // Date and Time (auto-filled)
|         "formula_date": "2025-01-15",
|         "formula_time": "14:30:00",
|         "formula_datetime": "2025-01-15T14:30:00.000000Z",
|         
|         // Quantities (manual input)
|         "consumed_quantity": 100.0,
|         "produced_quantity": 80.0,
|         
|         // Purchase prices from invoices (auto-filled)
|         "first_purchase_price": 25.50,     // Latest
|         "second_purchase_price": 24.75,    // Median
|         "third_purchase_price": 23.00,     // Earliest
|         "selected_purchase_price": 25.50,  // Based on selection
|         
|         // Selling prices from invoices (auto-filled)
|         "first_selling_price": 35.00,      // Latest
|         "second_selling_price": 34.25,     // Median
|         "third_selling_price": 33.50,      // Earliest
|         
|         // Costs (manual input)
|         "labor_cost": 10.00,
|         "operating_cost": 5.00,
|         "waste_cost": 2.50,
|         
|         // Final Cost (calculated)
|         "final_cost": 43.00,               // Labor + Operating + Waste + Selected Purchase Price
|         "material_cost": 25.50,
|         "total_production_cost": 43.00,
|         "cost_per_unit": 0.54,              // final_cost / produced_quantity
|         
|         // Production information
|         "batch_size": 100,
|         "production_time_minutes": 120,
|         "preparation_time_minutes": 30,
|         "production_notes": "يجب مراقبة درجة الحرارة أثناء الخبز",
|         "preparation_notes": "نخل الدقيق قبل الاستخدام",
|         "usage_instructions": "اتبع التسلسل المحدد للمكونات",
|         
|         // Quality control
|         "tolerance_percentage": 5.0,
|         "quality_requirements": "يجب أن يكون الخبز ذهبي اللون",
|         "requires_inspection": true,
|         
|         // Status
|         "status": "active",
|         "is_active": true,
|         
|         // Timestamps
|         "created_at": "2025-01-15T14:30:00.000000Z",
|         "updated_at": "2025-01-15T14:30:00.000000Z"
|     },
|     "message": "Manufacturing formula created successfully",
|     "message_ar": "تم إنشاء معادلة التصنيع بنجاح"
| }
|
|--------------------------------------------------------------------------
| ✅ All Requirements Implemented
|--------------------------------------------------------------------------
|
| ✅ Item Selection:
| - API provides all Item Numbers (dropdown simulation)
| - Auto-fill Item Name when Item Number selected
| - Auto-fill Item Number when Item Name selected
|
| ✅ Date & Time:
| - Automatic date insertion on create
| - Automatic time insertion on create
|
| ✅ Quantities:
| - Manual input for consumed_quantity
| - Manual input for produced_quantity
|
| ✅ Purchase Prices:
| - First Purchase Price (latest from invoices)
| - Second Purchase Price (median from invoices)
| - Third Purchase Price (earliest from invoices)
|
| ✅ Selling Prices:
| - First Selling Price (latest from invoices)
| - Second Selling Price (median from invoices)
| - Third Selling Price (earliest from invoices)
|
| ✅ Costs:
| - Manual input for Labor Cost
| - Manual input for Operating Cost
| - Manual input for Waste Cost
|
| ✅ Final Cost Calculation:
| - Formula: Final Cost = Labor Cost + Operating Cost + Waste Cost + Selected Purchase Price
| - User can select which purchase price to use (first, second, or third)
|
| ✅ Store Method:
| - POST /api/v1/manufacturing-formulas endpoint
| - Complete validation of all fields
| - Save data to bom_items table (consolidated Manufacturing Formula table)
| - JSON response with success status and all stored data
|
*/
