<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Carbon\Carbon;

class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Inventory Movements...');

        // Get required data
        $company = Company::first();
        $user = User::first();
        $warehouses = Warehouse::all();

        if (!$company || !$user || $warehouses->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Companies, Users, and Warehouses first.');
            return;
        }

        $movements = [
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'movement_number' => 'INV-MOV-001',
                'movement_type' => 'inbound',
                'movement_date' => Carbon::now()->subDays(30),
                'movement_time' => '09:00:00',
                'movement_datetime' => Carbon::now()->subDays(30)->setTime(9, 0, 0),
                'vendor_id' => null,
                'customer_id' => null,
                'vendor_name' => 'Tech Solutions Co.',
                'customer_name' => null,
                'movement_description' => 'Initial stock receipt for new warehouse setup',
                'inbound_invoice_id' => null,
                'outbound_invoice_id' => null,
                'user_number' => 'USR-001',
                'shipment_number' => 'SHP-001',
                'invoice_number' => 'INV-2025-001',
                'reference' => 'Initial Stock',
                'warehouse_id' => $warehouses->first()->id,
                'warehouse_number' => $warehouses->first()->warehouse_number ?? 'WH-001',
                'warehouse_name' => $warehouses->first()->warehouse_name_ar ?? 'المستودع الرئيسي',
                'status' => 'confirmed',
                'is_confirmed' => true,
                'confirmed_at' => Carbon::now()->subDays(30)->addHours(2),
                'confirmed_by' => $user->id,
                'total_quantity' => 500.00,
                'total_value' => 125000.00,
                'total_items' => 5,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'movement_number' => 'INV-MOV-002',
                'movement_type' => 'outbound',
                'movement_date' => Carbon::now()->subDays(25),
                'movement_time' => '14:30:00',
                'movement_datetime' => Carbon::now()->subDays(25)->setTime(14, 30, 0),
                'vendor_id' => null,
                'customer_id' => null,
                'vendor_name' => null,
                'customer_name' => 'Al-Yaseen Construction',
                'movement_description' => 'Materials delivery for construction project',
                'inbound_invoice_id' => null,
                'outbound_invoice_id' => null,
                'user_number' => 'USR-001',
                'shipment_number' => 'SHP-002',
                'invoice_number' => 'OUT-2025-001',
                'reference' => 'Project Delivery',
                'warehouse_id' => $warehouses->first()->id,
                'warehouse_number' => $warehouses->first()->warehouse_number ?? 'WH-001',
                'warehouse_name' => $warehouses->first()->warehouse_name_ar ?? 'المستودع الرئيسي',
                'status' => 'confirmed',
                'is_confirmed' => true,
                'confirmed_at' => Carbon::now()->subDays(25)->addHours(1),
                'confirmed_by' => $user->id,
                'total_quantity' => 150.00,
                'total_value' => 45000.00,
                'total_items' => 3,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'movement_number' => 'INV-MOV-003',
                'movement_type' => 'transfer',
                'movement_date' => Carbon::now()->subDays(20),
                'movement_time' => '11:15:00',
                'movement_datetime' => Carbon::now()->subDays(20)->setTime(11, 15, 0),
                'vendor_id' => null,
                'customer_id' => null,
                'vendor_name' => null,
                'customer_name' => null,
                'movement_description' => 'Inter-warehouse transfer for branch supply',
                'inbound_invoice_id' => null,
                'outbound_invoice_id' => null,
                'user_number' => 'USR-001',
                'shipment_number' => 'TRF-003',
                'invoice_number' => null,
                'reference' => 'Branch Transfer',
                'warehouse_id' => $warehouses->skip(1)->first()?->id ?? $warehouses->first()->id,
                'warehouse_number' => $warehouses->skip(1)->first()?->warehouse_number ?? 'WH-002',
                'warehouse_name' => $warehouses->skip(1)->first()?->warehouse_name_ar ?? 'مستودع الفرع',
                'status' => 'confirmed',
                'is_confirmed' => true,
                'confirmed_at' => Carbon::now()->subDays(20)->addMinutes(30),
                'confirmed_by' => $user->id,
                'total_quantity' => 75.00,
                'total_value' => 18750.00,
                'total_items' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'movement_number' => 'INV-MOV-004',
                'movement_type' => 'inventory_count',
                'movement_date' => Carbon::now()->subDays(15),
                'movement_time' => '16:00:00',
                'movement_datetime' => Carbon::now()->subDays(15)->setTime(16, 0, 0),
                'vendor_id' => null,
                'customer_id' => null,
                'vendor_name' => null,
                'customer_name' => null,
                'movement_description' => 'Monthly inventory count and adjustment',
                'inbound_invoice_id' => null,
                'outbound_invoice_id' => null,
                'user_number' => 'USR-001',
                'shipment_number' => null,
                'invoice_number' => null,
                'reference' => 'Monthly Count',
                'warehouse_id' => $warehouses->first()->id,
                'warehouse_number' => $warehouses->first()->warehouse_number ?? 'WH-001',
                'warehouse_name' => $warehouses->first()->warehouse_name_ar ?? 'المستودع الرئيسي',
                'status' => 'confirmed',
                'is_confirmed' => true,
                'confirmed_at' => Carbon::now()->subDays(15)->addHours(2),
                'confirmed_by' => $user->id,
                'total_quantity' => 25.00,
                'total_value' => 3750.00,
                'total_items' => 4,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'movement_number' => 'INV-MOV-005',
                'movement_type' => 'manufacturing',
                'movement_date' => Carbon::now()->subDays(10),
                'movement_time' => '08:45:00',
                'movement_datetime' => Carbon::now()->subDays(10)->setTime(8, 45, 0),
                'vendor_id' => null,
                'customer_id' => null,
                'vendor_name' => null,
                'customer_name' => null,
                'movement_description' => 'Raw materials consumption for production',
                'inbound_invoice_id' => null,
                'outbound_invoice_id' => null,
                'user_number' => 'USR-001',
                'shipment_number' => null,
                'invoice_number' => null,
                'reference' => 'Production Order #001',
                'warehouse_id' => $warehouses->skip(2)->first()?->id ?? $warehouses->first()->id,
                'warehouse_number' => $warehouses->skip(2)->first()?->warehouse_number ?? 'WH-003',
                'warehouse_name' => $warehouses->skip(2)->first()?->warehouse_name_ar ?? 'مستودع الإنتاج',
                'status' => 'draft',
                'is_confirmed' => false,
                'confirmed_at' => null,
                'confirmed_by' => null,
                'total_quantity' => 100.00,
                'total_value' => 15000.00,
                'total_items' => 3,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($movements as $movementData) {
            InventoryMovement::firstOrCreate([
                'company_id' => $movementData['company_id'],
                'movement_number' => $movementData['movement_number']
            ], $movementData);
        }

        $this->command->info('✅ Inventory Movements seeded successfully!');
    }
}
