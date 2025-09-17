<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\DepartmentWarehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class DepartmentWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Department Warehouses...');

        // Get required data
        $user = User::first();
        $company = Company::first();

        if (!$user || !$company) {
            $this->command->warn('⚠️  Required data not found. Please seed Users and Companies first.');
            return;
        }

        $departments = [
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-001',
                'department_name_ar' => 'قسم تقنية المعلومات',
                'department_name_en' => 'Information Technology Department',
                'description' => 'Responsible for IT infrastructure, software development, and technical support',
                'manager_name' => 'أحمد محمد الأحمد',
                'manager_phone' => '+966501234567',
                'manager_email' => 'ahmed.ahmed@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-002',
                'department_name_ar' => 'قسم المشاريع والإنشاءات',
                'department_name_en' => 'Projects and Construction Department',
                'description' => 'Manages construction projects, engineering, and project execution',
                'manager_name' => 'فاطمة علي السعد',
                'manager_phone' => '+966502234567',
                'manager_email' => 'fatima.alsaad@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-003',
                'department_name_ar' => 'قسم المشتريات والتوريد',
                'department_name_en' => 'Procurement and Supply Department',
                'description' => 'Handles purchasing, supplier management, and inventory procurement',
                'manager_name' => 'خالد عبدالله الغامدي',
                'manager_phone' => '+966503234567',
                'manager_email' => 'khalid.alghamdi@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-004',
                'department_name_ar' => 'قسم المالية والمحاسبة',
                'department_name_en' => 'Finance and Accounting Department',
                'description' => 'Manages financial operations, accounting, and budget control',
                'manager_name' => 'سارة أحمد الحربي',
                'manager_phone' => '+966504234567',
                'manager_email' => 'sara.alharbi@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-005',
                'department_name_ar' => 'قسم الموارد البشرية',
                'department_name_en' => 'Human Resources Department',
                'description' => 'Handles employee relations, recruitment, and HR policies',
                'manager_name' => 'محمد سعد الدوسري',
                'manager_phone' => '+966505234567',
                'manager_email' => 'mohammed.aldosari@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-006',
                'department_name_ar' => 'قسم المبيعات والتسويق',
                'department_name_en' => 'Sales and Marketing Department',
                'description' => 'Manages sales operations, marketing campaigns, and customer relations',
                'manager_name' => 'نورا عبدالرحمن القحطاني',
                'manager_phone' => '+966506234567',
                'manager_email' => 'nora.alqahtani@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-007',
                'department_name_ar' => 'قسم الجودة والسلامة',
                'department_name_en' => 'Quality and Safety Department',
                'description' => 'Ensures quality standards and workplace safety compliance',
                'manager_name' => 'عبدالله يوسف الشهري',
                'manager_phone' => '+966507234567',
                'manager_email' => 'abdullah.alshehri@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-008',
                'department_name_ar' => 'قسم الصيانة والخدمات',
                'department_name_en' => 'Maintenance and Services Department',
                'description' => 'Handles facility maintenance, equipment servicing, and general services',
                'manager_name' => 'ريم محمد العتيبي',
                'manager_phone' => '+966508234567',
                'manager_email' => 'reem.alotaibi@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-009',
                'department_name_ar' => 'قسم التخطيط والتطوير',
                'department_name_en' => 'Planning and Development Department',
                'description' => 'Strategic planning, business development, and organizational growth',
                'manager_name' => 'طارق فهد المالكي',
                'manager_phone' => '+966509234567',
                'manager_email' => 'tariq.almalki@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'department_number' => 'DEPT-010',
                'department_name_ar' => 'قسم الشؤون القانونية',
                'department_name_en' => 'Legal Affairs Department',
                'description' => 'Legal compliance, contract management, and regulatory affairs',
                'manager_name' => 'هند عبدالعزيز الراشد',
                'manager_phone' => '+966510234567',
                'manager_email' => 'hind.alrashid@company.com',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($departments as $deptData) {
            DepartmentWarehouse::firstOrCreate([
                'company_id' => $deptData['company_id'],
                'department_number' => $deptData['department_number']
            ], $deptData);
        }

        $this->command->info('✅ Department Warehouses seeded successfully!');
    }
}
