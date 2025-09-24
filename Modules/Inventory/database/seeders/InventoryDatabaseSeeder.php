<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;

class InventoryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding Inventory Management Module...');

        // Skip existing seeders that might have duplicate data
        $this->command->info('⚠️  Skipping BarcodeTypesSeeder and ItemTypesSeeder (already exist)');

        $this->call([
            // Basic setup seeders
            DepartmentWarehouseSeeder::class,
            CurrencySeeder::class,
            SupplierSeeder::class,
            UnitSeeder::class,
            ItemCategorySeeder::class,

            // Warehouse and item seeders
            WarehouseSeeder::class,
            ItemSeeder::class,
            ItemUnitSeeder::class, // Add ItemUnitSeeder after ItemSeeder
            InventoryItemSeeder::class,

            // Stock and movement seeders
            InventoryStockSeeder::class,
            StockMovementSeeder::class,
            InventoryMovementSeeder::class,
            InventoryMovementDataSeeder::class,

            // Purchase order seeders
            PurchaseOrderSeeder::class,
            PurchaseOrderItemSeeder::class,

            // Transfer seeders
            StockTransferSeeder::class,
            StockTransferItemSeeder::class,

            // Adjustment seeders
            InventoryAdjustmentSeeder::class,
            InventoryAdjustmentItemSeeder::class,

            // BOM (Bill of Materials) seeders
            BomItemSeeder::class,
        ]);

        $this->command->info('✅ Inventory Management Module seeded successfully!');
    }
}
