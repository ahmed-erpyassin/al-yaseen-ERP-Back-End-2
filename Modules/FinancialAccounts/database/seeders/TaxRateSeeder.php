<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\TaxRate;

class TaxRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxRates = [
            // VAT Tax Rates
            [
                'name' => 'Standard VAT',
                'code' => 'VAT-15',
                'rate' => 15.00,
                'type' => 'vat',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Reduced VAT',
                'code' => 'VAT-5',
                'rate' => 5.00,
                'type' => 'vat',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Zero Rated VAT',
                'code' => 'VAT-0',
                'rate' => 0.00,
                'type' => 'vat',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Exempt VAT',
                'code' => 'VAT-EXEMPT',
                'rate' => 0.00,
                'type' => 'vat',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // Withholding Tax Rates
            [
                'name' => 'Withholding Tax - Services',
                'code' => 'WHT-SERVICES',
                'rate' => 5.00,
                'type' => 'withholding',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Withholding Tax - Professional Fees',
                'code' => 'WHT-PROF',
                'rate' => 10.00,
                'type' => 'withholding',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Withholding Tax - Rent',
                'code' => 'WHT-RENT',
                'rate' => 10.00,
                'type' => 'withholding',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Withholding Tax - Dividends',
                'code' => 'WHT-DIV',
                'rate' => 10.00,
                'type' => 'withholding',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Withholding Tax - Interest',
                'code' => 'WHT-INT',
                'rate' => 15.00,
                'type' => 'withholding',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // Income Tax Rates
            [
                'name' => 'Corporate Income Tax',
                'code' => 'CIT-20',
                'rate' => 20.00,
                'type' => 'income_tax',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Small Business Income Tax',
                'code' => 'SBIT-15',
                'rate' => 15.00,
                'type' => 'income_tax',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // Custom Tax Rates
            [
                'name' => 'Luxury Goods Tax',
                'code' => 'LUXURY-25',
                'rate' => 25.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Environmental Tax',
                'code' => 'ENV-3',
                'rate' => 3.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Import Duty',
                'code' => 'IMPORT-10',
                'rate' => 10.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Excise Tax - Tobacco',
                'code' => 'EXCISE-TOBACCO',
                'rate' => 100.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Excise Tax - Alcohol',
                'code' => 'EXCISE-ALCOHOL',
                'rate' => 50.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Excise Tax - Soft Drinks',
                'code' => 'EXCISE-SOFT',
                'rate' => 50.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Excise Tax - Energy Drinks',
                'code' => 'EXCISE-ENERGY',
                'rate' => 100.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Excise Tax - Electronic Smoking',
                'code' => 'EXCISE-ESMOKING',
                'rate' => 100.00,
                'type' => 'custom',
                'user_id' => 1,
                'company_id' => 1,
                'branch_id' => null,
                'account_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($taxRates as $taxRate) {
            TaxRate::updateOrCreate(
                ['code' => $taxRate['code']],
                $taxRate
            );
        }

        $this->command->info('Tax rates seeded successfully!');
    }
}

