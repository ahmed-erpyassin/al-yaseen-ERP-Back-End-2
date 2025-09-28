<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الدور لو مش موجود
        $customerRole = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'api'
        ]);

        // اجلب الصلاحيات الخاصة بالـ api فقط
        $apiPermissions = Permission::where('guard_name', 'api')
            ->whereIn('name', [
                'api.users.index',
                'api.users.show',
                // الصلاحيات المسموح بها للعميل
                'access_customers',
                'access_sales',
                'access_billing',
            ])
            ->get();

        // اربط الصلاحيات بالدور
        $customerRole->syncPermissions($apiPermissions);

        // إنشاء المستخدم الرئيسي
        $user = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'first_name'        => 'مدير',
            'second_name'       => 'النظام',
            'phone'             => '0599916672',
            'password'          => Hash::make('admin123'),
            'status'            => 'active',
            'type'              => 'super_admin',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'created_by'        => 1,
            'updated_by'        => 1,
        ]);

        // إنشاء دوري المشرف الأعلى (واحد web وواحد api)
        $roles = [];
        foreach (['web', 'api'] as $guard) {
            $roles[$guard] = Role::firstOrCreate([
                'name'       => 'super_admin' . '_' . $guard,
                'guard_name' => $guard,
            ]);
        }

        // ربط كل الصلاحيات المتوفرة بكل دور حسب الـ guard
        foreach ($roles as $guard => $role) {
            $permissions = Permission::where('guard_name', $guard)->pluck('name')->toArray();
            $role->syncPermissions($permissions);
        }

        // ربط المستخدم بالدورين (web + api)
        $user->syncRoles([
            $roles['web'],
            $roles['api'],
        ]);

        // ===== FISCAL YEAR =====
        $fiscalYear = FiscalYear::firstOrCreate(
            ['name' => 'FY ' . now()->year],
            [
                'company_id' => 1,
                'user_id' => 1,
                'start_date' => Carbon::create(now()->year, 1, 1),
                'end_date' => Carbon::create(now()->year, 12, 31),
                'status' => 'open',
            ]
        );

        // ===== CURRENCIES =====
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimal_places' => 2],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'decimal_places' => 2],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimal_places' => 2],
            ['code' => 'ILS', 'name' => 'Israeli Shekel', 'symbol' => '₪', 'decimal_places' => 2],
        ];

        foreach ($currencies as $c) {
            Currency::firstOrCreate(
                ['code' => $c['code']],
                array_merge($c, [
                    'company_id' => 1,
                    'user_id' => 1,
                ])
            );
        }

        $company = Company::create([
            'currency_id'                           => 1,
            'financial_year_id'                     => 1,
            'industry_id'                           => 1,
            'business_type_id'                      => 1,
            'country_id'                            => 1,
            'region_id'                             => 1,
            'city_id'                               => 1,
            'user_id'                               => $user->id,
            'title'                                 => 'Yassin ERP Company',
            'commercial_registeration_number'       => 'YERPC-001',
            'address'                               => 'رام الله، فلسطين',
            'logo'                                  => 'path/to/logo.png',
            'email'                                 => 'info@yassincompany.com',
            'landline'                              => '02-1234567',
            'mobile'                                => '0599916672',
            'income_tax_rate'                       => 15.00,
            'vat_rate'                              => 16.00,
            'status'                                => 'active',
            'created_by'                            => $user->id,
            'updated_by'                            => $user->id,
        ]);

        $this->command->info('✅ تم إنشاء مدير النظام وربطه بكل الصلاحيات (web + api).');
    }
}
