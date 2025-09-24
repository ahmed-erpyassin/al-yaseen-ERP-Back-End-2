<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\InventoryController;
use Modules\Inventory\Http\Controllers\Api\WarehouseController;
use Modules\Inventory\Http\Controllers\Api\StockMovementController;
use Modules\Inventory\Http\Controllers\Api\DepartmentWarehouseController;
use Modules\Inventory\Http\Controllers\Api\UnitController;
use Modules\Inventory\Http\Controllers\Api\ItemController;
use Modules\Inventory\Http\Controllers\Api\ItemUnitController;
use Modules\Inventory\Http\Controllers\Api\BomItemController;
use Modules\Inventory\Http\Controllers\Api\BarcodeTypeController;
use Modules\Inventory\Http\Controllers\Api\ItemTypeController;
use Modules\Inventory\Http\Controllers\Api\InventoryMovementController;
use Modules\Inventory\Http\Controllers\Api\ManufacturingFormulaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Inventory Items Routes
    Route::prefix('inventory-items')->group(function () {
        // Main CRUD operations
        Route::get('/survey-all', [InventoryController::class, 'index'])->name('inventory-mgmt.inventory-items.survey-all');
        Route::post('/register-inventory', [InventoryController::class, 'store'])->name('inventory-mgmt.inventory-items.register-inventory');
        Route::get('/examine-inventory/{id}', [InventoryController::class, 'show'])->name('inventory-mgmt.inventory-items.examine-inventory');
        Route::put('/modify-inventory/{id}', [InventoryController::class, 'update'])->name('inventory-mgmt.inventory-items.modify-inventory');
        Route::delete('/remove-inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory-mgmt.inventory-items.remove-inventory');

        // Navigation helpers
        Route::get('/first-inventory', [InventoryController::class, 'first'])->name('inventory-mgmt.inventory-items.first-inventory');
        Route::get('/last-inventory', [InventoryController::class, 'last'])->name('inventory-mgmt.inventory-items.last-inventory');

        // ! Additional inventory management endpoints
        Route::get('/low-stock-items', [InventoryController::class, 'lowStock'])->name('inventory-mgmt.inventory-items.low-stock-items'); // ! Get low stock items
        Route::get('/reorder-required-items', [InventoryController::class, 'reorderItems'])->name('inventory-mgmt.inventory-items.reorder-required-items'); // ! Get items that need reordering
    });

    // ✅ Enhanced Warehouses Routes with Search, Sorting, and Soft Delete
    Route::prefix('warehouses')->group(function () {
        // Main CRUD operations
        Route::get('/scan-all', [WarehouseController::class, 'index'])->name('inventory-mgmt.warehouses.scan-all');
        Route::post('/establish-facility', [WarehouseController::class, 'store'])->name('inventory-mgmt.warehouses.establish-facility');
        Route::get('/inspect-facility/{id}', [WarehouseController::class, 'show'])->name('inventory-mgmt.warehouses.inspect-facility');
        Route::put('/modify-facility/{id}', [WarehouseController::class, 'update'])->name('inventory-mgmt.warehouses.modify-facility');
        Route::delete('/demolish-facility/{id}', [WarehouseController::class, 'destroy'])->name('inventory-mgmt.warehouses.demolish-facility');

        // Enhanced functionality
        Route::get('/configuration-data', [WarehouseController::class, 'getFormData'])->name('inventory-mgmt.warehouses.configuration-data');
        Route::get('/filter-by-criteria', [WarehouseController::class, 'filterByField'])->name('inventory-mgmt.warehouses.filter-by-criteria');
        Route::get('/archived-facilities', [WarehouseController::class, 'trashed'])->name('inventory-mgmt.warehouses.archived-facilities');
        Route::get('/initial-facility', [WarehouseController::class, 'first'])->name('inventory-mgmt.warehouses.initial-facility');
        Route::get('/final-facility', [WarehouseController::class, 'last'])->name('inventory-mgmt.warehouses.final-facility');
        Route::post('/{id}/reactivate-facility', [WarehouseController::class, 'restore'])->name('inventory-mgmt.warehouses.reactivate-facility');
        Route::delete('/{id}/permanently-remove', [WarehouseController::class, 'forceDelete'])->name('inventory-mgmt.warehouses.permanently-remove');
    });

    // Department Warehouses Routes
    Route::prefix('department-warehouses')->group(function () {
        // Main CRUD operations
        Route::get('/enumerate-all', [DepartmentWarehouseController::class, 'index'])->name('inventory-mgmt.dept-warehouses.enumerate-all');
        Route::post('/create-assignment', [DepartmentWarehouseController::class, 'store'])->name('inventory-mgmt.dept-warehouses.create-assignment');
        Route::get('/view-assignment/{id}', [DepartmentWarehouseController::class, 'show'])->name('inventory-mgmt.dept-warehouses.view-assignment');
        Route::put('/update-assignment/{id}', [DepartmentWarehouseController::class, 'update'])->name('inventory-mgmt.dept-warehouses.update-assignment');
        Route::delete('/remove-assignment/{id}', [DepartmentWarehouseController::class, 'destroy'])->name('inventory-mgmt.dept-warehouses.remove-assignment');

        // Navigation helpers
        Route::get('/primary-assignment', [DepartmentWarehouseController::class, 'first'])->name('inventory-mgmt.dept-warehouses.primary-assignment');
        Route::get('/ultimate-assignment', [DepartmentWarehouseController::class, 'last'])->name('inventory-mgmt.dept-warehouses.ultimate-assignment');
    });

    // Stock Movements Routes
    Route::prefix('stock-movements')->group(function () {
        // Main CRUD operations
        Route::get('/track-all', [StockMovementController::class, 'index'])->name('inventory-mgmt.stock-moves.track-all');
        Route::post('/record-movement', [StockMovementController::class, 'store'])->name('inventory-mgmt.stock-moves.record-movement');
        Route::get('/examine-movement/{id}', [StockMovementController::class, 'show'])->name('inventory-mgmt.stock-moves.examine-movement');

        // Specialized views
        Route::get('/movement-summary', [StockMovementController::class, 'stockSummary'])->name('inventory-mgmt.stock-moves.movement-summary');
        Route::get('/by-item/{itemId}', [StockMovementController::class, 'byItem'])->name('inventory-mgmt.stock-moves.by-item');
        Route::get('/by-warehouse/{warehouseId}', [StockMovementController::class, 'byWarehouse'])->name('inventory-mgmt.stock-moves.by-warehouse');
    });

    // Units Routes
    Route::prefix('units')->group(function () {
        // Main CRUD operations
        Route::get('/catalog-all', [UnitController::class, 'index'])->name('inventory-mgmt.units.catalog-all');
        Route::post('/define-unit', [UnitController::class, 'store'])->name('inventory-mgmt.units.define-unit');
        Route::get('/review-unit/{id}', [UnitController::class, 'show'])->name('inventory-mgmt.units.review-unit');
        Route::put('/revise-unit/{id}', [UnitController::class, 'update'])->name('inventory-mgmt.units.revise-unit');
        Route::delete('/eliminate-unit/{id}', [UnitController::class, 'destroy'])->name('inventory-mgmt.units.eliminate-unit');

        // Helper endpoints
        Route::get('/unit-choices', [UnitController::class, 'getUnitOptions'])->name('inventory-mgmt.units.unit-choices');
        Route::get('/comprehensive-choices', [UnitController::class, 'getAllUnitOptions'])->name('inventory-mgmt.units.comprehensive-choices');
        Route::get('/container-choices', [UnitController::class, 'getContainsOptions'])->name('inventory-mgmt.units.container-choices');
        Route::get('/selection-dropdown', [UnitController::class, 'getUnitsForDropdown'])->name('inventory-mgmt.units.selection-dropdown');
        Route::get('/warehouse-selection', [UnitController::class, 'getWarehousesForDropdown'])->name('inventory-mgmt.units.warehouse-selection');
        Route::get('/form-configuration', [UnitController::class, 'getUnitFormData'])->name('inventory-mgmt.units.form-configuration');
        Route::get('/initial-unit', [UnitController::class, 'first'])->name('inventory-mgmt.units.initial-unit');
        Route::get('/final-unit', [UnitController::class, 'last'])->name('inventory-mgmt.units.final-unit');
    });

    // Items Routes
    Route::prefix('items')->group(function () {
        // Main CRUD operations
        Route::get('/inventory-all', [ItemController::class, 'index'])->name('inventory-mgmt.items.inventory-all');
        Route::post('/register-item', [ItemController::class, 'store'])->name('inventory-mgmt.items.register-item');
        Route::get('/inspect-item/{id}', [ItemController::class, 'show'])->name('inventory-mgmt.items.inspect-item');
        Route::put('/modify-item/{id}', [ItemController::class, 'update'])->name('inventory-mgmt.items.modify-item');
        Route::delete('/discard-item/{id}', [ItemController::class, 'destroy'])->name('inventory-mgmt.items.discard-item');

        // Search and filtering
        Route::get('/locate-items', [ItemController::class, 'search'])->name('inventory-mgmt.items.locate-items');
        Route::get('/available-fields', [ItemController::class, 'getAvailableFields'])->name('inventory-mgmt.items.available-fields');
        Route::get('/sortable-columns', [ItemController::class, 'getSortableColumns'])->name('inventory-mgmt.items.sortable-columns');
        Route::get('/item-categories', [ItemController::class, 'getCategories'])->name('inventory-mgmt.items.item-categories');
        Route::get('/storage-locations', [ItemController::class, 'getAvailableWarehouses'])->name('inventory-mgmt.items.storage-locations');

        // Pricing and validation
        Route::get('/pricing-configuration', [ItemController::class, 'getPricingFormData'])->name('inventory-mgmt.items.pricing-configuration');
        Route::post('/validate-pricing-data', [ItemController::class, 'validatePricingData'])->name('inventory-mgmt.items.validate-pricing-data');
        Route::get('/barcode-type-options', [ItemController::class, 'getBarcodeTypes'])->name('inventory-mgmt.items.barcode-type-options');
        Route::get('/item-type-options', [ItemController::class, 'getItemTypes'])->name('inventory-mgmt.items.item-type-options');
        Route::post('/create-custom-type', [ItemController::class, 'createCustomItemType'])->name('inventory-mgmt.items.create-custom-type');
        Route::post('/validate-barcode-format', [ItemController::class, 'validateBarcode'])->name('inventory-mgmt.items.validate-barcode-format');

        // Barcode generation
        Route::post('/{item}/produce-barcode', [ItemController::class, 'generateBarcode'])->name('inventory-mgmt.items.produce-barcode');
        Route::post('/{item}/produce-barcode-svg', [ItemController::class, 'generateBarcodeSVG'])->name('inventory-mgmt.items.produce-barcode-svg');

        // Transaction management
        Route::get('/{item}/item-transactions', [ItemController::class, 'getItemTransactions'])->name('inventory-mgmt.items.item-transactions');
        Route::get('/{item}/export-transactions', [ItemController::class, 'exportItemTransactions'])->name('inventory-mgmt.items.export-transactions');

        // Navigation and filtering
        Route::get('/first-item', [ItemController::class, 'first'])->name('inventory-mgmt.items.first-item');
        Route::get('/last-item', [ItemController::class, 'last'])->name('inventory-mgmt.items.last-item');
        Route::get('/parent-items', [ItemController::class, 'parents'])->name('inventory-mgmt.items.parent-items');
        Route::get('/by-type/{type}', [ItemController::class, 'byType'])->name('inventory-mgmt.items.by-type');
        Route::get('/deleted-items', [ItemController::class, 'trashed'])->name('inventory-mgmt.items.deleted-items');
        Route::get('/preview-item/{id}', [ItemController::class, 'preview'])->name('inventory-mgmt.items.preview-item');

        // Soft delete management
        Route::post('/{id}/restore-item', [ItemController::class, 'restore'])->name('inventory-mgmt.items.restore-item');
        Route::delete('/{id}/permanently-delete', [ItemController::class, 'forceDelete'])->name('inventory-mgmt.items.permanently-delete');
    });

    // Item Units Routes
    Route::prefix('item-units')->group(function () {
        // Main CRUD operations
        Route::get('/list-all', [ItemUnitController::class, 'index'])->name('inventory-mgmt.item-units.list-all');
        Route::post('/establish-unit', [ItemUnitController::class, 'store'])->name('inventory-mgmt.item-units.establish-unit');
        Route::get('/examine-unit/{id}', [ItemUnitController::class, 'show'])->name('inventory-mgmt.item-units.examine-unit');
        Route::put('/adjust-unit/{id}', [ItemUnitController::class, 'update'])->name('inventory-mgmt.item-units.adjust-unit');
        Route::delete('/remove-unit/{id}', [ItemUnitController::class, 'destroy'])->name('inventory-mgmt.item-units.remove-unit');

        // Configuration and options
        Route::get('/type-selections', [ItemUnitController::class, 'getUnitTypeOptions'])->name('inventory-mgmt.item-units.type-selections');
        Route::get('/container-selections', [ItemUnitController::class, 'getItemUnitContainsOptions'])->name('inventory-mgmt.item-units.container-selections');
        Route::get('/form-setup', [ItemUnitController::class, 'getFormData'])->name('inventory-mgmt.item-units.form-setup');
        Route::post('/compute-conversion', [ItemUnitController::class, 'calculateConversion'])->name('inventory-mgmt.item-units.compute-conversion');

        // Item-specific queries
        Route::get('/by-item/{itemId}', [ItemUnitController::class, 'byItem'])->name('inventory-mgmt.item-units.by-item');
        Route::get('/by-item-type/{itemId}/{type}', [ItemUnitController::class, 'getByType'])->name('inventory-mgmt.item-units.by-item-type');
        Route::get('/comprehensive-data/{itemId}', [ItemUnitController::class, 'getComprehensiveData'])->name('inventory-mgmt.item-units.comprehensive-data');

        // Unit management
        Route::put('/{id}/designate-default', [ItemUnitController::class, 'setDefault'])->name('inventory-mgmt.item-units.designate-default');
    });

    // Barcode Types Routes
    Route::prefix('barcode-types')->group(function () {
        // Main operations
        Route::get('/enumerate-types', [BarcodeTypeController::class, 'index'])->name('inventory-mgmt.barcode-types.enumerate-types');
        Route::get('/view-type/{id}', [BarcodeTypeController::class, 'show'])->name('inventory-mgmt.barcode-types.view-type');

        // Configuration and options
        Route::get('/type-options', [BarcodeTypeController::class, 'getOptions'])->name('inventory-mgmt.barcode-types.type-options');
        Route::get('/supported-formats', [BarcodeTypeController::class, 'getSupportedTypes'])->name('inventory-mgmt.barcode-types.supported-formats');

        // Barcode operations
        Route::post('/verify-barcode', [BarcodeTypeController::class, 'validateBarcode'])->name('inventory-mgmt.barcode-types.verify-barcode');
        Route::post('/create-barcode', [BarcodeTypeController::class, 'generateBarcode'])->name('inventory-mgmt.barcode-types.create-barcode');
        Route::post('/create-barcode-svg', [BarcodeTypeController::class, 'generateBarcodeSVG'])->name('inventory-mgmt.barcode-types.create-barcode-svg');
    });

    // Item Types Routes
    Route::prefix('item-types')->group(function () {
        // Main CRUD operations
        Route::get('/catalog-types', [ItemTypeController::class, 'index'])->name('inventory-mgmt.item-types.catalog-types');
        Route::post('/establish-type', [ItemTypeController::class, 'store'])->name('inventory-mgmt.item-types.establish-type');
        Route::get('/examine-type/{id}', [ItemTypeController::class, 'show'])->name('inventory-mgmt.item-types.examine-type');
        Route::put('/modify-type/{id}', [ItemTypeController::class, 'update'])->name('inventory-mgmt.item-types.modify-type');
        Route::delete('/eliminate-type/{id}', [ItemTypeController::class, 'destroy'])->name('inventory-mgmt.item-types.eliminate-type');

        // Helper endpoints
        Route::get('/type-selections', [ItemTypeController::class, 'getOptions'])->name('inventory-mgmt.item-types.type-selections');
    });

    // BOM Items Routes
    Route::prefix('bom-items')->group(function () {
        // Main CRUD operations
        Route::get('/list-components', [BomItemController::class, 'index'])->name('inventory-mgmt.bom-items.list-components');
        Route::post('/create-component', [BomItemController::class, 'store'])->name('inventory-mgmt.bom-items.create-component');
        Route::get('/view-component/{id}', [BomItemController::class, 'show'])->name('inventory-mgmt.bom-items.view-component');
        Route::put('/update-component/{id}', [BomItemController::class, 'update'])->name('inventory-mgmt.bom-items.update-component');
        Route::delete('/delete-component/{id}', [BomItemController::class, 'destroy'])->name('inventory-mgmt.bom-items.delete-component');

        // Specialized queries
        Route::get('/by-item/{itemId}', [BomItemController::class, 'byItem'])->name('inventory-mgmt.bom-items.by-item');
        Route::get('/by-component/{componentId}', [BomItemController::class, 'byComponent'])->name('inventory-mgmt.bom-items.by-component');
        Route::post('/compute-requirements', [BomItemController::class, 'calculateRequirements'])->name('inventory-mgmt.bom-items.compute-requirements');

        // ! Additional BOM management endpoints
        Route::get('/filter-by-criteria', [BomItemController::class, 'filterByField'])->name('inventory-mgmt.bom-items.filter-by-criteria'); // ! Filter BOM items by field value
        Route::get('/first-component', [BomItemController::class, 'first'])->name('inventory-mgmt.bom-items.first-component'); // ! Get first BOM component
        Route::get('/last-component', [BomItemController::class, 'last'])->name('inventory-mgmt.bom-items.last-component'); // ! Get last BOM component
    });

    // ✅ Complete Inventory Movement Routes (Add Warehouse Movement System)
    Route::prefix('inventory-movements')->group(function () {
        // Main CRUD operations
        Route::get('/monitor-all', [InventoryMovementController::class, 'index'])->name('inventory-mgmt.inv-movements.monitor-all');
        Route::post('/initiate-movement', [InventoryMovementController::class, 'store'])->name('inventory-mgmt.inv-movements.initiate-movement');
        Route::get('/review-movement/{id}', [InventoryMovementController::class, 'show'])->name('inventory-mgmt.inv-movements.review-movement');
        Route::put('/adjust-movement/{id}', [InventoryMovementController::class, 'update'])->name('inventory-mgmt.inv-movements.adjust-movement');
        Route::delete('/cancel-movement/{id}', [InventoryMovementController::class, 'destroy'])->name('inventory-mgmt.inv-movements.cancel-movement');

        // Configuration and filtering
        Route::get('/setup-data', [InventoryMovementController::class, 'getFormData'])->name('inventory-mgmt.inv-movements.setup-data');
        Route::get('/filter-by-criteria', [InventoryMovementController::class, 'filterByField'])->name('inventory-mgmt.inv-movements.filter-by-criteria');
        Route::get('/archived-movements', [InventoryMovementController::class, 'trashed'])->name('inventory-mgmt.inv-movements.archived-movements');

        // Navigation helpers
        Route::get('/initial-movement', [InventoryMovementController::class, 'first'])->name('inventory-mgmt.inv-movements.initial-movement');
        Route::get('/final-movement', [InventoryMovementController::class, 'last'])->name('inventory-mgmt.inv-movements.final-movement');
        Route::get('/next-sequence-number', [InventoryMovementController::class, 'getNextMovementNumber'])->name('inventory-mgmt.inv-movements.next-sequence-number');

        // Movement operations
        Route::get('/{id}/movement-details', [InventoryMovementController::class, 'getMovementData'])->name('inventory-mgmt.inv-movements.movement-details');
        Route::post('/{id}/validate-movement', [InventoryMovementController::class, 'confirm'])->name('inventory-mgmt.inv-movements.validate-movement');
        Route::post('/{id}/replicate-movement', [InventoryMovementController::class, 'duplicate'])->name('inventory-mgmt.inv-movements.replicate-movement');
        Route::post('/{id}/reactivate-movement', [InventoryMovementController::class, 'restore'])->name('inventory-mgmt.inv-movements.reactivate-movement');
        Route::delete('/{id}/permanently-remove', [InventoryMovementController::class, 'forceDelete'])->name('inventory-mgmt.inv-movements.permanently-remove');
    });

    // Manufacturing Formula Routes
    Route::prefix('manufacturing-formulas')->group(function () {
        // Main CRUD operations
        Route::get('/catalog-all', [ManufacturingFormulaController::class, 'index'])->name('inventory-mgmt.manufacturing-formulas.catalog-all');
        Route::post('/establish-formula', [ManufacturingFormulaController::class, 'store'])->name('inventory-mgmt.manufacturing-formulas.establish-formula');
        Route::get('/examine-formula/{id}', [ManufacturingFormulaController::class, 'show'])->name('inventory-mgmt.manufacturing-formulas.examine-formula');
        Route::put('/modify-formula/{id}', [ManufacturingFormulaController::class, 'update'])->name('inventory-mgmt.manufacturing-formulas.modify-formula');
        Route::delete('/remove-formula/{id}', [ManufacturingFormulaController::class, 'destroy'])->name('inventory-mgmt.manufacturing-formulas.remove-formula');

        // Data retrieval and configuration
        Route::get('/item-numbers', [ManufacturingFormulaController::class, 'getItemNumbers'])->name('inventory-mgmt.manufacturing-formulas.item-numbers');
        Route::get('/item-details', [ManufacturingFormulaController::class, 'getItemDetails'])->name('inventory-mgmt.manufacturing-formulas.item-details');
        Route::post('/calculate-cost', [ManufacturingFormulaController::class, 'calculateCost'])->name('inventory-mgmt.manufacturing-formulas.calculate-cost');
        Route::get('/available-fields', [ManufacturingFormulaController::class, 'getAvailableFields'])->name('inventory-mgmt.manufacturing-formulas.available-fields');
        Route::get('/field-values', [ManufacturingFormulaController::class, 'getFieldValues'])->name('inventory-mgmt.manufacturing-formulas.field-values');
        Route::get('/selectable-fields', [ManufacturingFormulaController::class, 'getSelectableFields'])->name('inventory-mgmt.manufacturing-formulas.selectable-fields');
        Route::get('/field-based-data', [ManufacturingFormulaController::class, 'getFieldBasedData'])->name('inventory-mgmt.manufacturing-formulas.field-based-data');

        // Formula-specific operations
        Route::get('/formula-numbers', [ManufacturingFormulaController::class, 'getManufacturingFormulaNumbers'])->name('inventory-mgmt.manufacturing-formulas.formula-numbers');
        Route::get('/item-by-formula-number', [ManufacturingFormulaController::class, 'getItemByFormulaNumber'])->name('inventory-mgmt.manufacturing-formulas.item-by-formula-number');
        Route::get('/warehouses', [ManufacturingFormulaController::class, 'getWarehouses'])->name('inventory-mgmt.manufacturing-formulas.warehouses');

        // Price management
        Route::put('/update-prices-from-suppliers/{id}', [ManufacturingFormulaController::class, 'updatePricesFromSuppliers'])->name('inventory-mgmt.manufacturing-formulas.update-prices-from-suppliers');
        Route::put('/update-all-prices-from-suppliers', [ManufacturingFormulaController::class, 'updateAllPricesFromSuppliers'])->name('inventory-mgmt.manufacturing-formulas.update-all-prices-from-suppliers');

        // Soft delete management
        Route::post('/{id}/restore-formula', [ManufacturingFormulaController::class, 'restore'])->name('inventory-mgmt.manufacturing-formulas.restore-formula');
        Route::delete('/{id}/permanently-remove', [ManufacturingFormulaController::class, 'forceDelete'])->name('inventory-mgmt.manufacturing-formulas.permanently-remove');
        Route::get('/deleted-formulas', [ManufacturingFormulaController::class, 'trashed'])->name('inventory-mgmt.manufacturing-formulas.deleted-formulas');
    });

    // Legacy route for backward compatibility (commented out to avoid conflicts)
    // Route::apiResource('inventories', InventoryController::class)->names('inventory-mgmt.legacy-inventories');
});
