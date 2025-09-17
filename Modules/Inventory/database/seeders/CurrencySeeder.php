<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Currencies...');

        // Get required data
        $user = User::first();
        $company = Company::first();

        if (!$user || !$company) {
            $this->command->warn('⚠️  Required data not found. Please seed Users and Companies first.');
            return;
        }

        $currencies = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'symbol' => 'ر.س',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'AED',
                'name' => 'UAE Dirham',
                'symbol' => 'د.إ',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($currencies as $currencyData) {
            Currency::firstOrCreate([
                'company_id' => $currencyData['company_id'],
                'code' => $currencyData['code']
            ], $currencyData);
        }

        $this->command->info('✅ Currencies seeded successfully!');
    }
}
