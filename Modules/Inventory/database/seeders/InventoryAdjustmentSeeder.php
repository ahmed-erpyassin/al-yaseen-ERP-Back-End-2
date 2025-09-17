<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\InventoryAdjustment;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryItem;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Carbon\Carbon;

class InventoryAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Inventory Adjustments...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $warehouses = Warehouse::all();

        if (!$user || !$company || $warehouses->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Companies, and Warehouses first.');
            return;
        }

        $adjustments = [
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'warehouse_id' => $warehouses->first()->id,
                'adjustment_number' => 'ADJ-001',
                'adjustment_date' => Carbon::now()->subDays(30),
                'adjustment_type' => 'decrease',
                'reason' => 'damaged',
                'status' => 'approved',
                'notes' => 'Monthly inventory adjustment for damaged items found during inspection',
                'reason_description' => 'Several laptops found with water damage after office flooding incident',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(29),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'warehouse_id' => $warehouses->skip(1)->first()?->id ?? $warehouses->first()->id,
                'adjustment_number' => 'ADJ-002',
                'adjustment_date' => Carbon::now()->subDays(20),
                'adjustment_type' => 'increase',
                'reason' => 'found',
                'status' => 'approved',
                'notes' => 'Items found during warehouse reorganization',
                'reason_description' => 'Additional monitors found in storage room during cleanup',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(19),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'warehouse_id' => $warehouses->skip(2)->first()?->id ?? $warehouses->first()->id,
                'adjustment_number' => 'ADJ-003',
                'adjustment_date' => Carbon::now()->subDays(15),
                'adjustment_type' => 'decrease',
                'reason' => 'expired',
                'status' => 'approved',
                'notes' => 'Expired paint materials disposal',
                'reason_description' => 'Paint materials exceeded shelf life and had to be disposed of',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(14),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'warehouse_id' => $warehouses->first()->id,
                'adjustment_number' => 'ADJ-004',
                'adjustment_date' => Carbon::now()->subDays(10),
                'adjustment_type' => 'recount',
                'reason' => 'recount',
                'status' => 'approved',
                'notes' => 'Quarterly physical inventory count adjustment',
                'reason_description' => 'Discrepancies found during quarterly physical inventory count',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(9),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'warehouse_id' => $warehouses->skip(1)->first()?->id ?? $warehouses->first()->id,
                'adjustment_number' => 'ADJ-005',
                'adjustment_date' => Carbon::now()->subDays(5),
                'adjustment_type' => 'decrease',
                'reason' => 'lost',
                'status' => 'draft',
                'notes' => 'Missing items during inventory check - pending investigation',
                'reason_description' => 'Several items missing from designated locations, investigation ongoing',
                'approved_by' => null,
                'approved_at' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($adjustments as $adjustmentData) {
            InventoryAdjustment::firstOrCreate([
                'company_id' => $adjustmentData['company_id'],
                'adjustment_number' => $adjustmentData['adjustment_number']
            ], $adjustmentData);
        }

        $this->command->info('✅ Inventory Adjustments seeded successfully!');
    }
}
