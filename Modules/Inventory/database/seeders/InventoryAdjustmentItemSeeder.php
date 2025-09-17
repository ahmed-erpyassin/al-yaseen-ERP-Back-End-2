<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\InventoryAdjustmentItem;
use Modules\Inventory\Models\InventoryAdjustment;
use Modules\Inventory\Models\InventoryItem;
use Modules\Users\Models\User;
use Carbon\Carbon;

class InventoryAdjustmentItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Inventory Adjustment Items...');

        // Get required data
        $user = User::first();
        $adjustments = InventoryAdjustment::all();
        $inventoryItems = InventoryItem::all();

        if (!$user || $adjustments->isEmpty() || $inventoryItems->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Inventory Adjustments, and Inventory Items first.');
            return;
        }

        $adjustmentItems = [];

        // Items for ADJ-001 (Damaged laptops)
        $adjustment1 = $adjustments->where('adjustment_number', 'ADJ-001')->first();
        if ($adjustment1 && $inventoryItems->count() > 0) {
            $adjustmentItems[] = [
                'inventory_adjustment_id' => $adjustment1->id,
                'inventory_item_id' => $inventoryItems->first()->id,
                'system_quantity' => 50.00,
                'physical_quantity' => 47.00,
                'difference_quantity' => -3.00,
                'unit_cost' => 2500.00,
                'total_cost' => -7500.00,
                'notes' => 'Water damaged laptops - 3 units beyond repair',
                'batch_number' => 'BATCH-LAP-001',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Items for ADJ-002 (Found monitors)
        $adjustment2 = $adjustments->where('adjustment_number', 'ADJ-002')->first();
        if ($adjustment2 && $inventoryItems->count() > 1) {
            $adjustmentItems[] = [
                'inventory_adjustment_id' => $adjustment2->id,
                'inventory_item_id' => $inventoryItems->skip(1)->first()->id,
                'system_quantity' => 75.00,
                'physical_quantity' => 78.00,
                'difference_quantity' => 3.00,
                'unit_cost' => 800.00,
                'total_cost' => 2400.00,
                'notes' => 'Additional monitors found in storage room',
                'batch_number' => 'BATCH-MON-002',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Items for ADJ-003 (Expired paint)
        $adjustment3 = $adjustments->where('adjustment_number', 'ADJ-003')->first();
        if ($adjustment3 && $inventoryItems->count() > 3) {
            $adjustmentItems[] = [
                'inventory_adjustment_id' => $adjustment3->id,
                'inventory_item_id' => $inventoryItems->skip(3)->first()->id,
                'system_quantity' => 500.00,
                'physical_quantity' => 450.00,
                'difference_quantity' => -50.00,
                'unit_cost' => 25.00,
                'total_cost' => -1250.00,
                'notes' => 'Expired paint disposed according to safety regulations',
                'batch_number' => 'BATCH-PAINT-003',
                'expiry_date' => Carbon::now()->subMonths(2)->toDateString(),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Items for ADJ-004 (Recount - multiple items)
        $adjustment4 = $adjustments->where('adjustment_number', 'ADJ-004')->first();
        if ($adjustment4 && $inventoryItems->count() > 4) {
            // First item - Steel rods
            $adjustmentItems[] = [
                'inventory_adjustment_id' => $adjustment4->id,
                'inventory_item_id' => $inventoryItems->skip(2)->first()->id,
                'system_quantity' => 5000.00,
                'physical_quantity' => 4985.00,
                'difference_quantity' => -15.00,
                'unit_cost' => 3.50,
                'total_cost' => -52.50,
                'notes' => 'Minor discrepancy found during quarterly count',
                'batch_number' => 'BATCH-STEEL-004',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // Second item - Paper
            if ($inventoryItems->count() > 4) {
                $adjustmentItems[] = [
                    'inventory_adjustment_id' => $adjustment4->id,
                    'inventory_item_id' => $inventoryItems->skip(4)->first()->id,
                    'system_quantity' => 200.00,
                    'physical_quantity' => 205.00,
                    'difference_quantity' => 5.00,
                    'unit_cost' => 15.00,
                    'total_cost' => 75.00,
                    'notes' => 'Extra cartons found during count',
                    'batch_number' => 'BATCH-PAPER-004',
                    'expiry_date' => null,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }
        }

        // Items for ADJ-005 (Missing items)
        $adjustment5 = $adjustments->where('adjustment_number', 'ADJ-005')->first();
        if ($adjustment5 && $inventoryItems->count() > 0) {
            $adjustmentItems[] = [
                'inventory_adjustment_id' => $adjustment5->id,
                'inventory_item_id' => $inventoryItems->first()->id,
                'system_quantity' => 47.00,
                'physical_quantity' => 45.00,
                'difference_quantity' => -2.00,
                'unit_cost' => 2500.00,
                'total_cost' => -5000.00,
                'notes' => 'Items missing from designated location - investigation pending',
                'batch_number' => 'BATCH-LAP-005',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        foreach ($adjustmentItems as $itemData) {
            InventoryAdjustmentItem::create($itemData);
        }

        $this->command->info('✅ Inventory Adjustment Items seeded successfully!');
    }
}
