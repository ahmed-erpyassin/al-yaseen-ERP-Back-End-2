<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Suppliers\Models\Supplier;

class BomItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $company = Company::first();
        $branch = Branch::first();
        $user = User::first();

        if (!$company || !$user) {
            $this->command->error('❌ Company and User must exist before seeding BOM items');
            return;
        }

        // Get units
        $kgUnit = Unit::where('code', 'KG')->first();
        $literUnit = Unit::where('code', 'LTR')->first();
        $pieceUnit = Unit::where('code', 'PCS')->first();

        if (!$kgUnit || !$literUnit || !$pieceUnit) {
            $this->command->error('❌ Units (KG, LTR, PCS) must exist before seeding BOM items');
            return;
        }

        // Get items (we'll create some if they don't exist)
        $this->createItemsIfNeeded($company, $user, $kgUnit);

        // Get finished products and raw materials
        $breadItem = Item::where('name', 'خبز أبيض')->first();
        $cakeItem = Item::where('name', 'كيك شوكولاتة')->first();
        $pizzaItem = Item::where('name', 'بيتزا مارجريتا')->first();

        $flourItem = Item::where('name', 'دقيق أبيض')->first();
        $sugarItem = Item::where('name', 'سكر أبيض')->first();
        $butterItem = Item::where('name', 'زبدة')->first();
        $eggsItem = Item::where('name', 'بيض')->first();
        $milkItem = Item::where('name', 'حليب')->first();
        $yeastItem = Item::where('name', 'خميرة')->first();
        $saltItem = Item::where('name', 'ملح')->first();
        $chocolateItem = Item::where('name', 'شوكولاتة')->first();
        $cheeseItem = Item::where('name', 'جبنة موزاريلا')->first();
        $tomatoSauceItem = Item::where('name', 'صلصة طماطم')->first();

        // Get supplier
        $supplier = Supplier::first();

        $this->command->info('🔄 Creating BOM Items...');

        // ✅ BOM for Bread (خبز أبيض)
        if ($breadItem && $flourItem && $yeastItem && $saltItem) {
            $this->createBomItem([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $breadItem->id,
                'component_id' => $flourItem->id,
                'unit_id' => $kgUnit->id,
                'formula_number' => 'BOM-BREAD-001',
                'formula_name' => 'معادلة إنتاج الخبز الأبيض',
                'formula_description' => 'معادلة تصنيع الخبز الأبيض التقليدي',
                'quantity' => 2.5, // 2.5 kg flour per batch
                'required_quantity' => 2.5,
                'available_quantity' => 50.0,
                'consumed_quantity' => 2.4,
                'produced_quantity' => 20.0, // 20 pieces of bread
                'waste_quantity' => 0.1,
                'yield_percentage' => 96.0,
                'unit_cost' => 3.50,
                'total_cost' => 8.75,
                'component_type' => 'raw_material',
                'is_critical' => true,
                'sequence_order' => 1,
                'status' => 'active',
                'batch_size' => 20.0,
                'production_time_minutes' => 180,
                'preparation_time_minutes' => 30,
                'tolerance_percentage' => 5.0,
                'preferred_supplier_id' => $supplier?->id,
                'supplier_item_code' => 'FLOUR-001',
                'supplier_unit_price' => 3.50,
                'lead_time_days' => 7,
                'created_by' => $user->id,
            ]);

            $this->createBomItem([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $breadItem->id,
                'component_id' => $yeastItem->id,
                'unit_id' => $kgUnit->id,
                'formula_number' => 'BOM-BREAD-002',
                'formula_name' => 'خميرة للخبز الأبيض',
                'quantity' => 0.05, // 50g yeast per batch
                'required_quantity' => 0.05,
                'available_quantity' => 2.0,
                'consumed_quantity' => 0.05,
                'unit_cost' => 15.00,
                'total_cost' => 0.75,
                'component_type' => 'raw_material',
                'is_critical' => true,
                'sequence_order' => 2,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            $this->createBomItem([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $breadItem->id,
                'component_id' => $saltItem->id,
                'unit_id' => $kgUnit->id,
                'formula_number' => 'BOM-BREAD-003',
                'formula_name' => 'ملح للخبز الأبيض',
                'quantity' => 0.03, // 30g salt per batch
                'required_quantity' => 0.03,
                'available_quantity' => 5.0,
                'consumed_quantity' => 0.03,
                'unit_cost' => 2.00,
                'total_cost' => 0.06,
                'component_type' => 'raw_material',
                'is_critical' => false,
                'sequence_order' => 3,
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        // ✅ BOM for Chocolate Cake (كيك شوكولاتة)
        if ($cakeItem && $flourItem && $sugarItem && $eggsItem && $chocolateItem) {
            $this->createBomItem([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $cakeItem->id,
                'component_id' => $flourItem->id,
                'unit_id' => $kgUnit->id,
                'formula_number' => 'BOM-CAKE-001',
                'formula_name' => 'دقيق لكيك الشوكولاتة',
                'quantity' => 1.5,
                'required_quantity' => 1.5,
                'available_quantity' => 50.0,
                'consumed_quantity' => 1.5,
                'produced_quantity' => 8.0, // 8 pieces
                'unit_cost' => 3.50,
                'total_cost' => 5.25,
                'component_type' => 'raw_material',
                'is_critical' => true,
                'sequence_order' => 1,
                'status' => 'active',
                'batch_size' => 8.0,
                'production_time_minutes' => 120,
                'preparation_time_minutes' => 45,
                'created_by' => $user->id,
            ]);

            $this->createBomItem([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $cakeItem->id,
                'component_id' => $chocolateItem->id,
                'unit_id' => $kgUnit->id,
                'formula_number' => 'BOM-CAKE-002',
                'formula_name' => 'شوكولاتة للكيك',
                'quantity' => 0.3,
                'required_quantity' => 0.3,
                'available_quantity' => 10.0,
                'consumed_quantity' => 0.3,
                'unit_cost' => 25.00,
                'total_cost' => 7.50,
                'component_type' => 'raw_material',
                'is_critical' => true,
                'sequence_order' => 2,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            $this->createBomItem([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'item_id' => $cakeItem->id,
                'component_id' => $eggsItem->id,
                'unit_id' => $pieceUnit->id,
                'formula_number' => 'BOM-CAKE-003',
                'formula_name' => 'بيض للكيك',
                'quantity' => 4.0, // 4 eggs
                'required_quantity' => 4.0,
                'available_quantity' => 100.0,
                'consumed_quantity' => 4.0,
                'unit_cost' => 0.50,
                'total_cost' => 2.00,
                'component_type' => 'raw_material',
                'is_critical' => true,
                'sequence_order' => 3,
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('✅ BOM Items seeded successfully!');
        $this->command->info('📊 Created BOM items for bread and cake production');
    }

    /**
     * Create items if they don't exist
     */
    private function createItemsIfNeeded($company, $user, $defaultUnit)
    {
        $items = [
            // Finished Products
            ['name' => 'خبز أبيض', 'item_number' => 'FP-001', 'type' => 'finished'],
            ['name' => 'كيك شوكولاتة', 'item_number' => 'FP-002', 'type' => 'finished'],
            ['name' => 'بيتزا مارجريتا', 'item_number' => 'FP-003', 'type' => 'finished'],

            // Raw Materials
            ['name' => 'دقيق أبيض', 'item_number' => 'RM-001', 'type' => 'raw'],
            ['name' => 'سكر أبيض', 'item_number' => 'RM-002', 'type' => 'raw'],
            ['name' => 'زبدة', 'item_number' => 'RM-003', 'type' => 'raw'],
            ['name' => 'بيض', 'item_number' => 'RM-004', 'type' => 'raw'],
            ['name' => 'حليب', 'item_number' => 'RM-005', 'type' => 'raw'],
            ['name' => 'خميرة', 'item_number' => 'RM-006', 'type' => 'raw'],
            ['name' => 'ملح', 'item_number' => 'RM-007', 'type' => 'raw'],
            ['name' => 'شوكولاتة', 'item_number' => 'RM-008', 'type' => 'raw'],
            ['name' => 'جبنة موزاريلا', 'item_number' => 'RM-009', 'type' => 'raw'],
            ['name' => 'صلصة طماطم', 'item_number' => 'RM-010', 'type' => 'raw'],
        ];

        foreach ($items as $itemData) {
            if (!Item::where('name', $itemData['name'])->exists()) {
                Item::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'unit_id' => $defaultUnit->id, // Add required unit_id field
                    'item_number' => $itemData['item_number'],
                    'code' => $itemData['item_number'], // Add required code field
                    'name' => $itemData['name'],
                    'description' => 'مادة خام للإنتاج',
                    'type' => 'raw_material', // Add required type field
                    'balance' => 100.0,
                    'minimum_limit' => 10.0,
                    'maximum_limit' => 500.0,
                    'purchase_price' => rand(100, 5000) / 100,
                    'sale_price' => rand(200, 8000) / 100, // Use sale_price instead of selling_price
                    'active' => true, // Add active field
                    'created_by' => $user->id,
                ]);
            }
        }
    }

    /**
     * Create a BOM item with default values
     */
    private function createBomItem(array $data)
    {
        $defaults = [
            'formula_date' => now()->toDateString(),
            'formula_time' => now()->toTimeString(),
            'formula_datetime' => now(),
            'balance' => 0,
            'minimum_limit' => 0,
            'maximum_limit' => 0,
            'minimum_reorder_level' => 0,
            'selling_price' => 0,
            'purchase_price' => 0,
            'first_purchase_price' => 0,
            'second_purchase_price' => 0,
            'third_purchase_price' => 0,
            'first_selling_price' => 0,
            'second_selling_price' => 0,
            'third_selling_price' => 0,
            'actual_cost' => 0,
            'labor_cost' => 0,
            'operating_cost' => 0,
            'waste_cost' => 0,
            'final_cost' => 0,
            'material_cost' => 0,
            'overhead_cost' => 0,
            'total_production_cost' => 0,
            'cost_per_unit' => 0,
            'component_balance' => 0,
            'component_minimum_limit' => 0,
            'component_maximum_limit' => 0,
            'reorder_level' => 0,
            'is_optional' => false,
            'is_active' => true,
            'effective_from' => now()->toDateString(),
            'production_notes' => 'ملاحظات الإنتاج',
            'preparation_notes' => 'ملاحظات التحضير',
            'usage_instructions' => 'تعليمات الاستخدام',
            'quality_requirements' => 'متطلبات الجودة',
            'requires_inspection' => false,
            'supplier_item_code' => null,
            'supplier_unit_price' => 0,
            'lead_time_days' => 0,
        ];

        BomItem::create(array_merge($defaults, $data));
    }
}
