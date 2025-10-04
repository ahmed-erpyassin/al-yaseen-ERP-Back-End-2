<?php

namespace Modules\Suppliers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Suppliers\Models\Supplier;
use App\Models\User;

class SampleSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user for testing
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        // Create test suppliers
        $suppliers = [
            [
                'user_id' => $user->id,
                'company_id' => $user->company_id ?? 1,
                'branch_id' => 1,
                'currency_id' => 1,
                'first_name' => 'Ahmed',
                'second_name' => 'Mohamed',
                'supplier_name_ar' => 'أحمد محمد للتوريدات',
                'supplier_name_en' => 'Ahmed Mohamed Supplies',
                'supplier_code' => 'SUP001',
                'supplier_number' => 'SUPP20250001',
                'email' => 'ahmed@example.com',
                'phone' => '01234567890',
                'mobile' => '01234567890',
                'address_one' => 'Cairo, Egypt',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company_id ?? 1,
                'branch_id' => 1,
                'currency_id' => 1,
                'first_name' => 'Fatima',
                'second_name' => 'Ali',
                'supplier_name_ar' => 'فاطمة علي للمواد',
                'supplier_name_en' => 'Fatima Ali Materials',
                'supplier_code' => 'SUP002',
                'supplier_number' => 'SUPP20250002',
                'email' => 'fatima@example.com',
                'phone' => '01234567891',
                'mobile' => '01234567891',
                'address_one' => 'Alexandria, Egypt',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company_id ?? 1,
                'branch_id' => 1,
                'currency_id' => 1,
                'first_name' => 'Omar',
                'second_name' => 'Hassan',
                'supplier_name_ar' => 'عمر حسن للخدمات',
                'supplier_name_en' => 'Omar Hassan Services',
                'supplier_code' => 'SUP003',
                'supplier_number' => 'SUPP20250003',
                'email' => 'omar@example.com',
                'phone' => '01234567892',
                'mobile' => '01234567892',
                'address_one' => 'Giza, Egypt',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        $this->command->info('Sample suppliers created successfully!');
    }
}
