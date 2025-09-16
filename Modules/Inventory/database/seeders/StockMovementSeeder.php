<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $user = User::first();
        $company = Company::first();
        $items = Item::all();
        $units = Unit::all();
        $warehouses = Warehouse::all();

        if (!$user || !$company || $items->isEmpty() || $units->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Items, Units, and Warehouses first.');
            return;
        }

        $laptop = $items->where('code', 'LAP-001')->first();
        $monitor = $items->where('code', 'MON-001')->first();
        $steel = $items->where('code', 'STEEL-001')->first();
        $paint = $items->where('code', 'PAINT-001')->first();
        $paper = $items->where('code', 'PAPER-001')->first();

        $pieceUnit = $units->where('code', 'PCS')->first();
        $kgUnit = $units->where('code', 'KG')->first();
        $literUnit = $units->where('code', 'LTR')->first();
        $cartonUnit = $units->where('code', 'CTN')->first();

        $mainWarehouse = $warehouses->where('warehouse_number', 'WH-001')->first();
        $rawMaterialWarehouse = $warehouses->where('warehouse_number', 'WH-002')->first();
        $finishedGoodsWarehouse = $warehouses->where('warehouse_number', 'WH-003')->first();

        $stockMovements = [];

        // Initial stock movements (purchases)
        if ($laptop && $pieceUnit && $mainWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $mainWarehouse->id,
                'document_id' => null,
                'item_id' => $laptop->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'purchase',
                'movement_type' => 'in',
                'quantity' => 50.00,
                'unit_cost' => 2500.00,
                'total_cost' => 125000.00,
                'transaction_date' => Carbon::now()->subDays(30),
                'notes' => 'Initial stock purchase - Dell Laptops',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($monitor && $pieceUnit && $mainWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $mainWarehouse->id,
                'document_id' => null,
                'item_id' => $monitor->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'purchase',
                'movement_type' => 'in',
                'quantity' => 75.00,
                'unit_cost' => 450.00,
                'total_cost' => 33750.00,
                'transaction_date' => Carbon::now()->subDays(28),
                'notes' => 'Initial stock purchase - Samsung Monitors',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($steel && $kgUnit && $rawMaterialWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $rawMaterialWarehouse->id,
                'document_id' => null,
                'item_id' => $steel->id,
                'unit_id' => $kgUnit->id,
                'type' => 'purchase',
                'movement_type' => 'in',
                'quantity' => 5000.000,
                'unit_cost' => 3.50,
                'total_cost' => 17500.00,
                'transaction_date' => Carbon::now()->subDays(25),
                'notes' => 'Bulk purchase - Steel rods 12mm',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($paint && $literUnit && $rawMaterialWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $rawMaterialWarehouse->id,
                'document_id' => null,
                'item_id' => $paint->id,
                'unit_id' => $literUnit->id,
                'type' => 'purchase',
                'movement_type' => 'in',
                'quantity' => 500.000,
                'unit_cost' => 25.00,
                'total_cost' => 12500.00,
                'transaction_date' => Carbon::now()->subDays(22),
                'notes' => 'Stock replenishment - White wall paint',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($paper && $cartonUnit && $finishedGoodsWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $finishedGoodsWarehouse->id,
                'document_id' => null,
                'item_id' => $paper->id,
                'unit_id' => $cartonUnit->id,
                'type' => 'purchase',
                'movement_type' => 'in',
                'quantity' => 200.00,
                'unit_cost' => 120.00,
                'total_cost' => 24000.00,
                'transaction_date' => Carbon::now()->subDays(20),
                'notes' => 'Monthly stock - A4 copy paper',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Sales movements (outbound)
        if ($laptop && $pieceUnit && $mainWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $mainWarehouse->id,
                'document_id' => null,
                'item_id' => $laptop->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'sales',
                'movement_type' => 'out',
                'quantity' => 5.00,
                'unit_cost' => 2500.00,
                'total_cost' => 12500.00,
                'transaction_date' => Carbon::now()->subDays(15),
                'notes' => 'Sale to corporate client - 5 laptops',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($monitor && $pieceUnit && $mainWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $mainWarehouse->id,
                'document_id' => null,
                'item_id' => $monitor->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'sales',
                'movement_type' => 'out',
                'quantity' => 10.00,
                'unit_cost' => 450.00,
                'total_cost' => 4500.00,
                'transaction_date' => Carbon::now()->subDays(12),
                'notes' => 'Sale to office setup project',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($steel && $kgUnit && $rawMaterialWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $rawMaterialWarehouse->id,
                'document_id' => null,
                'item_id' => $steel->id,
                'unit_id' => $kgUnit->id,
                'type' => 'production',
                'movement_type' => 'out',
                'quantity' => 500.000,
                'unit_cost' => 3.50,
                'total_cost' => 1750.00,
                'transaction_date' => Carbon::now()->subDays(10),
                'notes' => 'Used in construction project - Building A',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($paint && $literUnit && $rawMaterialWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $rawMaterialWarehouse->id,
                'document_id' => null,
                'item_id' => $paint->id,
                'unit_id' => $literUnit->id,
                'type' => 'production',
                'movement_type' => 'out',
                'quantity' => 50.000,
                'unit_cost' => 25.00,
                'total_cost' => 1250.00,
                'transaction_date' => Carbon::now()->subDays(8),
                'notes' => 'Used for interior painting - Office renovation',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        if ($paper && $cartonUnit && $finishedGoodsWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $finishedGoodsWarehouse->id,
                'document_id' => null,
                'item_id' => $paper->id,
                'unit_id' => $cartonUnit->id,
                'type' => 'sales',
                'movement_type' => 'out',
                'quantity' => 25.00,
                'unit_cost' => 120.00,
                'total_cost' => 3000.00,
                'transaction_date' => Carbon::now()->subDays(5),
                'notes' => 'Bulk sale to printing company',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Adjustment movements
        if ($laptop && $pieceUnit && $mainWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $mainWarehouse->id,
                'document_id' => null,
                'item_id' => $laptop->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'adjustments',
                'movement_type' => 'out',
                'quantity' => 2.00,
                'unit_cost' => 2500.00,
                'total_cost' => 5000.00,
                'transaction_date' => Carbon::now()->subDays(3),
                'notes' => 'Damaged units - removed from inventory',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // Transfer movements
        if ($monitor && $pieceUnit && $mainWarehouse && $finishedGoodsWarehouse) {
            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $mainWarehouse->id,
                'document_id' => null,
                'item_id' => $monitor->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'transfer',
                'movement_type' => 'out',
                'quantity' => 15.00,
                'unit_cost' => 450.00,
                'total_cost' => 6750.00,
                'transaction_date' => Carbon::now()->subDays(2),
                'notes' => 'Transfer to finished goods warehouse',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            $stockMovements[] = [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_id' => $finishedGoodsWarehouse->id,
                'document_id' => null,
                'item_id' => $monitor->id,
                'unit_id' => $pieceUnit->id,
                'type' => 'transfer',
                'movement_type' => 'in',
                'quantity' => 15.00,
                'unit_cost' => 450.00,
                'total_cost' => 6750.00,
                'transaction_date' => Carbon::now()->subDays(2),
                'notes' => 'Received from main warehouse',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        foreach ($stockMovements as $movementData) {
            StockMovement::create($movementData);
        }

        $this->command->info('✅ Stock Movements seeded successfully!');
    }
}
