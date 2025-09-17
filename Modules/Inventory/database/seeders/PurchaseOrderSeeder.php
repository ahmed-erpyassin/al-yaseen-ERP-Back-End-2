<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\Warehouse;
use Modules\Suppliers\Models\Supplier;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Carbon\Carbon;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Purchase Orders...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();
        $currency = Currency::where('code', 'SAR')->first();

        if (!$user || !$company || $warehouses->isEmpty() || $suppliers->isEmpty() || !$currency) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Companies, Warehouses, Suppliers, and Currencies first.');
            return;
        }

        $purchaseOrders = [
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_id' => $suppliers->where('supplier_code', 'SUP-001')->first()?->id ?? $suppliers->first()->id,
                'warehouse_id' => $warehouses->first()->id,
                'order_number' => 'PO-001',
                'order_date' => Carbon::now()->subDays(30),
                'delivery_date' => Carbon::now()->subDays(25),
                'received_date' => Carbon::now()->subDays(25),
                'currency_id' => $currency->id,
                'currency_rate' => 1.0000,
                'subtotal' => 125000.00,
                'discount_percentage' => 0.00,
                'discount_amount' => 0.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 18750.00,
                'total_amount' => 143750.00,
                'status' => 'received',
                'notes' => 'Bulk order for new office setup - laptops and accessories',
                'terms_conditions' => 'Standard Dell warranty terms apply. Free delivery included.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_id' => $suppliers->where('supplier_code', 'SUP-002')->first()?->id ?? $suppliers->skip(1)->first()->id,
                'warehouse_id' => $warehouses->skip(1)->first()?->id ?? $warehouses->first()->id,
                'order_number' => 'PO-002',
                'order_date' => Carbon::now()->subDays(25),
                'delivery_date' => Carbon::now()->subDays(20),
                'received_date' => Carbon::now()->subDays(20),
                'currency_id' => $currency->id,
                'currency_rate' => 1.0000,
                'subtotal' => 60000.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 3000.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 9000.00,
                'total_amount' => 66000.00,
                'status' => 'received',
                'notes' => 'Monitor order for multiple office locations',
                'terms_conditions' => '3-year warranty. Bulk discount applied.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_id' => $suppliers->where('supplier_code', 'SUP-003')->first()?->id ?? $suppliers->skip(2)->first()->id,
                'warehouse_id' => $warehouses->skip(2)->first()?->id ?? $warehouses->first()->id,
                'order_number' => 'PO-003',
                'order_date' => Carbon::now()->subDays(20),
                'delivery_date' => Carbon::now()->subDays(15),
                'received_date' => Carbon::now()->subDays(15),
                'currency_id' => $currency->id,
                'currency_rate' => 1.0000,
                'subtotal' => 175000.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 8750.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 26250.00,
                'total_amount' => 192500.00,
                'status' => 'received',
                'notes' => 'Steel rods for construction project - Grade 60',
                'terms_conditions' => 'Quality certificates included. Delivery to project site.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_id' => $suppliers->where('supplier_code', 'SUP-004')->first()?->id ?? $suppliers->skip(3)->first()->id,
                'warehouse_id' => $warehouses->first()->id,
                'order_number' => 'PO-004',
                'order_date' => Carbon::now()->subDays(15),
                'delivery_date' => Carbon::now()->subDays(10),
                'received_date' => null,
                'currency_id' => $currency->id,
                'currency_rate' => 1.0000,
                'subtotal' => 12500.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 625.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 1875.00,
                'total_amount' => 13750.00,
                'status' => 'confirmed',
                'notes' => 'White wall paint for interior finishing',
                'terms_conditions' => 'Environmental compliance certificates required.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_id' => $suppliers->where('supplier_code', 'SUP-005')->first()?->id ?? $suppliers->skip(4)->first()->id,
                'warehouse_id' => $warehouses->skip(1)->first()?->id ?? $warehouses->first()->id,
                'order_number' => 'PO-005',
                'order_date' => Carbon::now()->subDays(10),
                'delivery_date' => Carbon::now()->addDays(5),
                'received_date' => null,
                'currency_id' => $currency->id,
                'currency_rate' => 1.0000,
                'subtotal' => 3000.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 150.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 450.00,
                'total_amount' => 3300.00,
                'status' => 'sent',
                'notes' => 'A4 copy paper bulk order for all offices',
                'terms_conditions' => 'Free delivery for orders above 2000 SAR.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($purchaseOrders as $poData) {
            PurchaseOrder::create($poData);
        }

        $this->command->info('✅ Purchase Orders seeded successfully!');
    }
}
