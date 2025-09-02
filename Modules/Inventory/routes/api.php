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

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Inventory Items Routes
    Route::prefix('inventory-items')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.items.index');
        Route::post('/', [InventoryController::class, 'store'])->name('inventory.items.store');
        Route::get('/first', [InventoryController::class, 'first'])->name('inventory.items.first');
        Route::get('/last', [InventoryController::class, 'last'])->name('inventory.items.last');
        Route::get('/{id}', [InventoryController::class, 'show'])->name('inventory.items.show');
        Route::put('/{id}', [InventoryController::class, 'update'])->name('inventory.items.update');
        Route::delete('/{id}', [InventoryController::class, 'destroy'])->name('inventory.items.destroy');
    });

    // Warehouses Routes
    Route::prefix('warehouses')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('inventory.warehouses.index');
        Route::post('/', [WarehouseController::class, 'store'])->name('inventory.warehouses.store');
        Route::get('/first', [WarehouseController::class, 'first'])->name('inventory.warehouses.first');
        Route::get('/last', [WarehouseController::class, 'last'])->name('inventory.warehouses.last');
        Route::get('/{id}', [WarehouseController::class, 'show'])->name('inventory.warehouses.show');
        Route::put('/{id}', [WarehouseController::class, 'update'])->name('inventory.warehouses.update');
        Route::delete('/{id}', [WarehouseController::class, 'destroy'])->name('inventory.warehouses.destroy');
    });

    // Department Warehouses Routes
    Route::prefix('department-warehouses')->group(function () {
        Route::get('/', [DepartmentWarehouseController::class, 'index'])->name('inventory.departments.index');
        Route::post('/', [DepartmentWarehouseController::class, 'store'])->name('inventory.departments.store');
        Route::get('/first', [DepartmentWarehouseController::class, 'first'])->name('inventory.departments.first');
        Route::get('/last', [DepartmentWarehouseController::class, 'last'])->name('inventory.departments.last');
        Route::get('/{id}', [DepartmentWarehouseController::class, 'show'])->name('inventory.departments.show');
        Route::put('/{id}', [DepartmentWarehouseController::class, 'update'])->name('inventory.departments.update');
        Route::delete('/{id}', [DepartmentWarehouseController::class, 'destroy'])->name('inventory.departments.destroy');
    });

    // Stock Movements Routes
    Route::prefix('stock-movements')->group(function () {
        Route::get('/', [StockMovementController::class, 'index'])->name('inventory.movements.index');
        Route::post('/', [StockMovementController::class, 'store'])->name('inventory.movements.store');
        Route::get('/summary', [StockMovementController::class, 'stockSummary'])->name('inventory.movements.summary');
        Route::get('/item/{itemId}', [StockMovementController::class, 'byItem'])->name('inventory.movements.by-item');
        Route::get('/warehouse/{warehouseId}', [StockMovementController::class, 'byWarehouse'])->name('inventory.movements.by-warehouse');
        Route::get('/{id}', [StockMovementController::class, 'show'])->name('inventory.movements.show');
    });

    // Units Routes
    Route::prefix('units')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('inventory.units.index');
        Route::post('/', [UnitController::class, 'store'])->name('inventory.units.store');
        Route::get('/options', [UnitController::class, 'getUnitOptions'])->name('inventory.units.options');
        Route::get('/all-options', [UnitController::class, 'getAllUnitOptions'])->name('inventory.units.all-options');
        Route::get('/contains-options', [UnitController::class, 'getContainsOptions'])->name('inventory.units.contains-options');
        Route::get('/dropdown', [UnitController::class, 'getUnitsForDropdown'])->name('inventory.units.dropdown');
        Route::get('/warehouses-dropdown', [UnitController::class, 'getWarehousesForDropdown'])->name('inventory.units.warehouses-dropdown');
        Route::get('/form-data', [UnitController::class, 'getUnitFormData'])->name('inventory.units.form-data');
        Route::get('/first', [UnitController::class, 'first'])->name('inventory.units.first');
        Route::get('/last', [UnitController::class, 'last'])->name('inventory.units.last');
        Route::get('/{id}', [UnitController::class, 'show'])->name('inventory.units.show');
        Route::put('/{id}', [UnitController::class, 'update'])->name('inventory.units.update');
        Route::delete('/{id}', [UnitController::class, 'destroy'])->name('inventory.units.destroy');
    });

    // Items Routes
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('inventory.items.index');
        Route::post('/', [ItemController::class, 'store'])->name('inventory.items.store');
        Route::get('/search', [ItemController::class, 'search'])->name('inventory.items.search');
        Route::get('/fields', [ItemController::class, 'getAvailableFields'])->name('inventory.items.fields');
        Route::get('/columns', [ItemController::class, 'getSortableColumns'])->name('inventory.items.columns');
        Route::get('/categories', [ItemController::class, 'getCategories'])->name('inventory.items.categories');
        Route::get('/warehouses', [ItemController::class, 'getAvailableWarehouses'])->name('inventory.items.warehouses');
        Route::get('/pricing-form-data', [ItemController::class, 'getPricingFormData'])->name('inventory.items.pricing-form-data');
        Route::post('/validate-pricing', [ItemController::class, 'validatePricingData'])->name('inventory.items.validate-pricing');
        Route::get('/barcode-types', [ItemController::class, 'getBarcodeTypes'])->name('inventory.items.barcode-types');
        Route::get('/item-types', [ItemController::class, 'getItemTypes'])->name('inventory.items.item-types');
        Route::post('/custom-item-type', [ItemController::class, 'createCustomItemType'])->name('inventory.items.custom-item-type');
        Route::post('/validate-barcode', [ItemController::class, 'validateBarcode'])->name('inventory.items.validate-barcode');
        Route::post('/{item}/generate-barcode', [ItemController::class, 'generateBarcode'])->name('inventory.items.generate-barcode');
        Route::post('/{item}/generate-barcode-svg', [ItemController::class, 'generateBarcodeSVG'])->name('inventory.items.generate-barcode-svg');
        Route::get('/{item}/transactions', [ItemController::class, 'getItemTransactions'])->name('inventory.items.transactions');
        Route::get('/{item}/transactions/export', [ItemController::class, 'exportItemTransactions'])->name('inventory.items.transactions.export');
        Route::get('/first', [ItemController::class, 'first'])->name('inventory.items.first');
        Route::get('/last', [ItemController::class, 'last'])->name('inventory.items.last');
        Route::get('/parents', [ItemController::class, 'parents'])->name('inventory.items.parents');
        Route::get('/type/{type}', [ItemController::class, 'byType'])->name('inventory.items.by-type');
        Route::get('/trashed', [ItemController::class, 'trashed'])->name('inventory.items.trashed');
        Route::get('/{id}', [ItemController::class, 'show'])->name('inventory.items.show');
        Route::get('/{id}/preview', [ItemController::class, 'preview'])->name('inventory.items.preview');
        Route::put('/{id}', [ItemController::class, 'update'])->name('inventory.items.update');
        Route::delete('/{id}', [ItemController::class, 'destroy'])->name('inventory.items.destroy');
        Route::post('/{id}/restore', [ItemController::class, 'restore'])->name('inventory.items.restore');
        Route::delete('/{id}/force', [ItemController::class, 'forceDelete'])->name('inventory.items.force-delete');
    });

    // Item Units Routes
    Route::prefix('item-units')->group(function () {
        Route::get('/', [ItemUnitController::class, 'index'])->name('inventory.item-units.index');
        Route::post('/', [ItemUnitController::class, 'store'])->name('inventory.item-units.store');
        Route::get('/type-options', [ItemUnitController::class, 'getUnitTypeOptions'])->name('inventory.item-units.type-options');
        Route::get('/contains-options', [ItemUnitController::class, 'getItemUnitContainsOptions'])->name('inventory.item-units.contains-options');
        Route::get('/form-data', [ItemUnitController::class, 'getFormData'])->name('inventory.item-units.form-data');
        Route::post('/calculate-conversion', [ItemUnitController::class, 'calculateConversion'])->name('inventory.item-units.calculate-conversion');
        Route::get('/item/{itemId}', [ItemUnitController::class, 'byItem'])->name('inventory.item-units.by-item');
        Route::get('/item/{itemId}/type/{type}', [ItemUnitController::class, 'getByType'])->name('inventory.item-units.by-type');
        Route::get('/item/{itemId}/comprehensive', [ItemUnitController::class, 'getComprehensiveData'])->name('inventory.item-units.comprehensive');
        Route::get('/{id}', [ItemUnitController::class, 'show'])->name('inventory.item-units.show');
        Route::put('/{id}', [ItemUnitController::class, 'update'])->name('inventory.item-units.update');
        Route::put('/{id}/set-default', [ItemUnitController::class, 'setDefault'])->name('inventory.item-units.set-default');
        Route::delete('/{id}', [ItemUnitController::class, 'destroy'])->name('inventory.item-units.destroy');
    });

    // Barcode Types Routes
    Route::prefix('barcode-types')->group(function () {
        Route::get('/', [BarcodeTypeController::class, 'index'])->name('inventory.barcode-types.index');
        Route::get('/options', [BarcodeTypeController::class, 'getOptions'])->name('inventory.barcode-types.options');
        Route::get('/supported', [BarcodeTypeController::class, 'getSupportedTypes'])->name('inventory.barcode-types.supported');
        Route::get('/{id}', [BarcodeTypeController::class, 'show'])->name('inventory.barcode-types.show');
        Route::post('/validate', [BarcodeTypeController::class, 'validateBarcode'])->name('inventory.barcode-types.validate');
        Route::post('/generate', [BarcodeTypeController::class, 'generateBarcode'])->name('inventory.barcode-types.generate');
        Route::post('/generate-svg', [BarcodeTypeController::class, 'generateBarcodeSVG'])->name('inventory.barcode-types.generate-svg');
    });

    // Item Types Routes
    Route::prefix('item-types')->group(function () {
        Route::get('/', [ItemTypeController::class, 'index'])->name('inventory.item-types.index');
        Route::post('/', [ItemTypeController::class, 'store'])->name('inventory.item-types.store');
        Route::get('/options', [ItemTypeController::class, 'getOptions'])->name('inventory.item-types.options');
        Route::get('/{id}', [ItemTypeController::class, 'show'])->name('inventory.item-types.show');
        Route::put('/{id}', [ItemTypeController::class, 'update'])->name('inventory.item-types.update');
        Route::delete('/{id}', [ItemTypeController::class, 'destroy'])->name('inventory.item-types.destroy');
    });

    // BOM Items Routes
    Route::prefix('bom-items')->group(function () {
        Route::get('/', [BomItemController::class, 'index'])->name('inventory.bom-items.index');
        Route::post('/', [BomItemController::class, 'store'])->name('inventory.bom-items.store');
        Route::get('/item/{itemId}', [BomItemController::class, 'byItem'])->name('inventory.bom-items.by-item');
        Route::get('/component/{componentId}', [BomItemController::class, 'byComponent'])->name('inventory.bom-items.by-component');
        Route::post('/calculate-requirements', [BomItemController::class, 'calculateRequirements'])->name('inventory.bom-items.calculate-requirements');
        Route::get('/{id}', [BomItemController::class, 'show'])->name('inventory.bom-items.show');
        Route::put('/{id}', [BomItemController::class, 'update'])->name('inventory.bom-items.update');
        Route::delete('/{id}', [BomItemController::class, 'destroy'])->name('inventory.bom-items.destroy');
    });

    // Legacy route for backward compatibility
    Route::apiResource('inventories', InventoryController::class)->names('inventory');
});
