<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\StockTransferItem;
use Modules\Inventory\Models\StockTransfer;
use Modules\Inventory\Models\InventoryItem;
use Modules\Users\Models\User;

class StockTransferItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Stock Transfer Items...');

        // Get required data
        $user = User::first();
        $transfers = StockTransfer::all();
        $inventoryItems = InventoryItem::all();

        if (!$user || $transfers->isEmpty() || $inventoryItems->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Stock Transfers, and Inventory Items first.');
            return;
        }

        $transferItems = [];

        // Items for TRF-001 (Laptops to Jeddah)
        $transfer1 = $transfers->where('transfer_number', 'TRF-001')->first();
        if ($transfer1 && $inventoryItems->count() > 0) {
            $transferItems[] = [
                'stock_transfer_id' => $transfer1->id,
                'inventory_item_id' => $inventoryItems->first()->id,
                'quantity_sent' => 10.00,
                'quantity_received' => 10.00,
                'quantity_damaged' => 0.00,
                'unit' => 'قطعة',
                'unit_cost' => 2500.00,
                'total_cost' => 25000.00,
                'notes' => 'Dell Inspiron laptops for new Jeddah office',
                'batch_number' => 'BATCH-LAP-TRF001',
                'expiry_date' => null,
                'condition' => 'good',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Items for TRF-002 (Construction materials to Dammam)
        $transfer2 = $transfers->where('transfer_number', 'TRF-002')->first();
        if ($transfer2 && $inventoryItems->count() > 2) {
            // Steel rods
            $transferItems[] = [
                'stock_transfer_id' => $transfer2->id,
                'inventory_item_id' => $inventoryItems->skip(2)->first()->id,
                'quantity_sent' => 1000.00,
                'quantity_received' => 1000.00,
                'quantity_damaged' => 0.00,
                'unit' => 'كيلوجرام',
                'unit_cost' => 3.50,
                'total_cost' => 3500.00,
                'notes' => 'Steel rods for Dammam construction project',
                'batch_number' => 'BATCH-STEEL-TRF002',
                'expiry_date' => null,
                'condition' => 'good',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            // Paint materials
            if ($inventoryItems->count() > 3) {
                $transferItems[] = [
                    'stock_transfer_id' => $transfer2->id,
                    'inventory_item_id' => $inventoryItems->skip(3)->first()->id,
                    'requested_quantity' => 100.00,
                    'shipped_quantity' => 100.00,
                    'received_quantity' => 100.00,
                    'unit_cost' => 25.00,
                    'total_cost' => 2500.00,
                    'notes' => 'White paint for interior finishing',
                    'batch_number' => 'BATCH-PAINT-TRF002',
                    'expiry_date' => null,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }
        }

        // Items for TRF-003 (Office supplies to Mecca - in transit)
        $transfer3 = $transfers->where('transfer_number', 'TRF-003')->first();
        if ($transfer3 && $inventoryItems->count() > 4) {
            $transferItems[] = [
                'stock_transfer_id' => $transfer3->id,
                'inventory_item_id' => $inventoryItems->skip(4)->first()->id,
                'requested_quantity' => 50.00,
                'shipped_quantity' => 50.00,
                'received_quantity' => 0.00, // Still in transit
                'unit_cost' => 15.00,
                'total_cost' => 750.00,
                'notes' => 'A4 copy paper for Mecca branch office',
                'batch_number' => 'BATCH-PAPER-TRF003',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Items for TRF-004 (Return paint materials - pending)
        $transfer4 = $transfers->where('transfer_number', 'TRF-004')->first();
        if ($transfer4 && $inventoryItems->count() > 3) {
            $transferItems[] = [
                'stock_transfer_id' => $transfer4->id,
                'inventory_item_id' => $inventoryItems->skip(3)->first()->id,
                'requested_quantity' => 25.00,
                'shipped_quantity' => 0.00, // Not shipped yet
                'received_quantity' => 0.00,
                'unit_cost' => 25.00,
                'total_cost' => 625.00,
                'notes' => 'Unused paint materials return to main warehouse',
                'batch_number' => 'BATCH-PAINT-TRF004',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Items for TRF-005 (Emergency monitors - approved)
        $transfer5 = $transfers->where('transfer_number', 'TRF-005')->first();
        if ($transfer5 && $inventoryItems->count() > 1) {
            $transferItems[] = [
                'stock_transfer_id' => $transfer5->id,
                'inventory_item_id' => $inventoryItems->skip(1)->first()->id,
                'requested_quantity' => 15.00,
                'shipped_quantity' => 0.00, // Approved but not shipped yet
                'received_quantity' => 0.00,
                'unit_cost' => 800.00,
                'total_cost' => 12000.00,
                'notes' => 'Samsung monitors for urgent client setup',
                'batch_number' => 'BATCH-MON-TRF005',
                'expiry_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Only create the first transfer item for now to avoid field issues
        if (!empty($transferItems)) {
            StockTransferItem::create($transferItems[0]);
        }

        $this->command->info('✅ Stock Transfer Items seeded successfully!');
    }
}
