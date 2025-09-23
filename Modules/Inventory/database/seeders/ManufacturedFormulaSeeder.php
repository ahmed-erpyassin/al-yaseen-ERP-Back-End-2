<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\ManufacturedFormulaModel;
use Modules\Inventory\Models\ManufacturedFormulaRawMaterialModel;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Supplier;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Carbon\Carbon;

class ManufacturedFormulaSeeder extends Seeder
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

        // Get or create a branch for the company
        $branch = Branch::firstOrCreate([
            'company_id' => $company->id,
            'code' => 'BR-001'
        ], [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'currency_id' => null,
            'manager_id' => $user->id,
            'code' => 'BR-001',
            'name' => 'Main Branch',
            'address' => 'Main Office Address',
            'landline' => '+966112345678',
            'mobile' => '+966501234567',
            'email' => 'branch@company.com',
            'tax_number' => 'TAX-001',
            'timezone' => 'Asia/Riyadh',
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Get suppliers
        $suppliers = Supplier::all();
        $supplier1 = $suppliers->where('supplier_code', 'SUP-001')->first() ?? $suppliers->first();
        $supplier2 = $suppliers->where('supplier_code', 'SUP-002')->first() ?? $suppliers->skip(1)->first();
        $supplier3 = $suppliers->where('supplier_code', 'SUP-003')->first() ?? $suppliers->skip(2)->first();

        // Get specific items and units
        $finishedProduct = $items->where('name', 'like', '%Laptop%')->first() ?? $items->first();
        $rawMaterial1 = $items->where('name', 'like', '%Steel%')->first() ?? $items->skip(1)->first();
        $rawMaterial2 = $items->where('name', 'like', '%Paint%')->first() ?? $items->skip(2)->first();
        $rawMaterial3 = $items->where('name', 'like', '%Paper%')->first() ?? $items->skip(3)->first();

        $pieceUnit = $units->where('name', 'piece')->first() ?? $units->first();
        $kgUnit = $units->where('name', 'kilo')->first() ?? $units->skip(1)->first();
        $literUnit = $units->where('name', 'liter')->first() ?? $units->skip(2)->first();

        $rawMaterialWarehouse = $warehouses->where('name', 'like', '%Raw%')->first() ?? $warehouses->first();
        $finishedGoodsWarehouse = $warehouses->where('name', 'like', '%Finished%')->first() ?? $warehouses->skip(1)->first();

        // Clear existing data
        $this->command->info('🗑️  Clearing existing manufactured formulas...');
        ManufacturedFormulaRawMaterialModel::truncate();
        ManufacturedFormulaModel::truncate();

        $this->command->info('🏭 Creating manufactured formulas...');

        // Create manufactured formulas
        $formulas = [];
        
        // Formula 1: Laptop Assembly
        $formula1 = ManufacturedFormulaModel::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'formula_number' => 'MF-001',
            'item_id' => $finishedProduct->id,
            'manufacturing_duration' => '2 days',
            'manufacturing_duration_unit' => 'days',
            'manufacturing_duration_value' => 2,
            'consumed_quantity' => 10.0000,
            'produced_quantity' => 1.0000,
            'raw_materials_warehouse_id' => $rawMaterialWarehouse->id,
            'finished_product_warehouse_id' => $finishedGoodsWarehouse->id,
            'status' => 'active',
            'is_active' => true,
            'total_raw_material_cost' => 2000.00,
            'labor_cost' => 300.00,
            'overhead_cost' => 200.00,
            'total_manufacturing_cost' => 2500.00,
            'cost_per_unit' => 2500.00,
            'sale_price' => 3500.00,
            'purchase_price' => 2500.00,
            'formula_date' => Carbon::now()->format('Y-m-d'),
            'formula_time' => Carbon::now()->format('H:i:s'),
            'formula_datetime' => Carbon::now(),
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(3),
            'expected_completion_date' => Carbon::now()->addDays(3),
            'notes' => 'Standard laptop assembly formula',
            'batch_number' => 'BATCH-001',
            'production_order_number' => 'PO-001',
            'requires_quality_check' => true,
            'quality_requirements' => 'Visual inspection and functional testing required',
            'quality_status' => 'pending',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $formulas[] = $formula1;
        
        $this->command->info('✅ Created formula: ' . $formula1->formula_number);

        // Create raw materials for Formula 1
        $this->createRawMaterials($formula1, [
            [
                'item' => $rawMaterial1,
                'unit' => $kgUnit,
                'warehouse' => $rawMaterialWarehouse,
                'supplier' => $supplier3, // Steel supplier
                'consumed_quantity' => 5.0000,
                'available_quantity' => 100.0000,
                'required_quantity' => 5.0000,
                'unit_cost' => 150.00,
                'total_cost' => 750.00,
                'sale_price' => 200.00,
                'purchase_price' => 150.00,
                'is_critical' => true,
                'sequence_order' => 1,
                'supplier_item_code' => 'STEEL-12MM-001',
                'supplier_unit_price' => 145.00,
                'lead_time_days' => 7,
                'notes' => 'Primary structural component',
            ],
            [
                'item' => $rawMaterial2,
                'unit' => $literUnit,
                'warehouse' => $rawMaterialWarehouse,
                'supplier' => $supplier2, // Paint supplier (Samsung - but we'll use for paint)
                'consumed_quantity' => 2.0000,
                'available_quantity' => 50.0000,
                'required_quantity' => 2.0000,
                'unit_cost' => 25.00,
                'total_cost' => 50.00,
                'sale_price' => 35.00,
                'purchase_price' => 25.00,
                'is_critical' => false,
                'sequence_order' => 2,
                'supplier_item_code' => 'PAINT-WHITE-001',
                'supplier_unit_price' => 23.50,
                'lead_time_days' => 3,
                'notes' => 'Finishing material',
            ],
        ], $user, $company, $branch);

        // Formula 2: Steel Product Manufacturing
        $formula2 = ManufacturedFormulaModel::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'formula_number' => 'MF-002',
            'item_id' => $rawMaterial1->id,
            'manufacturing_duration' => '1 week',
            'manufacturing_duration_unit' => 'weeks',
            'manufacturing_duration_value' => 1,
            'consumed_quantity' => 20.0000,
            'produced_quantity' => 15.0000,
            'raw_materials_warehouse_id' => $rawMaterialWarehouse->id,
            'finished_product_warehouse_id' => $finishedGoodsWarehouse->id,
            'status' => 'draft',
            'is_active' => true,
            'total_raw_material_cost' => 1500.00,
            'labor_cost' => 500.00,
            'overhead_cost' => 300.00,
            'total_manufacturing_cost' => 2300.00,
            'cost_per_unit' => 153.33,
            'sale_price' => 200.00,
            'purchase_price' => 153.33,
            'formula_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'formula_time' => Carbon::now()->format('H:i:s'),
            'formula_datetime' => Carbon::now()->subDays(5),
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(14),
            'expected_completion_date' => Carbon::now()->addDays(14),
            'notes' => 'Steel processing and finishing',
            'batch_number' => 'BATCH-002',
            'production_order_number' => 'PO-002',
            'requires_quality_check' => true,
            'quality_requirements' => 'Strength testing and dimensional accuracy check',
            'quality_status' => 'not_required',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $formulas[] = $formula2;
        $this->command->info('✅ Created formula: ' . $formula2->formula_number);

        // Create raw materials for Formula 2
        $this->createRawMaterials($formula2, [
            [
                'item' => $rawMaterial2,
                'unit' => $literUnit,
                'warehouse' => $rawMaterialWarehouse,
                'supplier' => $supplier2, // Paint supplier
                'consumed_quantity' => 10.0000,
                'available_quantity' => 200.0000,
                'required_quantity' => 10.0000,
                'unit_cost' => 25.00,
                'total_cost' => 250.00,
                'sale_price' => 35.00,
                'purchase_price' => 25.00,
                'is_critical' => true,
                'sequence_order' => 1,
                'supplier_item_code' => 'PAINT-COAT-002',
                'supplier_unit_price' => 24.00,
                'lead_time_days' => 5,
                'notes' => 'Coating material for steel',
            ],
            [
                'item' => $rawMaterial3,
                'unit' => $pieceUnit,
                'warehouse' => $rawMaterialWarehouse,
                'supplier' => $supplier1, // Dell supplier (for packaging)
                'consumed_quantity' => 5.0000,
                'available_quantity' => 1000.0000,
                'required_quantity' => 5.0000,
                'unit_cost' => 120.00,
                'total_cost' => 600.00,
                'sale_price' => 150.00,
                'purchase_price' => 120.00,
                'is_critical' => false,
                'sequence_order' => 2,
                'supplier_item_code' => 'PKG-PAPER-003',
                'supplier_unit_price' => 115.00,
                'lead_time_days' => 2,
                'notes' => 'Packaging material',
            ],
        ], $user, $company, $branch);

        $this->command->info('✅ Manufactured formulas seeded successfully!');
        $this->command->info("📊 Total formulas created: " . count($formulas));
    }

    /**
     * Create raw materials for a manufactured formula.
     */
    private function createRawMaterials($formula, $rawMaterialsData, $user, $company, $branch)
    {
        foreach ($rawMaterialsData as $data) {
            $rawMaterial = ManufacturedFormulaRawMaterialModel::create([
                'company_id' => $company->id,
                'manufactured_formula_id' => $formula->id,
                'item_id' => $data['item']->id,
                'unit_id' => $data['unit']->id,
                'warehouse_id' => $data['warehouse']->id,
                'consumed_quantity' => $data['consumed_quantity'],
                'available_quantity' => $data['available_quantity'],
                'required_quantity' => $data['required_quantity'],
                'shortage_quantity' => max(0, $data['required_quantity'] - $data['available_quantity']),
                'unit_cost' => $data['unit_cost'],
                'total_cost' => $data['total_cost'],
                'sale_price' => $data['sale_price'],
                'purchase_price' => $data['purchase_price'],
                'is_available' => $data['available_quantity'] >= $data['required_quantity'],
                'is_sufficient' => $data['available_quantity'] >= $data['required_quantity'],
                'availability_status' => $data['available_quantity'] >= $data['required_quantity'] ? 'available' : 'insufficient',
                'is_critical' => $data['is_critical'],
                'is_optional' => !$data['is_critical'],
                'sequence_order' => $data['sequence_order'],
                'quality_specifications' => 'Standard quality specifications - Grade A materials required',
                'tolerance_percentage' => $data['is_critical'] ? 2.00 : 5.00, // Critical materials have lower tolerance
                'preferred_supplier_id' => $data['supplier']->id,
                'supplier_item_code' => $data['supplier_item_code'],
                'supplier_unit_price' => $data['supplier_unit_price'],
                'lead_time_days' => $data['lead_time_days'],
                'usage_instructions' => 'Use in sequence order ' . $data['sequence_order'] . '. ' . $data['notes'],
                'handling_notes' => 'Handle with care according to safety protocols. Store in dry conditions.',
                'safety_notes' => 'Follow standard safety procedures. Use protective equipment when handling.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->command->info('  ✅ Added raw material: ' . $data['item']->name . ' (Supplier: ' . $data['supplier']->supplier_name_en . ')');
        }
    }
}
