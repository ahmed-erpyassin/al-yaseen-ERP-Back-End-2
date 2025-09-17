<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\StockTransfer;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Carbon\Carbon;

class StockTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Stock Transfers...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $warehouses = Warehouse::all();

        if (!$user || !$company || $warehouses->count() < 2) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Companies, and at least 2 Warehouses first.');
            return;
        }

        $transfers = [
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'transfer_number' => 'TRF-001',
                'transfer_date' => Carbon::now()->subDays(25),
                'from_warehouse_id' => $warehouses->first()->id,
                'to_warehouse_id' => $warehouses->skip(1)->first()->id,
                'status' => 'received',
                'notes' => 'Transfer laptops to Jeddah warehouse for new office setup',
                'transfer_reason' => 'Office relocation and expansion',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(24),
                'received_by' => $user->id,
                'received_date' => Carbon::now()->subDays(22),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'transfer_number' => 'TRF-002',
                'transfer_date' => Carbon::now()->subDays(20),
                'from_warehouse_id' => $warehouses->skip(1)->first()->id,
                'to_warehouse_id' => $warehouses->skip(2)->first()?->id ?? $warehouses->first()->id,
                'status' => 'received',
                'notes' => 'Transfer construction materials to Dammam project site',
                'transfer_reason' => 'Project material requirements',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(19),
                'received_by' => $user->id,
                'received_date' => Carbon::now()->subDays(17),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'transfer_number' => 'TRF-003',
                'transfer_date' => Carbon::now()->subDays(15),
                'from_warehouse_id' => $warehouses->first()->id,
                'to_warehouse_id' => $warehouses->skip(3)->first()?->id ?? $warehouses->skip(1)->first()->id,
                'status' => 'in_transit',
                'notes' => 'Transfer office supplies to Mecca branch',
                'transfer_reason' => 'Branch office supply replenishment',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(14),
                'received_by' => null,
                'received_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'transfer_number' => 'TRF-004',
                'transfer_date' => Carbon::now()->subDays(10),
                'from_warehouse_id' => $warehouses->skip(2)->first()?->id ?? $warehouses->first()->id,
                'to_warehouse_id' => $warehouses->first()->id,
                'status' => 'draft',
                'notes' => 'Return unused paint materials to main warehouse',
                'transfer_reason' => 'Project completion - return excess materials',
                'approved_by' => null,
                'approved_at' => null,
                'received_by' => null,
                'received_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'transfer_number' => 'TRF-005',
                'transfer_date' => Carbon::now()->subDays(5),
                'from_warehouse_id' => $warehouses->first()->id,
                'to_warehouse_id' => $warehouses->skip(4)->first()?->id ?? $warehouses->skip(1)->first()->id,
                'status' => 'sent',
                'notes' => 'Emergency transfer of monitors for urgent client setup',
                'transfer_reason' => 'Urgent client requirements',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now()->subDays(4),
                'received_by' => null,
                'received_date' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($transfers as $transferData) {
            StockTransfer::firstOrCreate([
                'company_id' => $transferData['company_id'],
                'transfer_number' => $transferData['transfer_number']
            ], $transferData);
        }

        $this->command->info('✅ Stock Transfers seeded successfully!');
    }
}
