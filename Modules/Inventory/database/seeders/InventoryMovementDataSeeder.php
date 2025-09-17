<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\InventoryMovementData;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Carbon\Carbon;

class InventoryMovementDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Inventory Movement Data...');

        // Get required data
        $company = Company::first();
        $user = User::first();
        $movements = InventoryMovement::all();
        $items = Item::all();
        $units = Unit::all();
        $warehouses = Warehouse::all();

        if (!$company || !$user || $movements->isEmpty() || $items->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Companies, Users, Inventory Movements, Items, and Warehouses first.');
            return;
        }

        $movementData = [];

        // Data for INV-MOV-001 (Inbound - Initial stock receipt)
        $movement1 = $movements->where('movement_number', 'INV-MOV-001')->first();
        if ($movement1 && $items->count() >= 5) {
            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement1->id,
                'item_id' => $items->first()->id,
                'unit_id' => $units->first()?->id,
                'warehouse_id' => $warehouses->first()->id,
                'inventory_count' => 0.00,
                'quantity' => 100.00,
                'previous_quantity' => 0.00,
                'new_quantity' => 100.00,
                'unit_cost' => 250.00,
                'unit_price' => 300.00,
                'total_cost' => 25000.00,
                'total_price' => 30000.00,
                'notes' => 'Initial stock - Dell Inspiron laptops',
                'batch_number' => 'BATCH-LAP-001',
                'expiry_date' => null,
                'serial_number' => null,
                'location_code' => 'A-01',
                'shelf_number' => 'S-001',
                'bin_number' => 'B-001',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement1->id,
                'item_id' => $items->skip(1)->first()->id,
                'unit_id' => $units->first()?->id,
                'warehouse_id' => $warehouses->first()->id,
                'inventory_count' => 0.00,
                'quantity' => 50.00,
                'previous_quantity' => 0.00,
                'new_quantity' => 50.00,
                'unit_cost' => 150.00,
                'unit_price' => 200.00,
                'total_cost' => 7500.00,
                'total_price' => 10000.00,
                'notes' => 'Initial stock - Samsung monitors',
                'batch_number' => 'BATCH-MON-001',
                'expiry_date' => null,
                'serial_number' => null,
                'location_code' => 'A-02',
                'shelf_number' => 'S-002',
                'bin_number' => 'B-002',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Data for INV-MOV-002 (Outbound - Materials delivery)
        $movement2 = $movements->where('movement_number', 'INV-MOV-002')->first();
        if ($movement2 && $items->count() >= 3) {
            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement2->id,
                'item_id' => $items->skip(2)->first()->id,
                'unit_id' => $units->skip(1)->first()?->id ?? $units->first()?->id,
                'warehouse_id' => $warehouses->first()->id,
                'inventory_count' => 1000.00,
                'quantity' => -100.00, // Negative for outbound
                'previous_quantity' => 1000.00,
                'new_quantity' => 900.00,
                'unit_cost' => 3.50,
                'unit_price' => 4.50,
                'total_cost' => 350.00,
                'total_price' => 450.00,
                'notes' => 'Steel rods delivery for construction project',
                'batch_number' => 'BATCH-STEEL-002',
                'expiry_date' => null,
                'serial_number' => null,
                'location_code' => 'B-01',
                'shelf_number' => 'S-010',
                'bin_number' => 'B-010',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement2->id,
                'item_id' => $items->skip(3)->first()->id,
                'unit_id' => $units->first()?->id,
                'warehouse_id' => $warehouses->first()->id,
                'inventory_count' => 200.00,
                'quantity' => -50.00, // Negative for outbound
                'previous_quantity' => 200.00,
                'new_quantity' => 150.00,
                'unit_cost' => 25.00,
                'unit_price' => 35.00,
                'total_cost' => 1250.00,
                'total_price' => 1750.00,
                'notes' => 'White paint for interior finishing',
                'batch_number' => 'BATCH-PAINT-002',
                'expiry_date' => Carbon::now()->addYears(2),
                'serial_number' => null,
                'location_code' => 'C-01',
                'shelf_number' => 'S-015',
                'bin_number' => 'B-015',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Data for INV-MOV-003 (Transfer - Inter-warehouse transfer)
        $movement3 = $movements->where('movement_number', 'INV-MOV-003')->first();
        if ($movement3 && $items->count() >= 5) {
            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement3->id,
                'item_id' => $items->skip(4)->first()->id,
                'unit_id' => $units->first()?->id,
                'warehouse_id' => $warehouses->skip(1)->first()?->id ?? $warehouses->first()->id,
                'inventory_count' => 500.00,
                'quantity' => 75.00, // Positive for receiving warehouse
                'previous_quantity' => 500.00,
                'new_quantity' => 575.00,
                'unit_cost' => 15.00,
                'unit_price' => 20.00,
                'total_cost' => 1125.00,
                'total_price' => 1500.00,
                'notes' => 'A4 copy paper transfer from main warehouse',
                'batch_number' => 'BATCH-PAPER-003',
                'expiry_date' => null,
                'serial_number' => null,
                'location_code' => 'D-01',
                'shelf_number' => 'S-020',
                'bin_number' => 'B-020',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Data for INV-MOV-004 (Inventory Count - Monthly adjustment)
        $movement4 = $movements->where('movement_number', 'INV-MOV-004')->first();
        if ($movement4 && $items->count() >= 2) {
            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement4->id,
                'item_id' => $items->first()->id,
                'unit_id' => $units->first()?->id,
                'warehouse_id' => $warehouses->first()->id,
                'inventory_count' => 98.00, // Physical count
                'quantity' => -2.00, // Adjustment (system had 100, physical is 98)
                'previous_quantity' => 100.00,
                'new_quantity' => 98.00,
                'unit_cost' => 250.00,
                'unit_price' => 300.00,
                'total_cost' => 500.00,
                'total_price' => 600.00,
                'notes' => 'Monthly inventory count adjustment - 2 units missing',
                'batch_number' => 'BATCH-LAP-001',
                'expiry_date' => null,
                'serial_number' => null,
                'location_code' => 'A-01',
                'shelf_number' => 'S-001',
                'bin_number' => 'B-001',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Data for INV-MOV-005 (Manufacturing - Raw materials consumption)
        $movement5 = $movements->where('movement_number', 'INV-MOV-005')->first();
        if ($movement5 && $items->count() >= 3) {
            $movementData[] = [
                'company_id' => $company->id,
                'inventory_movement_id' => $movement5->id,
                'item_id' => $items->skip(2)->first()->id,
                'unit_id' => $units->skip(1)->first()?->id ?? $units->first()?->id,
                'warehouse_id' => $warehouses->skip(2)->first()?->id ?? $warehouses->first()->id,
                'inventory_count' => 900.00,
                'quantity' => -100.00, // Negative for consumption
                'previous_quantity' => 900.00,
                'new_quantity' => 800.00,
                'unit_cost' => 3.50,
                'unit_price' => 4.50,
                'total_cost' => 350.00,
                'total_price' => 450.00,
                'notes' => 'Steel rods consumed in production process',
                'batch_number' => 'BATCH-STEEL-002',
                'expiry_date' => null,
                'serial_number' => null,
                'location_code' => 'B-01',
                'shelf_number' => 'S-010',
                'bin_number' => 'B-010',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        foreach ($movementData as $data) {
            InventoryMovementData::firstOrCreate([
                'company_id' => $data['company_id'],
                'inventory_movement_id' => $data['inventory_movement_id'],
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id']
            ], $data);
        }

        $this->command->info('✅ Inventory Movement Data seeded successfully!');
    }
}
