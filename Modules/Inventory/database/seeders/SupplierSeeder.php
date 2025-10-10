<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Suppliers\Models\Supplier;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Suppliers...');

        // Get required data
        $user = User::first();
        $company = Company::first();

        if (!$user || !$company) {
            $this->command->warn('⚠️  Required data not found. Please seed Users and Companies first.');
            return;
        }

        // Create simple suppliers that match the actual table structure
        $suppliers = [
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_number' => 'SUP-001',
                'first_name' => 'مورد تجريبي',
                'email' => 'supplier1@example.com',
                'phone' => '0599123456',
                'address_one' => 'رام الله، فلسطين',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'supplier_number' => 'SUP-002',
                'first_name' => 'مورد آخر',
                'email' => 'supplier2@example.com',
                'phone' => '0599234567',
                'address_one' => 'نابلس، فلسطين',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate([
                'company_id' => $supplierData['company_id'],
                'supplier_number' => $supplierData['supplier_number']
            ], $supplierData);
        }

        $this->command->info('✅ Suppliers seeded successfully!');
        return;

        // Old complex suppliers data (commented out)
        $oldSuppliers = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'supplier_code' => 'SUP-001',
                'supplier_name_ar' => 'شركة ديل للتقنيات',
                'supplier_name_en' => 'Dell Technologies',
                'contact_person' => 'Ahmed Al-Rashid',
                'phone' => '+966112345678',
                'mobile' => '+966501234567',
                'email' => 'ahmed@dell-saudi.com',
                'website' => 'www.dell.com/sa',
                'address' => 'King Fahd Road, Riyadh, Saudi Arabia',
                'tax_number' => '300123456789003',
                'commercial_register' => '1010123456',
                'credit_limit' => 500000.00,
                'payment_terms' => 30,
                'notes' => 'Leading technology supplier for laptops and computer equipment',
                'status' => 'active',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'supplier_code' => 'SUP-002',
                'supplier_name_ar' => 'شركة سامسونج الإلكترونيات',
                'supplier_name_en' => 'Samsung Electronics',
                'contact_person' => 'Fatima Al-Zahra',
                'phone' => '+966122345678',
                'mobile' => '+966502234567',
                'email' => 'fatima@samsung-ksa.com',
                'website' => 'www.samsung.com/sa',
                'address' => 'Tahlia Street, Jeddah, Saudi Arabia',
                'tax_number' => '300234567890003',
                'commercial_register' => '2020234567',
                'credit_limit' => 300000.00,
                'payment_terms' => 45,
                'notes' => 'Electronics supplier specializing in monitors and displays',
                'status' => 'active',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'supplier_code' => 'SUP-003',
                'supplier_name_ar' => 'شركة الراجحي للحديد',
                'supplier_name_en' => 'Al-Rajhi Steel Company',
                'contact_person' => 'Khalid Al-Saud',
                'phone' => '+966132345678',
                'mobile' => '+966503234567',
                'email' => 'khalid@rajhi-steel.com',
                'website' => 'www.rajhi-steel.com',
                'address' => 'Industrial City, Dammam, Saudi Arabia',
                'tax_number' => '300345678901003',
                'commercial_register' => '3030345678',
                'credit_limit' => 1000000.00,
                'payment_terms' => 60,
                'notes' => 'Major steel and construction materials supplier',
                'status' => 'active',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'supplier_code' => 'SUP-004',
                'supplier_name_ar' => 'مصنع الدهانات الوطني',
                'supplier_name_en' => 'National Paints Factory',
                'contact_person' => 'Sara Ahmed',
                'phone' => '+966142345678',
                'mobile' => '+966504234567',
                'email' => 'sara@nationalpaints.com',
                'website' => 'www.nationalpaints.com',
                'address' => 'Industrial Area, Mecca, Saudi Arabia',
                'tax_number' => '300456789012003',
                'commercial_register' => '4040456789',
                'credit_limit' => 150000.00,
                'payment_terms' => 30,
                'notes' => 'Paint and coating materials supplier with environmental compliance',
                'status' => 'active',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'supplier_code' => 'SUP-005',
                'supplier_name_ar' => 'مكتب بلس للوازم المكتبية',
                'supplier_name_en' => 'Office Plus Supplies',
                'contact_person' => 'Mohammed Al-Ghamdi',
                'phone' => '+966152345678',
                'mobile' => '+966505234567',
                'email' => 'mohammed@officeplus.com',
                'website' => 'www.officeplus.com',
                'address' => 'Business District, Medina, Saudi Arabia',
                'tax_number' => '300567890123003',
                'commercial_register' => '5050567890',
                'credit_limit' => 50000.00,
                'payment_terms' => 15,
                'notes' => 'Office supplies and stationery provider with fast delivery',
                'status' => 'active',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            // Convert supplier_code to supplier_number for the actual table
            $supplierData['supplier_number'] = $supplierData['supplier_code'];
            unset($supplierData['supplier_code']);

            Supplier::firstOrCreate([
                'company_id' => $supplierData['company_id'],
                'supplier_number' => $supplierData['supplier_number']
            ], $supplierData);
        }

        $this->command->info('✅ Suppliers seeded successfully!');
    }
}
