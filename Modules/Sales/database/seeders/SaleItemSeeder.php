<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\Sale;

class SaleItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the sales we created
        $sale1 = Sale::where('invoice_number', 'INV-000001')->first();
        $sale2 = Sale::where('invoice_number', 'INV-000002')->first();
        $sale3 = Sale::where('invoice_number', 'QUO-000001')->first();
        $sale4 = Sale::where('invoice_number', 'INV-000003')->first();
        $sale5 = Sale::where('invoice_number', 'INV-000004')->first();
        $sale6 = Sale::where('invoice_number', 'SHIP-000001')->first();
        $sale7 = Sale::where('invoice_number', 'SRV-000001')->first();

        $saleItems = [];

        // Items for Sale 1 (INV-000001)
        if ($sale1) {
            $saleItems[] = [
                'sale_id' => $sale1->id,
                'item_id' => 1,
                'description' => 'Laptop Dell XPS 15 - High-performance laptop',
                'quantity' => 2.0000,
                'unit_price' => 500.0000,
                'discount_rate' => 50.00,
                'tax_rate' => 15.00,
                'total_foreign' => 1092.50,
                'total_local' => 1092.50,
                'total' => 1092.50,
            ];
        }

        // Items for Sale 2 (INV-000002)
        if ($sale2) {
            $saleItems[] = [
                'sale_id' => $sale2->id,
                'item_id' => 2,
                'description' => 'Office Chair Ergonomic - Comfortable office chair',
                'quantity' => 10.0000,
                'unit_price' => 100.0000,
                'discount_rate' => 100.00,
                'tax_rate' => 15.00,
                'total_foreign' => 1035.00,
                'total_local' => 1035.00,
                'total' => 1035.00,
            ];
        }

        // Items for Sale 3 (QUO-000001)
        if ($sale3) {
            $saleItems[] = [
                'sale_id' => $sale3->id,
                'item_id' => 3,
                'description' => 'Office Desk Large - Large executive desk',
                'quantity' => 5.0000,
                'unit_price' => 500.0000,
                'discount_rate' => 0.00,
                'tax_rate' => 15.00,
                'total_foreign' => 2875.00,
                'total_local' => 2875.00,
                'total' => 2875.00,
            ];
        }

        // Items for Sale 4 (INV-000003)
        if ($sale4) {
            $saleItems[] = [
                'sale_id' => $sale4->id,
                'item_id' => 1,
                'description' => 'Laptop Dell XPS 15 - High-performance laptop',
                'quantity' => 5.0000,
                'unit_price' => 500.0000,
                'discount_rate' => 125.00,
                'tax_rate' => 15.00,
                'total_foreign' => 2743.75,
                'total_local' => 2743.75,
                'total' => 2743.75,
            ];

            $saleItems[] = [
                'sale_id' => $sale4->id,
                'item_id' => 4,
                'description' => 'Wireless Mouse - Ergonomic wireless mouse',
                'quantity' => 10.0000,
                'unit_price' => 30.0000,
                'discount_rate' => 15.00,
                'tax_rate' => 15.00,
                'total_foreign' => 328.50,
                'total_local' => 328.50,
                'total' => 328.50,
            ];

            $saleItems[] = [
                'sale_id' => $sale4->id,
                'item_id' => 5,
                'description' => 'Keyboard Mechanical - RGB mechanical keyboard',
                'quantity' => 10.0000,
                'unit_price' => 80.0000,
                'discount_rate' => 40.00,
                'tax_rate' => 15.00,
                'total_foreign' => 874.00,
                'total_local' => 874.00,
                'total' => 874.00,
            ];
        }

        // Items for Sale 5 (INV-000004)
        if ($sale5) {
            $saleItems[] = [
                'sale_id' => $sale5->id,
                'item_id' => 6,
                'description' => 'Monitor 27 inch 4K - 4K UHD monitor',
                'quantity' => 3.0000,
                'unit_price' => 400.0000,
                'discount_rate' => 0.00,
                'tax_rate' => 15.00,
                'total_foreign' => 1380.00,
                'total_local' => 1380.00,
                'total' => 1380.00,
            ];
        }

        // Items for Sale 6 (SHIP-000001)
        if ($sale6) {
            $saleItems[] = [
                'sale_id' => $sale6->id,
                'item_id' => 7,
                'description' => 'Printer Laser Color - High-speed color laser printer',
                'quantity' => 2.0000,
                'unit_price' => 800.0000,
                'discount_rate' => 48.00,
                'tax_rate' => 15.00,
                'total_foreign' => 1794.40,
                'total_local' => 1794.40,
                'total' => 1794.40,
            ];

            $saleItems[] = [
                'sale_id' => $sale6->id,
                'item_id' => 8,
                'description' => 'Scanner Document - High-speed document scanner',
                'quantity' => 3.0000,
                'unit_price' => 600.0000,
                'discount_rate' => 54.00,
                'tax_rate' => 15.00,
                'total_foreign' => 2001.90,
                'total_local' => 2001.90,
                'total' => 2001.90,
            ];

            $saleItems[] = [
                'sale_id' => $sale6->id,
                'item_id' => 9,
                'description' => 'Projector HD - Full HD projector',
                'quantity' => 1.0000,
                'unit_price' => 1000.0000,
                'discount_rate' => 30.00,
                'tax_rate' => 15.00,
                'total_foreign' => 1115.50,
                'total_local' => 1115.50,
                'total' => 1115.50,
            ];
        }

        // Items for Sale 7 (SRV-000001) - Service
        if ($sale7) {
            $saleItems[] = [
                'sale_id' => $sale7->id,
                'item_id' => 10,
                'description' => 'IT Consulting Service - Professional IT consulting',
                'quantity' => 8.0000,
                'unit_price' => 100.0000,
                'discount_rate' => 0.00,
                'tax_rate' => 15.00,
                'total_foreign' => 920.00,
                'total_local' => 920.00,
                'total' => 920.00,
            ];
        }

        foreach ($saleItems as $item) {
            SaleItem::create($item);
        }

        $this->command->info('Sale items seeded successfully!');
    }
}

