<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\FinancialAccounts\Models\{
    Account,
    AccountGroup,
    Currency,
    ExchangeRate,
    FinancialSettings,
    FiscalYear,
    TaxRate
};
use Modules\Users\Models\User;

class FinancialModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(1); // تأكد من وجود مستخدم بالمعرف 1

        // 3. Fiscal Year
        $fy = FiscalYear::firstOrCreate([
            'user_id' => $user->id,
            'company_id' => $user->company?->id,
            'name'       => 'FY 2025',
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'end_date'   => now()->endOfYear()->format('Y-m-d'),
            'status'     => 'open',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        // 2. Currencies

        $usd = Currency::firstOrCreate(
            [
                'code' => 'USD'
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2
            ]
        );

        $ils = Currency::firstOrCreate(
            ['code' => 'ILS'],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'name' => 'Israeli Shekel',
                'symbol' => '₪',
                'decimal_places' => 2
            ]
        );

        // Exchange Rates
        ExchangeRate::firstOrCreate([
            'currency_id' => $usd->id,
            'rate_date'   => now()->format('Y-m-d'),
        ], [
            'user_id' => $user->id,
            'company_id' => $user->company?->id,
            'rate' => 1.0, // USD هو العملة المرجعية (Base Currency)
        ]);

        ExchangeRate::firstOrCreate([
            'currency_id' => $ils->id,
            'rate_date'   => now()->format('Y-m-d'),
        ], [
            'user_id' => $user->id,
            'company_id' => $user->company?->id,
            'rate' => 3.65, // 1 USD = 3.65 ILS
        ]);

        // 1. Account Groups
        $groups = [
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'name' => 'Assets',
                'code' => '1000',
                'type' => 'asset',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'name' => 'Liabilities',
                'code' => '2000',
                'type' => 'liability',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'name' => 'Equity',
                'code' => '3000',
                'type' => 'equity',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'name' => 'Revenue',
                'code' => '4000',
                'type' => 'revenue',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'name' => 'Expenses',
                'code' => '5000',
                'type' => 'expense',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
        ];

        foreach ($groups as $group) {
            AccountGroup::firstOrCreate([
                'code' => $group['code']
            ], $group);
        }

        $accounts = [
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'account_group_id' => AccountGroup::where('code', '1000')->first()->id,
                'name' => 'Cash',
                'code' => '1010',
                'type' => 'asset',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'account_group_id' => AccountGroup::where('code', '2000')->first()->id,
                'name' => 'VAT Payable',
                'code' => '2100',
                'type' => 'liability',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'account_group_id' => AccountGroup::where('code', '3000')->first()->id,
                'name' => 'Retained Earnings',
                'code' => '3100',
                'type' => 'equity',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'account_group_id' => AccountGroup::where('code', '4000')->first()->id,
                'name' => 'Sales Revenue',
                'code' => '4000',
                'type' => 'revenue',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'fiscal_year_id' => $fy->id,
                'currency_id' => $usd->id,
                'account_group_id' => AccountGroup::where('code', '5000')->first()->id,
                'name' => 'Purchases',
                'code' => '5000',
                'type' => 'expense',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate([
                'code' => $account['code']
            ], $account);
        }

        // 5. Tax Rates
        TaxRate::firstOrCreate([
            'company_id' => $user->company?->id,
            'name'       => 'Standard VAT',
            'code'       => 'VAT-16',
        ], [
            'user_id'    => $user->id,
            'account_id' => Account::where('code', '2100')->first()->id, // ربط بحساب VAT Payable
            'rate'       => 16.00,
            'type'       => 'vat',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);


        // 6. Settings
        FinancialSettings::firstOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => $user->company?->id,
                'default_currency_id' => $usd->id,
                'vat_account_id' => Account::where('code', '2100')->first()->id,
                'retained_earnings_account_id' => Account::where('code', '3100')->first()->id,
                'default_sales_account_id' => Account::where('code', '4000')->first()->id,
                'default_purchase_account_id' => Account::where('code', '5000')->first()->id,
                'fiscal_year_id' => $fy->id,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]
        );
    }
}
