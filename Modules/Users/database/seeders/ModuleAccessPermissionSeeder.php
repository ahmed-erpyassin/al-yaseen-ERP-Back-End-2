<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class ModuleAccessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // قائمة الموديولات
        $modules = [
            'Users'                => 'المستخدمين',
            'Companies'            => 'الشركات والفروع',
            'Customers'            => 'العملاء',
            'Suppliers'            => 'الموردون',
            'HumanResources'       => 'الموارد البشرية',
            'FinancialAccounts'    => 'الحسابات المالية',
            'Inventory'            => 'المخزون',
            'Purchases'            => 'المشتريات',
            'Sales'                => 'المبيعات',
            'ProjectsManagement'   => 'إدارة المشاريع',
            'Billing'              => 'الفواتير',
        ];

        foreach ($modules as $key => $label) {
            Permission::firstOrCreate([
                'name' => 'access_' . Str::snake($key),
            ], [
                'guard_name' => 'api', // 🔥 مهم
                'group' => $key,
                'label' => 'الوصول إلى ' . $label,
            ]);
        }
        $this->command->info("✔ تم توليد صلاحيات الوصول للموديولات بنجاح.");
    }
}
