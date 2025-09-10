<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies to seed item types for each
        $companies = DB::table('companies')->pluck('id');

        $systemItemTypes = [
            [
                'code' => 'service',
                'name' => 'Service',
                'name_ar' => 'خدمة',
                'description' => 'Service items that are provided to customers',
                'description_ar' => 'عناصر الخدمة التي يتم تقديمها للعملاء',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'goods',
                'name' => 'Goods',
                'name_ar' => 'بضائع',
                'description' => 'Physical goods and products',
                'description_ar' => 'البضائع والمنتجات المادية',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'work',
                'name' => 'Work',
                'name_ar' => 'عمل',
                'description' => 'Work-related items and labor',
                'description_ar' => 'العناصر المتعلقة بالعمل والعمالة',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'asset',
                'name' => 'Asset',
                'name_ar' => 'أصل',
                'description' => 'Fixed assets and equipment',
                'description_ar' => 'الأصول الثابتة والمعدات',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'transfer',
                'name' => 'Transfer',
                'name_ar' => 'تحويل',
                'description' => 'Transfer items between locations',
                'description_ar' => 'نقل العناصر بين المواقع',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'minimum',
                'name' => 'Minimum',
                'name_ar' => 'حد أدنى',
                'description' => 'Minimum stock level items',
                'description_ar' => 'عناصر الحد الأدنى لمستوى المخزون',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($companies as $companyId) {
            foreach ($systemItemTypes as $itemType) {
                DB::table('item_types')->insert([
                    'company_id' => $companyId,
                    'code' => $itemType['code'],
                    'name' => $itemType['name'],
                    'name_ar' => $itemType['name_ar'],
                    'description' => $itemType['description'],
                    'description_ar' => $itemType['description_ar'],
                    'is_system' => $itemType['is_system'],
                    'is_active' => $itemType['is_active'],
                    'sort_order' => $itemType['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
