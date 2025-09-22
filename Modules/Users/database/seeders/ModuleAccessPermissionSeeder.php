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
            'Users'               =>  'users',
            'Companies'            => 'companies',
            'Customers'            => 'customers',
            'Suppliers'            => 'suppliers',
            'HumanResources'       => 'human resources',
            'FinancialAccounts'    => 'financial accounts',
            'Inventory'            => 'inventory',
            'Purchases'            => 'purchases',
            'Sales'                => 'sales',
            'ProjectsManagement'   => 'projects management',
            'Billing'              => 'billing',
        ];

        foreach ($modules as $key => $label) {
            foreach (['web', 'api'] as $guard) {
                Permission::firstOrCreate([
                    'name'       => 'access_' . Str::snake($key) . '_' . $guard,
                    'guard_name' => $guard,
                ], [
                    'group' => $key,
                    'label' => 'Access to ' . $label . ' Module ' . strtoupper($guard),
                ]);
            }
        }

        $this->command->info("✔ تم توليد صلاحيات الوصول للموديولات بنجاح.");
    }
}
