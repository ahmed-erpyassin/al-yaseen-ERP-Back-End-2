<?php

namespace Modules\Companies\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Region;
use Modules\Companies\Models\City;
use Modules\Companies\Models\Industry;
use Modules\Companies\Models\BusinessType;
use Modules\Users\Models\User;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Companies...');

        // Get required dependencies
        $user = User::first();
        $country = Country::first();
        $region = Region::first();
        $city = City::first();
        $industry = Industry::first();
        $businessType = BusinessType::first();
        $currency = Currency::first();
        $fiscalYear = FiscalYear::first();

        // Check for required dependencies
        if (!$user) {
            $this->command->warn('⚠️  User not found. Please seed Users module first.');
            return;
        }

        // Create default data if not found
        if (!$country) {
            $country = Country::firstOrCreate([
                'code' => 'PS'
            ], [
                'name' => 'فلسطين',
                'name_en' => 'Palestine',
                'phone_code' => '+970',
                'currency_code' => 'ILS',
                'timezone' => 'Asia/Gaza',
            ]);
        }

        if (!$region) {
            $region = Region::firstOrCreate([
                'country_id' => $country->id,
                'name' => 'قطاع غزة'
            ], [
                'name_en' => 'Gaza Strip',
            ]);
        }

        if (!$city) {
            $city = City::firstOrCreate([
                'region_id' => $region->id,
                'name' => 'غزة'
            ], [
                'name_en' => 'Gaza',
            ]);
        }

        if (!$industry) {
            $industry = Industry::firstOrCreate([
                'name_en' => 'Technology'
            ], [
                'name' => 'التكنولوجيا',
                'description' => 'قطاع التكنولوجيا والبرمجيات',
            ]);
        }

        if (!$businessType) {
            $businessType = BusinessType::firstOrCreate([
                'name' => 'شركة برمجيات'
            ], [
                'industry_id' => $industry->id,
                'description' => 'شركة متخصصة في تطوير البرمجيات',
                'status' => 'active',
            ]);
        }

        if (!$currency) {
            $currency = Currency::firstOrCreate([
                'code' => 'USD'
            ], [
                'company_id' => 1, // Will be updated after company creation
                'user_id' => $user->id,
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }

        if (!$fiscalYear) {
            $fiscalYear = FiscalYear::firstOrCreate([
                'name' => 'FY ' . now()->year
            ], [
                'company_id' => 1, // Will be updated after company creation
                'user_id' => $user->id,
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'status' => 'open',
            ]);
        }

        // Create company
        $company = Company::firstOrCreate([
            'commercial_registeration_number' => 'YERPC-001'
        ], [
            'currency_id' => $currency->id,
            'financial_year_id' => $fiscalYear->id,
            'industry_id' => $industry->id,
            'business_type_id' => $businessType->id,
            'country_id' => $country->id,
            'region_id' => $region->id,
            'city_id' => $city->id,
            'user_id' => $user->id,
            'title' => 'Yassin ERP Company',
            'address' => 'رام الله، فلسطين',
            'logo' => 'path/to/logo.png',
            'email' => 'info@yassincompany.com',
            'landline' => '02-1234567',
            'mobile' => '0599916672',
            'income_tax_rate' => 15.00,
            'vat_rate' => 16.00,
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Update currency and fiscal year with company_id
        $currency->update(['company_id' => $company->id]);
        $fiscalYear->update(['company_id' => $company->id]);

        // Create default branch
        $branch = Branch::firstOrCreate([
            'company_id' => $company->id,
            'code' => 'BR-001'
        ], [
            'user_id' => $user->id,
            'name' => 'الفرع الرئيسي',
            'branch_name_ar' => 'الفرع الرئيسي',
            'branch_name_en' => 'Main Branch',
            'address' => 'رام الله، فلسطين',
            'phone' => '02-1234567',
            'email' => 'main@yassincompany.com',
            'manager_name' => 'مدير الفرع',
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->command->info('✅ Company and Branch seeded successfully!');
    }
}
