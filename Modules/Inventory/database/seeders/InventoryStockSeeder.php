<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\InventoryItem;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class InventoryStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Inventory Stock...');

        // Get required data
        $company = Company::first();
        $user = User::first();
        $inventoryItems = InventoryItem::all();
        $warehouses = Warehouse::all();

        if (!$company || !$user || $inventoryItems->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Companies, Users, Inventory Items, and Warehouses first.');
            return;
        }

        $stockData = [];

        // Create stock records for each inventory item in each warehouse
        foreach ($inventoryItems as $item) {
            foreach ($warehouses as $warehouse) {
                // Generate realistic stock levels based on item and warehouse
                $baseQuantity = $this->getBaseQuantityForItem($item, $warehouse);
                $reservedQuantity = $baseQuantity * 0.1; // 10% reserved
                $availableQuantity = $baseQuantity - $reservedQuantity;

                $stockData[] = [
                    'company_id' => $company->id,
                    'inventory_item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $baseQuantity,
                    'reserved_quantity' => $reservedQuantity,
                    'available_quantity' => $availableQuantity,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }
        }

        // Additional specific stock scenarios
        if ($inventoryItems->count() >= 5 && $warehouses->count() >= 3) {
            // High stock scenario - Main warehouse with laptops
            $stockData[] = [
                'company_id' => $company->id,
                'inventory_item_id' => $inventoryItems->first()->id,
                'warehouse_id' => $warehouses->first()->id,
                'quantity' => 150.00,
                'reserved_quantity' => 25.00,
                'available_quantity' => 125.00,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // Low stock scenario - Branch warehouse with monitors
            $stockData[] = [
                'company_id' => $company->id,
                'inventory_item_id' => $inventoryItems->skip(1)->first()->id,
                'warehouse_id' => $warehouses->skip(1)->first()->id,
                'quantity' => 5.00,
                'reserved_quantity' => 2.00,
                'available_quantity' => 3.00,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // Zero stock scenario - Some items out of stock
            $stockData[] = [
                'company_id' => $company->id,
                'inventory_item_id' => $inventoryItems->skip(2)->first()->id,
                'warehouse_id' => $warehouses->skip(2)->first()->id,
                'quantity' => 0.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 0.00,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // High volume scenario - Construction materials
            $stockData[] = [
                'company_id' => $company->id,
                'inventory_item_id' => $inventoryItems->skip(3)->first()->id,
                'warehouse_id' => $warehouses->first()->id,
                'quantity' => 2500.00,
                'reserved_quantity' => 500.00,
                'available_quantity' => 2000.00,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // Office supplies scenario
            $stockData[] = [
                'company_id' => $company->id,
                'inventory_item_id' => $inventoryItems->skip(4)->first()->id,
                'warehouse_id' => $warehouses->skip(1)->first()->id,
                'quantity' => 1000.00,
                'reserved_quantity' => 100.00,
                'available_quantity' => 900.00,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        foreach ($stockData as $stock) {
            InventoryStock::firstOrCreate([
                'company_id' => $stock['company_id'],
                'inventory_item_id' => $stock['inventory_item_id'],
                'warehouse_id' => $stock['warehouse_id']
            ], $stock);
        }

        $this->command->info('✅ Inventory Stock seeded successfully!');
    }

    /**
     * Get base quantity for an item based on its characteristics
     */
    private function getBaseQuantityForItem($item, $warehouse): float
    {
        // Generate realistic quantities based on item name patterns
        $itemName = strtolower($item->item_name_en ?? $item->item_name_ar ?? '');
        
        if (str_contains($itemName, 'laptop') || str_contains($itemName, 'لابتوب')) {
            return rand(20, 100);
        } elseif (str_contains($itemName, 'monitor') || str_contains($itemName, 'شاشة')) {
            return rand(10, 50);
        } elseif (str_contains($itemName, 'steel') || str_contains($itemName, 'حديد')) {
            return rand(500, 2000);
        } elseif (str_contains($itemName, 'paint') || str_contains($itemName, 'دهان')) {
            return rand(50, 300);
        } elseif (str_contains($itemName, 'paper') || str_contains($itemName, 'ورق')) {
            return rand(200, 1000);
        } else {
            // Default range for other items
            return rand(10, 200);
        }
    }
}
