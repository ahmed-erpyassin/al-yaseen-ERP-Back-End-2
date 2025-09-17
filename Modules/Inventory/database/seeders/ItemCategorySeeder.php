<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\ItemCategory;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Item Categories...');

        // Get required data
        $user = User::first();
        $company = Company::first();

        if (!$user || !$company) {
            $this->command->warn('⚠️  Required data not found. Please seed Users and Companies first.');
            return;
        }

        $categories = [
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-001',
                'category_name_ar' => 'الإلكترونيات',
                'category_name_en' => 'Electronics',
                'description' => 'Electronic devices and components - الأجهزة الإلكترونية والمكونات',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-002',
                'category_name_ar' => 'أجهزة الكمبيوتر',
                'category_name_en' => 'Computers',
                'description' => 'Laptops, desktops, and computer accessories - أجهزة الكمبيوتر المحمولة والمكتبية والملحقات',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-003',
                'category_name_ar' => 'الشاشات',
                'category_name_en' => 'Monitors',
                'description' => 'Computer monitors and displays - شاشات الكمبيوتر وأجهزة العرض',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-004',
                'category_name_ar' => 'مواد البناء',
                'category_name_en' => 'Construction Materials',
                'description' => 'Building and construction materials - مواد البناء والتشييد',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-005',
                'category_name_ar' => 'الحديد والمعادن',
                'category_name_en' => 'Steel & Metal',
                'description' => 'Steel rods, metal sheets, and metal products - حديد التسليح والصفائح المعدنية والمنتجات المعدنية',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-006',
                'category_name_ar' => 'الدهانات والطلاءات',
                'category_name_en' => 'Paint & Coatings',
                'description' => 'Paints, primers, and protective coatings - الدهانات والبرايمر والطلاءات الواقية',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'category_code' => 'CAT-007',
                'category_name_ar' => 'اللوازم المكتبية',
                'category_name_en' => 'Office Supplies',
                'description' => 'Paper, stationery, and office consumables - الورق والقرطاسية واللوازم المكتبية',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($categories as $categoryData) {
            ItemCategory::firstOrCreate([
                'company_id' => $categoryData['company_id'],
                'category_code' => $categoryData['category_code']
            ], $categoryData);
        }

        $this->command->info('✅ Item Categories seeded successfully!');
    }
}
