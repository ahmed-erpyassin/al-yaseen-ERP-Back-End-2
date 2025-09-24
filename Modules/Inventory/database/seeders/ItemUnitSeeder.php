<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemUnit;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;

class ItemUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Item Units...');

        // Get required data
        $company = Company::first();
        $user = User::first();
        $branch = Branch::first();
        $items = Item::all();
        $units = Unit::all();
        $warehouses = Warehouse::all();

        if (!$company || !$user || $items->isEmpty() || $units->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Companies, Users, Items, and Units first.');
            return;
        }

        // Get specific units for different types
        $pieceUnit = $units->where('code', 'PCS')->first();
        $kgUnit = $units->where('code', 'KG')->first();
        $literUnit = $units->where('code', 'L')->first();
        $meterUnit = $units->where('code', 'M')->first();
        $boxUnit = $units->where('code', 'BOX')->first();
        $cartonUnit = $units->where('code', 'CTN')->first();

        $itemUnits = [];

        // Create item units for each item with different unit configurations
        foreach ($items->take(10) as $index => $item) {
            $baseUnitId = $pieceUnit?->id ?? $units->first()->id;

            // Primary unit (default) - usually pieces
            $itemUnits[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $item->id,
                'unit_id' => $baseUnitId,
                'conversion_rate' => 1.000000,
                'is_default' => true,
                'unit_type' => 'balance',
                'quantity_factor' => 1.0000,
                'balance_unit' => 'piece',
                'custom_balance_unit' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'second_unit' => null,
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => null,
                'second_unit_content' => null,
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => $baseUnitId,
                'default_warehouse_id' => $warehouses->first()?->id,
                'contains' => 'all',
                'custom_contains' => null,
                'unit_content' => 'Single piece',
                'unit_item_number' => $item->item_number . '-PCS',
                'unit_purchase_price' => $item->purchase_price,
                'unit_sale_price' => $item->sale_price,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // Add secondary units for some items
            if ($index % 2 == 0 && $boxUnit) {
                // Box unit (contains multiple pieces)
                $itemUnits[] = [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'item_id' => $item->id,
                    'unit_id' => $boxUnit->id,
                    'conversion_rate' => 12.000000, // 1 box = 12 pieces
                    'is_default' => false,
                    'unit_type' => 'second',
                    'quantity_factor' => 12.0000,
                    'balance_unit' => 'piece',
                    'custom_balance_unit' => null,
                    'length' => 30.00,
                    'width' => 20.00,
                    'height' => 15.00,
                    'second_unit' => 'piece',
                    'custom_second_unit' => 'box',
                    'second_unit_contains' => 'all',
                    'custom_second_unit_contains' => null,
                    'second_unit_content' => '12 pieces per box',
                    'second_unit_item_number' => $item->item_number . '-BOX',
                    'third_unit' => null,
                    'custom_third_unit' => null,
                    'third_unit_contains' => 'all',
                    'custom_third_unit_contains' => null,
                    'third_unit_content' => null,
                    'third_unit_item_number' => null,
                    'default_handling_unit_id' => $boxUnit->id,
                    'default_warehouse_id' => $warehouses->first()?->id,
                    'contains' => 'all',
                    'custom_contains' => null,
                    'unit_content' => 'Box containing 12 pieces',
                    'unit_item_number' => $item->item_number . '-BOX',
                    'unit_purchase_price' => $item->purchase_price * 12 * 0.95, // 5% bulk discount
                    'unit_sale_price' => $item->sale_price * 12 * 0.98, // 2% bulk discount
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }

            // Add carton units for some items
            if ($index % 3 == 0 && $cartonUnit) {
                // Carton unit (contains multiple boxes)
                $itemUnits[] = [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'item_id' => $item->id,
                    'unit_id' => $cartonUnit->id,
                    'conversion_rate' => 144.000000, // 1 carton = 144 pieces (12 boxes * 12 pieces)
                    'is_default' => false,
                    'unit_type' => 'third',
                    'quantity_factor' => 144.0000,
                    'balance_unit' => 'piece',
                    'custom_balance_unit' => null,
                    'length' => 60.00,
                    'width' => 40.00,
                    'height' => 30.00,
                    'second_unit' => 'piece',
                    'custom_second_unit' => 'box',
                    'second_unit_contains' => 'all',
                    'custom_second_unit_contains' => null,
                    'second_unit_content' => '12 pieces per box',
                    'second_unit_item_number' => $item->item_number . '-BOX',
                    'third_unit' => 'carton',
                    'custom_third_unit' => null,
                    'third_unit_contains' => 'all',
                    'custom_third_unit_contains' => null,
                    'third_unit_content' => '12 boxes per carton (144 pieces total)',
                    'third_unit_item_number' => $item->item_number . '-CTN',
                    'default_handling_unit_id' => $cartonUnit->id,
                    'default_warehouse_id' => $warehouses->first()?->id,
                    'contains' => 'all',
                    'custom_contains' => null,
                    'unit_content' => 'Carton containing 12 boxes (144 pieces total)',
                    'unit_item_number' => $item->item_number . '-CTN',
                    'unit_purchase_price' => $item->purchase_price * 144 * 0.90, // 10% bulk discount
                    'unit_sale_price' => $item->sale_price * 144 * 0.95, // 5% bulk discount
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }

            // Add weight-based units for some items (if applicable)
            if ($index % 4 == 0 && $kgUnit && in_array($index, [0, 4, 8])) {
                $itemUnits[] = [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'item_id' => $item->id,
                    'unit_id' => $kgUnit->id,
                    'conversion_rate' => 0.500000, // 1 kg = 0.5 pieces (assuming 2kg per piece)
                    'is_default' => false,
                    'unit_type' => 'balance',
                    'quantity_factor' => 0.5000,
                    'balance_unit' => 'kilo',
                    'custom_balance_unit' => null,
                    'length' => null,
                    'width' => null,
                    'height' => null,
                    'second_unit' => null,
                    'custom_second_unit' => null,
                    'second_unit_contains' => 'all',
                    'custom_second_unit_contains' => null,
                    'second_unit_content' => null,
                    'second_unit_item_number' => null,
                    'third_unit' => null,
                    'custom_third_unit' => null,
                    'third_unit_contains' => 'all',
                    'custom_third_unit_contains' => null,
                    'third_unit_content' => null,
                    'third_unit_item_number' => null,
                    'default_handling_unit_id' => $kgUnit->id,
                    'default_warehouse_id' => $warehouses->first()?->id,
                    'contains' => 'all',
                    'custom_contains' => null,
                    'unit_content' => 'Weight-based measurement',
                    'unit_item_number' => $item->item_number . '-KG',
                    'unit_purchase_price' => $item->purchase_price * 2, // Price per kg
                    'unit_sale_price' => $item->sale_price * 2, // Price per kg
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }
        }

        // Insert all item units
        foreach ($itemUnits as $itemUnitData) {
            try {
                ItemUnit::firstOrCreate([
                    'item_id' => $itemUnitData['item_id'],
                    'unit_id' => $itemUnitData['unit_id'],
                ], $itemUnitData);
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Failed to create item unit: " . $e->getMessage());
            }
        }

        $this->command->info('✅ Item Units seeded successfully! Created ' . count($itemUnits) . ' item unit relationships.');
    }
}
