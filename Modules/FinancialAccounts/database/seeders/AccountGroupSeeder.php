<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\AccountGroup;
use Modules\FinancialAccounts\Models\Currency;

class AccountGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get USD currency (assuming it exists from previous seeders)
        $usd = Currency::where('code', 'USD')->first();
        if (!$usd) {
            // Create USD if it doesn't exist
            $usd = Currency::create([
                'company_id' => 1,
                'user_id' => 1,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'created_by' => 1,
            ]);
        }

        $accountGroups = [
            // ASSETS (1000-1999)
            [
                'code' => '1000',
                'name' => 'Current Assets',
                'type' => 'asset',
                'parent_id' => null,
            ],
            [
                'code' => '1100',
                'name' => 'Cash and Cash Equivalents',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '1200',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '1300',
                'name' => 'Inventory',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '1400',
                'name' => 'Prepaid Expenses',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '1500',
                'name' => 'Fixed Assets',
                'type' => 'asset',
                'parent_id' => null,
            ],
            [
                'code' => '1510',
                'name' => 'Property, Plant & Equipment',
                'type' => 'asset',
                'parent_code' => '1500',
            ],
            [
                'code' => '1520',
                'name' => 'Accumulated Depreciation',
                'type' => 'asset',
                'parent_code' => '1500',
            ],

            // LIABILITIES (2000-2999)
            [
                'code' => '2000',
                'name' => 'Current Liabilities',
                'type' => 'liability',
                'parent_id' => null,
            ],
            [
                'code' => '2100',
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'parent_code' => '2000',
            ],
            [
                'code' => '2200',
                'name' => 'Accrued Expenses',
                'type' => 'liability',
                'parent_code' => '2000',
            ],
            [
                'code' => '2300',
                'name' => 'Short-term Debt',
                'type' => 'liability',
                'parent_code' => '2000',
            ],
            [
                'code' => '2400',
                'name' => 'Tax Liabilities',
                'type' => 'liability',
                'parent_code' => '2000',
            ],
            [
                'code' => '2500',
                'name' => 'Long-term Liabilities',
                'type' => 'liability',
                'parent_id' => null,
            ],
            [
                'code' => '2510',
                'name' => 'Long-term Debt',
                'type' => 'liability',
                'parent_code' => '2500',
            ],

            // EQUITY (3000-3999)
            [
                'code' => '3000',
                'name' => 'Owner\'s Equity',
                'type' => 'equity',
                'parent_id' => null,
            ],
            [
                'code' => '3100',
                'name' => 'Capital',
                'type' => 'equity',
                'parent_code' => '3000',
            ],
            [
                'code' => '3200',
                'name' => 'Retained Earnings',
                'type' => 'equity',
                'parent_code' => '3000',
            ],
            [
                'code' => '3300',
                'name' => 'Current Year Earnings',
                'type' => 'equity',
                'parent_code' => '3000',
            ],

            // REVENUE (4000-4999)
            [
                'code' => '4000',
                'name' => 'Operating Revenue',
                'type' => 'revenue',
                'parent_id' => null,
            ],
            [
                'code' => '4100',
                'name' => 'Sales Revenue',
                'type' => 'revenue',
                'parent_code' => '4000',
            ],
            [
                'code' => '4200',
                'name' => 'Service Revenue',
                'type' => 'revenue',
                'parent_code' => '4000',
            ],
            [
                'code' => '4300',
                'name' => 'Other Revenue',
                'type' => 'revenue',
                'parent_code' => '4000',
            ],

            // EXPENSES (5000-5999)
            [
                'code' => '5000',
                'name' => 'Cost of Goods Sold',
                'type' => 'expense',
                'parent_id' => null,
            ],
            [
                'code' => '5100',
                'name' => 'Direct Materials',
                'type' => 'expense',
                'parent_code' => '5000',
            ],
            [
                'code' => '5200',
                'name' => 'Direct Labor',
                'type' => 'expense',
                'parent_code' => '5000',
            ],
            [
                'code' => '5300',
                'name' => 'Manufacturing Overhead',
                'type' => 'expense',
                'parent_code' => '5000',
            ],
            [
                'code' => '6000',
                'name' => 'Operating Expenses',
                'type' => 'expense',
                'parent_id' => null,
            ],
            [
                'code' => '6100',
                'name' => 'Administrative Expenses',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6200',
                'name' => 'Selling Expenses',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6300',
                'name' => 'General Expenses',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
        ];

        // First pass: Create all groups without parent relationships
        foreach ($accountGroups as $group) {
            AccountGroup::firstOrCreate(
                ['code' => $group['code']],
                [
                    'code' => $group['code'],
                    'name' => $group['name'],
                    'type' => $group['type'],
                    'company_id' => 1,
                    'user_id' => 1,
                    'fiscal_year_id' => 1,
                    'currency_id' => $usd->id,
                    'parent_id' => null,
                    'created_by' => 1,
                ]
            );
        }

        // Second pass: Update parent relationships
        foreach ($accountGroups as $group) {
            if (isset($group['parent_code'])) {
                $parent = AccountGroup::where('code', $group['parent_code'])->first();
                if ($parent) {
                    AccountGroup::where('code', $group['code'])
                        ->update(['parent_id' => $parent->id]);
                }
            }
        }

        $this->command->info('Account Groups seeded successfully!');
    }
}
