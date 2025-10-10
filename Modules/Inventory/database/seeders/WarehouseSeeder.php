<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Warehouse;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\HumanResources\Models\Employee;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $user = User::first();
        $company = Company::first();
        // Try to get first employee, but handle if Employee model doesn't exist or has no soft deletes
        try {
            $employee = Employee::withoutGlobalScopes()->first();
        } catch (\Exception $e) {
            $employee = null;
        }

        if (!$user || !$company) {
            $this->command->warn('⚠️  Users or Companies not found. Please seed Users and Companies modules first.');
            return;
        }

        $warehouses = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_number' => 'WH-001',
                'name' => 'المستودع الرئيسي',
                'address' => 'الرياض، المملكة العربية السعودية',
                'description' => 'المستودع الرئيسي لتخزين جميع المواد والمنتجات',
                'warehouse_data' => [
                    'capacity' => '10000 متر مربع',
                    'zones' => ['منطقة A', 'منطقة B', 'منطقة C'],
                    'temperature_controlled' => true,
                    'security_level' => 'high'
                ],
                'warehouse_keeper_id' => $employee?->id,
                'warehouse_keeper_employee_number' => $employee?->employee_number ?? 'EMP-001',
                'warehouse_keeper_employee_name' => $employee?->name ?? 'أحمد محمد',
                'phone_number' => '+966112345678',
                'fax_number' => '+966112345679',
                'mobile' => '+966501234567',
                'sales_account_id' => null,
                'purchase_account_id' => null,
                'location' => 'الرياض',
                'department_warehouse_id' => null,
                'inventory_valuation_method' => 'FIFO',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_number' => 'WH-002',
                'name' => 'مستودع المواد الخام',
                'address' => 'جدة، المملكة العربية السعودية',
                'description' => 'مستودع مخصص لتخزين المواد الخام والمكونات',
                'warehouse_data' => [
                    'capacity' => '5000 متر مربع',
                    'zones' => ['منطقة المواد الخام', 'منطقة الفحص'],
                    'temperature_controlled' => false,
                    'security_level' => 'medium'
                ],
                'warehouse_keeper_id' => $employee?->id,
                'warehouse_keeper_employee_number' => $employee?->employee_number ?? 'EMP-002',
                'warehouse_keeper_employee_name' => $employee?->name ?? 'فاطمة علي',
                'phone_number' => '+966122345678',
                'fax_number' => '+966122345679',
                'mobile' => '+966502234567',
                'sales_account_id' => null,
                'purchase_account_id' => null,
                'location' => 'جدة',
                'department_warehouse_id' => null,
                'inventory_valuation_method' => 'LIFO',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_number' => 'WH-003',
                'name' => 'مستودع المنتجات النهائية',
                'address' => 'الدمام، المملكة العربية السعودية',
                'description' => 'مستودع للمنتجات النهائية الجاهزة للشحن',
                'warehouse_data' => [
                    'capacity' => '7500 متر مربع',
                    'zones' => ['منطقة التخزين', 'منطقة التعبئة', 'منطقة الشحن'],
                    'temperature_controlled' => true,
                    'security_level' => 'high'
                ],
                'warehouse_keeper_id' => $employee?->id,
                'warehouse_keeper_employee_number' => $employee?->employee_number ?? 'EMP-003',
                'warehouse_keeper_employee_name' => $employee?->name ?? 'خالد السعد',
                'phone_number' => '+966132345678',
                'fax_number' => '+966132345679',
                'mobile' => '+966503234567',
                'sales_account_id' => null,
                'purchase_account_id' => null,
                'location' => 'الدمام',
                'department_warehouse_id' => null,
                'inventory_valuation_method' => 'FIFO',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_number' => 'WH-004',
                'name' => 'مستودع قطع الغيار',
                'address' => 'مكة المكرمة، المملكة العربية السعودية',
                'description' => 'مستودع مخصص لقطع الغيار والصيانة',
                'warehouse_data' => [
                    'capacity' => '2000 متر مربع',
                    'zones' => ['قطع غيار كهربائية', 'قطع غيار ميكانيكية'],
                    'temperature_controlled' => false,
                    'security_level' => 'medium'
                ],
                'warehouse_keeper_id' => $employee?->id,
                'warehouse_keeper_employee_number' => $employee?->employee_number ?? 'EMP-004',
                'warehouse_keeper_employee_name' => $employee?->name ?? 'سارة أحمد',
                'phone_number' => '+966142345678',
                'fax_number' => '+966142345679',
                'mobile' => '+966504234567',
                'sales_account_id' => null,
                'purchase_account_id' => null,
                'location' => 'مكة المكرمة',
                'department_warehouse_id' => null,
                'inventory_valuation_method' => 'Average',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_number' => 'WH-005',
                'name' => 'مستودع المرتجعات',
                'address' => 'المدينة المنورة، المملكة العربية السعودية',
                'description' => 'مستودع للمنتجات المرتجعة والمعيبة',
                'warehouse_data' => [
                    'capacity' => '1500 متر مربع',
                    'zones' => ['منطقة الفحص', 'منطقة الإصلاح', 'منطقة التخلص'],
                    'temperature_controlled' => false,
                    'security_level' => 'low'
                ],
                'warehouse_keeper_id' => $employee?->id,
                'warehouse_keeper_employee_number' => $employee?->employee_number ?? 'EMP-005',
                'warehouse_keeper_employee_name' => $employee?->name ?? 'محمد الغامدي',
                'phone_number' => '+966152345678',
                'fax_number' => '+966152345679',
                'mobile' => '+966505234567',
                'sales_account_id' => null,
                'purchase_account_id' => null,
                'location' => 'المدينة المنورة',
                'department_warehouse_id' => null,
                'inventory_valuation_method' => 'FIFO',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'warehouse_number' => 'WH-006',
                'name' => 'مستودع التبريد',
                'address' => 'الطائف، المملكة العربية السعودية',
                'description' => 'مستودع مبرد للمنتجات الحساسة للحرارة',
                'warehouse_data' => [
                    'capacity' => '3000 متر مربع',
                    'zones' => ['منطقة التبريد العادي', 'منطقة التجميد'],
                    'temperature_controlled' => true,
                    'security_level' => 'high',
                    'temperature_range' => '-18°C to +4°C'
                ],
                'warehouse_keeper_id' => $employee?->id,
                'warehouse_keeper_employee_number' => $employee?->employee_number ?? 'EMP-006',
                'warehouse_keeper_employee_name' => $employee?->name ?? 'نورا الحربي',
                'phone_number' => '+966162345678',
                'fax_number' => '+966162345679',
                'mobile' => '+966506234567',
                'sales_account_id' => null,
                'purchase_account_id' => null,
                'location' => 'الطائف',
                'department_warehouse_id' => null,
                'inventory_valuation_method' => 'FIFO',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            // Fix inventory_valuation_method values to match enum
            if (isset($warehouseData['inventory_valuation_method'])) {
                if ($warehouseData['inventory_valuation_method'] === 'FIFO') {
                    $warehouseData['inventory_valuation_method'] = 'first_purchase_price';
                } elseif ($warehouseData['inventory_valuation_method'] === 'LIFO') {
                    $warehouseData['inventory_valuation_method'] = 'second_purchase_price';
                } elseif ($warehouseData['inventory_valuation_method'] === 'Average') {
                    $warehouseData['inventory_valuation_method'] = 'natural_division';
                }
            }

            Warehouse::firstOrCreate([
                'company_id' => $warehouseData['company_id'],
                'warehouse_number' => $warehouseData['warehouse_number']
            ], $warehouseData);
        }

        $this->command->info('✅ Warehouses seeded successfully!');
    }
}
