<?php

namespace Modules\Purchases\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;

class PurchaseItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Purchase Items...');

        // Get required data
        $purchases = Purchase::all();
        $items = Item::all();
        $units = Unit::all();

        if ($purchases->isEmpty()) {
            $this->command->warn('⚠️  No purchases found. Please seed Purchases first.');
            return;
        }

        // Create items for each purchase
        foreach ($purchases as $purchase) {
            $this->createItemsForPurchase($purchase, $items, $units);
        }

        $this->command->info('✅ Purchase Items seeded successfully!');
    }

    private function createItemsForPurchase($purchase, $items, $units)
    {
        $serialNumber = 1;

        if ($purchase->type === 'invoice') {
            // Office supplies and equipment
            $purchaseItems = [
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->isNotEmpty() ? $items->first()->id : null,
                    'item_number' => $items->isNotEmpty() ? $items->first()->item_number ?? 'ITEM-001' : 'ITEM-001',
                    'item_name' => $items->isNotEmpty() ? $items->first()->name ?? 'Office Chair' : 'Office Chair',
                    'unit_id' => $units->isNotEmpty() ? $units->first()->id : null,
                    'unit_name' => $units->isNotEmpty() ? $units->first()->name ?? 'Piece' : 'Piece',
                    'description' => 'Ergonomic office chair with lumbar support',
                    'quantity' => 5.0000,
                    'unit_price' => 800.0000,
                    'discount_rate' => 5.00,
                    'discount_percentage' => 5.00,
                    'discount_amount' => 200.00,
                    'net_unit_price' => 760.0000,
                    'line_total_before_tax' => 3800.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 570.0000,
                    'line_total_after_tax' => 4370.0000,
                    'total_foreign' => 4370.0000,
                    'total_local' => 4370.0000,
                    'total' => 4370.0000,
                    'notes' => 'High-quality office furniture',
                ],
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->count() > 1 ? $items->skip(1)->first()->id : ($items->isNotEmpty() ? $items->first()->id : null),
                    'item_number' => $items->count() > 1 ? ($items->skip(1)->first()->item_number ?? 'ITEM-002') : 'ITEM-002',
                    'item_name' => $items->count() > 1 ? ($items->skip(1)->first()->name ?? 'Laptop Computer') : 'Laptop Computer',
                    'unit_id' => $units->isNotEmpty() ? $units->first()->id : null,
                    'unit_name' => $units->isNotEmpty() ? $units->first()->name ?? 'Piece' : 'Piece',
                    'description' => 'Business laptop with 16GB RAM and 512GB SSD',
                    'quantity' => 3.0000,
                    'unit_price' => 2500.0000,
                    'discount_rate' => 4.00,
                    'discount_percentage' => 4.00,
                    'discount_amount' => 300.00,
                    'net_unit_price' => 2400.0000,
                    'line_total_before_tax' => 7200.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 1080.0000,
                    'line_total_after_tax' => 8280.0000,
                    'total_foreign' => 8280.0000,
                    'total_local' => 8280.0000,
                    'total' => 8280.0000,
                    'notes' => 'High-performance business laptops',
                ],
            ];
        } elseif ($purchase->type === 'order') {
            // Inventory items for purchase order
            $purchaseItems = [
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->isNotEmpty() ? $items->first()->id : null,
                    'item_number' => $items->isNotEmpty() ? $items->first()->item_number ?? 'ITEM-003' : 'ITEM-003',
                    'item_name' => $items->isNotEmpty() ? $items->first()->name ?? 'Raw Material A' : 'Raw Material A',
                    'unit_id' => $units->count() > 1 ? $units->skip(1)->first()->id : ($units->isNotEmpty() ? $units->first()->id : null),
                    'unit_name' => $units->count() > 1 ? ($units->skip(1)->first()->name ?? 'Kg') : 'Kg',
                    'description' => 'High-grade raw material for production',
                    'quantity' => 100.0000,
                    'unit_price' => 50.0000,
                    'discount_rate' => 0.00,
                    'discount_percentage' => 0.00,
                    'discount_amount' => 0.00,
                    'net_unit_price' => 50.0000,
                    'line_total_before_tax' => 5000.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 750.0000,
                    'line_total_after_tax' => 5750.0000,
                    'total_foreign' => 5750.0000,
                    'total_local' => 5750.0000,
                    'total' => 5750.0000,
                    'notes' => 'Premium quality raw material',
                ],
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->count() > 1 ? $items->skip(1)->first()->id : ($items->isNotEmpty() ? $items->first()->id : null),
                    'item_number' => $items->count() > 1 ? ($items->skip(1)->first()->item_number ?? 'ITEM-004') : 'ITEM-004',
                    'item_name' => $items->count() > 1 ? ($items->skip(1)->first()->name ?? 'Component B') : 'Component B',
                    'unit_id' => $units->isNotEmpty() ? $units->first()->id : null,
                    'unit_name' => $units->isNotEmpty() ? $units->first()->name ?? 'Piece' : 'Piece',
                    'description' => 'Electronic component for assembly',
                    'quantity' => 200.0000,
                    'unit_price' => 25.0000,
                    'discount_rate' => 0.00,
                    'discount_percentage' => 0.00,
                    'discount_amount' => 0.00,
                    'net_unit_price' => 25.0000,
                    'line_total_before_tax' => 5000.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 750.0000,
                    'line_total_after_tax' => 5750.0000,
                    'total_foreign' => 5750.0000,
                    'total_local' => 5750.0000,
                    'total' => 5750.0000,
                    'notes' => 'High-quality electronic components',
                ],
            ];
        } elseif ($purchase->type === 'order' && $purchase->customer_id) {
            // Customer order items
            $purchaseItems = [
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->isNotEmpty() ? $items->first()->id : null,
                    'item_number' => $items->isNotEmpty() ? $items->first()->item_number ?? 'ITEM-005' : 'ITEM-005',
                    'item_name' => $items->isNotEmpty() ? $items->first()->name ?? 'Finished Product A' : 'Finished Product A',
                    'unit_id' => $units->isNotEmpty() ? $units->first()->id : null,
                    'unit_name' => $units->isNotEmpty() ? $units->first()->name ?? 'Piece' : 'Piece',
                    'description' => 'Custom manufactured product for customer',
                    'quantity' => 50.0000,
                    'unit_price' => 200.0000,
                    'discount_rate' => 2.00,
                    'discount_percentage' => 2.00,
                    'discount_amount' => 200.00,
                    'net_unit_price' => 196.0000,
                    'line_total_before_tax' => 9800.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 1470.0000,
                    'line_total_after_tax' => 11270.0000,
                    'total_foreign' => 11270.0000,
                    'total_local' => 11270.0000,
                    'total' => 11270.0000,
                    'notes' => 'Custom order for valued customer',
                ],
            ];
        } elseif ($purchase->type === 'expense') {
            // Expense items (non-inventory)
            $purchaseItems = [
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->isNotEmpty() ? $items->first()->id : null,
                    'item_number' => 'EXP-001',
                    'item_name' => 'Office Expenses',
                    'description' => 'Monthly office rent and utilities',
                    'quantity' => 1.0000,
                    'unit_price' => 2500.0000,
                    'discount_rate' => 0.00,
                    'discount_percentage' => 0.00,
                    'discount_amount' => 0.00,
                    'net_unit_price' => 2500.0000,
                    'line_total_before_tax' => 2500.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 375.0000,
                    'line_total_after_tax' => 2875.0000,
                    'total_foreign' => 2875.0000,
                    'total_local' => 2875.0000,
                    'total' => 2875.0000,
                    'notes' => 'Monthly operational expenses',
                ],
            ];
        } else {
            // Default items for other types
            $purchaseItems = [
                [
                    'purchase_id' => $purchase->id,
                    'serial_number' => $serialNumber++,
                    'item_id' => $items->isNotEmpty() ? $items->first()->id : null,
                    'item_number' => $items->isNotEmpty() ? $items->first()->item_number ?? 'ITEM-999' : 'ITEM-999',
                    'item_name' => $items->isNotEmpty() ? $items->first()->name ?? 'General Item' : 'General Item',
                    'unit_id' => $units->isNotEmpty() ? $units->first()->id : null,
                    'unit_name' => $units->isNotEmpty() ? $units->first()->name ?? 'Piece' : 'Piece',
                    'description' => 'General purchase item',
                    'quantity' => 1.0000,
                    'unit_price' => 100.0000,
                    'discount_rate' => 0.00,
                    'discount_percentage' => 0.00,
                    'discount_amount' => 0.00,
                    'net_unit_price' => 100.0000,
                    'line_total_before_tax' => 100.0000,
                    'tax_rate' => 15.00,
                    'tax_amount' => 15.0000,
                    'line_total_after_tax' => 115.0000,
                    'total_foreign' => 115.0000,
                    'total_local' => 115.0000,
                    'total' => 115.0000,
                    'notes' => 'General purchase item',
                ],
            ];
        }

        foreach ($purchaseItems as $itemData) {
            PurchaseItem::create($itemData);
        }
    }
}
