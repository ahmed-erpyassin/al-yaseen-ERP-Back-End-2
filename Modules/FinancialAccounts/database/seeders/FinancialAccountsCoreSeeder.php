<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\FinancialAccounts\Models\Account;
use Modules\FinancialAccounts\Models\AccountGroup;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\ExchangeRate;
use Modules\FinancialAccounts\Models\TaxRate;

class FinancialAccountsCoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ===== EXCHANGE RATES =====
        $usd = Currency::where('code', 'USD')->first();
        $eur = Currency::where('code', 'EUR')->first();
        $ils = Currency::where('code', 'ILS')->first();

        $rates = [
            ['currency_id' => $eur->id, 'rate' => 0.93],
            ['currency_id' => $ils->id, 'rate' => 3.75],
            ['currency_id' => $usd->id, 'rate' => 1.00],
        ];

        foreach ($rates as $r) {
            ExchangeRate::create([
                'company_id' => 1,
                'user_id' => 1,
                'branch_id' => null,
                'currency_id' => $r['currency_id'],
                'rate_date' => Carbon::today(),
                'rate' => $r['rate'],
            ]);
        }

        // ===== ACCOUNT GROUPS =====
        $accountGroups = [
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue'],
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'expense'],
        ];

        foreach ($accountGroups as $g) {
            AccountGroup::firstOrCreate(
                ['code' => $g['code']],
                array_merge($g, [
                    'company_id' => 1,
                    'user_id' => 1,
                    'fiscal_year_id' => 1,
                    'currency_id' => $usd->id,
                ])
            );
        }

        // ===== ACCOUNTS =====
        $cashGroup = AccountGroup::where('code', '1000')->first();
        $revenueGroup = AccountGroup::where('code', '4000')->first();
        $expenseGroup = AccountGroup::where('code', '5000')->first();

        $accounts = [
            ['code' => '1010', 'name' => 'Cash', 'type' => 'asset', 'account_group_id' => $cashGroup->id],
            ['code' => '1020', 'name' => 'Bank Account', 'type' => 'asset', 'account_group_id' => $cashGroup->id],
            ['code' => '4010', 'name' => 'Sales Revenue', 'type' => 'revenue', 'account_group_id' => $revenueGroup->id],
            ['code' => '5010', 'name' => 'General Expenses', 'type' => 'expense', 'account_group_id' => $expenseGroup->id],
        ];

        foreach ($accounts as $a) {
            Account::firstOrCreate(
                ['code' => $a['code']],
                array_merge($a, [
                    'company_id' => 1,
                    'user_id' => 1,
                    'fiscal_year_id' => 1,
                    'currency_id' => $usd->id,
                ])
            );
        }

        // ===== TAX RATES =====
        $cashAccount = Account::where('code', '1010')->first();

        $taxRates = [
            ['code' => 'VAT15', 'name' => 'VAT 15%', 'rate' => 15.00, 'type' => 'vat'],
            ['code' => 'WHT5', 'name' => 'Withholding 5%', 'rate' => 5.00, 'type' => 'withholding'],
        ];

        foreach ($taxRates as $t) {
            TaxRate::firstOrCreate(
                ['code' => $t['code']],
                array_merge($t, [
                    'company_id' => 1,
                    'user_id' => 1,
                    'branch_id' => null,
                    'account_id' => $cashAccount->id,
                ])
            );
        }
    }
}
