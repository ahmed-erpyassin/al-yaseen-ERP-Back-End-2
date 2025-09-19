<?php

namespace Modules\Customers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Customers\Models\Customer;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Region;
use Modules\Companies\Models\City;
use Modules\FinancialAccounts\Models\Currency;
// use App\Models\Employee;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required dependencies
        $user = User::first();
        $company = Company::first();
        $country = Country::first();
        $region = Region::first();
        $city = City::first();
        $currency = Currency::first();
        // $employee = Employee::first();

        // Create default values if not found
        if (!$user) {
            $user = User::create([
                'first_name' => 'Admin',
                'second_name' => 'User',
                'email' => 'admin@example.com',
                'phone' => '0599123456',
                'password' => bcrypt('password'),
                'status' => 'active',
                'type' => 'admin',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);
        }

        if (!$company) {
            $company = Company::create([
                'user_id' => $user->id,
                'title' => 'Default Company',
                'email' => 'company@example.com',
                'status' => 'active',
            ]);
        }

        if (!$country) {
            $country = Country::create([
                'code' => 'PS',
                'name' => 'فلسطين',
                'name_en' => 'Palestine',
                'phone_code' => '+970',
            ]);
        }

        if (!$region) {
            $region = Region::create([
                'country_id' => $country->id,
                'name' => 'قطاع غزة',
                'name_en' => 'Gaza Strip',
            ]);
        }

        if (!$city) {
            $city = City::create([
                'country_id' => $country->id,
                'region_id' => $region->id,
                'name' => 'غزة',
                'name_en' => 'Gaza',
            ]);
        }

        if (!$currency) {
            $currency = Currency::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'ILS',
                'name' => 'Israeli Shekel',
                'symbol' => '₪',
            ]);
        }

        // if (!$employee) {
        //     $employee = Employee::create([
        //         'user_id' => $user->id,
        //         'company_id' => $company->id,
        //         'employee_number' => 'EMP-001',
        //         'first_name' => 'John',
        //         'second_name' => 'Doe',
        //         'email' => 'employee@example.com',
        //         'phone1' => '0599123456',
        //     ]);
        // }

        // Customer data array
        $customers = [
            [
                'customer_number' => 'CUST-001',
                'company_name' => 'شركة التقنية المتقدمة',
                'first_name' => 'أحمد',
                'second_name' => 'محمد',
                'contact_name' => 'أحمد محمد علي',
                'email' => 'ahmed@techcompany.com',
                'phone' => '082345678',
                'mobile' => '0599123456',
                'address_one' => 'شارع الجلاء، المدينة',
                'address_two' => 'بناية رقم 15، الطابق الثالث',
                'postal_code' => '12345',
                'licensed_operator' => 'مؤسسة التقنية المحدودة',
                'tax_number' => 'TAX-001-2024',
                'notes' => 'عميل مهم، يحتاج متابعة دورية',
                'code' => 'TECH-001',
                'invoice_type' => 'electronic',
                'category' => 'corporate',
            ],
            [
                'customer_number' => 'CUST-002',
                'company_name' => 'مؤسسة البناء والتعمير',
                'first_name' => 'فاطمة',
                'second_name' => 'أحمد',
                'contact_name' => 'فاطمة أحمد حسن',
                'email' => 'fatima@construction.com',
                'phone' => '082567890',
                'mobile' => '0599234567',
                'address_one' => 'شارع النصر، وسط البلد',
                'address_two' => 'مجمع الأعمال، مكتب 205',
                'postal_code' => '23456',
                'licensed_operator' => 'مؤسسة البناء والتعمير المحدودة',
                'tax_number' => 'TAX-002-2024',
                'notes' => 'متخصص في مشاريع البناء الكبيرة',
                'code' => 'CONST-002',
                'invoice_type' => 'paper',
                'category' => 'construction',
            ],
            [
                'customer_number' => 'CUST-003',
                'company_name' => 'شركة التجارة العامة',
                'first_name' => 'محمد',
                'second_name' => 'علي',
                'contact_name' => 'محمد علي سالم',
                'email' => 'mohammed@trading.com',
                'phone' => '082789012',
                'mobile' => '0599345678',
                'address_one' => 'السوق التجاري المركزي',
                'address_two' => 'محل رقم 45',
                'postal_code' => '34567',
                'licensed_operator' => 'شركة التجارة العامة المساهمة',
                'tax_number' => 'TAX-003-2024',
                'notes' => 'تاجر جملة، يطلب خصومات كمية',
                'code' => 'TRADE-003',
                'invoice_type' => 'electronic',
                'category' => 'wholesale',
            ],
            [
                'customer_number' => 'CUST-004',
                'company_name' => 'مركز الخدمات الطبية',
                'first_name' => 'سارة',
                'second_name' => 'خالد',
                'contact_name' => 'د. سارة خالد محمود',
                'email' => 'sara@medical.com',
                'phone' => '082901234',
                'mobile' => '0599456789',
                'address_one' => 'شارع المستشفى، الحي الطبي',
                'address_two' => 'مجمع العيادات، الطابق الثاني',
                'postal_code' => '45678',
                'licensed_operator' => 'مركز الخدمات الطبية المحدود',
                'tax_number' => 'TAX-004-2024',
                'notes' => 'مركز طبي متخصص، يحتاج فواتير سريعة',
                'code' => 'MED-004',
                'invoice_type' => 'electronic',
                'category' => 'healthcare',
            ],
            [
                'customer_number' => 'CUST-005',
                'company_name' => 'شركة النقل والمواصلات',
                'first_name' => 'خالد',
                'second_name' => 'يوسف',
                'contact_name' => 'خالد يوسف إبراهيم',
                'email' => 'khalid@transport.com',
                'phone' => '082012345',
                'mobile' => '0599567890',
                'address_one' => 'محطة النقل المركزية',
                'address_two' => 'مكتب الإدارة، الطابق الأول',
                'postal_code' => '56789',
                'licensed_operator' => 'شركة النقل والمواصلات المحدودة',
                'tax_number' => 'TAX-005-2024',
                'notes' => 'شركة نقل كبيرة، عقود طويلة المدى',
                'code' => 'TRANS-005',
                'invoice_type' => 'paper',
                'category' => 'transportation',
            ],
        ];

        // Create customers
        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['customer_number' => $customerData['customer_number']],
                array_merge($customerData, [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => 1, // Default branch
                    'currency_id' => $currency->id,
                    'employee_id' => 1, // Default employee ID
                    'country_id' => $country->id,
                    'region_id' => $region->id,
                    'city_id' => $city->id,
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ])
            );
        }

        $this->command->info('✅ Customers seeded successfully!');
    }
}
