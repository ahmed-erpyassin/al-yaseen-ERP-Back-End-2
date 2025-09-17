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
            UnitSeeder::class,
            WarehouseSeeder::class,
            ItemSeeder::class,
            InventoryItemSeeder::class,
            StockMovementSeeder::class,
        ]);

        $this->command->info('✅ Inventory Management Module seeded successfully!');
    }
}
