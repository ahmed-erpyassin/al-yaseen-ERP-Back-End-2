<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\PurchaseOrderItem;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\Item;
use Modules\Users\Models\User;
use Carbon\Carbon;

class PurchaseOrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Purchase Order Items...');

        // Get required data
        $user = User::first();
        $purchaseOrders = PurchaseOrder::all();
        $inventoryItems = \Modules\Inventory\Models\InventoryItem::all();

        if (!$user || $purchaseOrders->isEmpty() || $inventoryItems->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Purchase Orders, and Inventory Items first.');
            return;
        }

        $poItems = [];

        // Items for PO-001 (Dell laptops)
        $po1 = $purchaseOrders->where('order_number', 'PO-001')->first();
        if ($po1 && $inventoryItems->count() > 0) {
            $poItems[] = [
                'purchase_order_id' => $po1->id,
                'inventory_item_id' => $inventoryItems->first()->id,
                'item_description' => 'Dell Inspiron 15 laptops with standard warranty',
                'unit' => 'قطعة',
                'quantity_ordered' => 50.00,
                'quantity_received' => 50.00,
                'quantity_remaining' => 0.00,
                'unit_price' => 2500.00,
                'discount_percentage' => 0.00,
                'discount_amount' => 0.00,
                'net_unit_price' => 2500.00,
                'total_amount' => 125000.00,
                'status' => 'received',
                'notes' => 'Intel Core i5, 8GB RAM, 256GB SSD, 15.6" Display',
            ];
        }

        // Items for PO-002 (Samsung monitors)
        $po2 = $purchaseOrders->where('order_number', 'PO-002')->first();
        if ($po2 && $inventoryItems->count() > 1) {
            $poItems[] = [
                'purchase_order_id' => $po2->id,
                'inventory_item_id' => $inventoryItems->skip(1)->first()->id,
                'item_description' => 'Samsung 24-inch Full HD monitors with 3-year warranty',
                'unit' => 'قطعة',
                'quantity_ordered' => 75.00,
                'quantity_received' => 75.00,
                'quantity_remaining' => 0.00,
                'unit_price' => 800.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 3000.00,
                'net_unit_price' => 760.00,
                'total_amount' => 57000.00,
                'status' => 'received',
                'notes' => '24" Full HD, LED, HDMI connectivity, VESA mount compatible',
            ];
        }

        // Items for PO-003 (Steel rods)
        $po3 = $purchaseOrders->where('order_number', 'PO-003')->first();
        if ($po3 && $inventoryItems->count() > 2) {
            $poItems[] = [
                'purchase_order_id' => $po3->id,
                'inventory_item_id' => $inventoryItems->skip(2)->first()->id,
                'item_description' => 'High-grade steel reinforcement rods with quality certificates',
                'unit' => 'كيلوجرام',
                'quantity_ordered' => 5000.00,
                'quantity_received' => 5000.00,
                'quantity_remaining' => 0.00,
                'unit_price' => 3.50,
                'discount_percentage' => 5.00,
                'discount_amount' => 875.00,
                'net_unit_price' => 3.33,
                'total_amount' => 16625.00,
                'status' => 'received',
                'notes' => '12mm diameter, Grade 60, SASO certified',
            ];
        }

        // Items for PO-004 (Paint - pending)
        $po4 = $purchaseOrders->where('order_number', 'PO-004')->first();
        if ($po4 && $inventoryItems->count() > 3) {
            $poItems[] = [
                'purchase_order_id' => $po4->id,
                'inventory_item_id' => $inventoryItems->skip(3)->first()->id,
                'item_description' => 'Premium white wall paint for interior use',
                'unit' => 'لتر',
                'quantity_ordered' => 500.00,
                'quantity_received' => 0.00,
                'quantity_remaining' => 500.00,
                'unit_price' => 25.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 625.00,
                'net_unit_price' => 23.75,
                'total_amount' => 11875.00,
                'status' => 'pending',
                'notes' => 'Water-based, low VOC, washable finish',
            ];
        }

        // Items for PO-005 (Paper - approved)
        $po5 = $purchaseOrders->where('order_number', 'PO-005')->first();
        if ($po5 && $inventoryItems->count() > 4) {
            $poItems[] = [
                'purchase_order_id' => $po5->id,
                'inventory_item_id' => $inventoryItems->skip(4)->first()->id,
                'item_description' => 'A4 copy paper 80gsm, 500 sheets per ream',
                'unit' => 'رزمة',
                'quantity_ordered' => 200.00,
                'quantity_received' => 0.00,
                'quantity_remaining' => 200.00,
                'unit_price' => 15.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 150.00,
                'net_unit_price' => 14.25,
                'total_amount' => 2850.00,
                'status' => 'pending',
                'notes' => '80GSM, A4 size, white, 500 sheets/ream, 10 reams/carton',
            ];
        }

        foreach ($poItems as $itemData) {
            PurchaseOrderItem::firstOrCreate([
                'purchase_order_id' => $itemData['purchase_order_id'],
                'inventory_item_id' => $itemData['inventory_item_id']
            ], $itemData);
        }

        $this->command->info('✅ Purchase Order Items seeded successfully!');
    }
}
