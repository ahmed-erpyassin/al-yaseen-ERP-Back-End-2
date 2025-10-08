<?php

namespace Modules\Companies\app\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\Account;
use Modules\FinancialAccounts\Models\AccountGroup;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\ExchangeRate;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\FinancialAccounts\Models\TaxRate;

class CompanyService
{
    public function createCompany(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            dd($data);

            $company = Company::create($data);

            $this->setCompanyCoreData($company, $user);

            return $company;
        });
    }

    public function getCompanies($user)
    {
        return Company::where('user_id', $user->id)->get();
    }

    public function getCompanyById($id)
    {
        return Company::findOrFail($id);
    }

    public function updateCompany($id, array $data)
    {
        $company = Company::findOrFail($id);
        $company->update($data);

        return $company;
    }

    public function deleteCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
    }

    public function setCompanyCoreData($company, $user)
    {
        // ===== Create Fiscal Year =====

        $fiscalYear = FiscalYear::firstOrCreate([
            'start_date' => Carbon::create(now()->year, 1, 1),
            'end_date' => Carbon::create(now()->year, 12, 31),
            'company_id' => $company->id,
            'user_id' => $user->id,
        ], [
            'name' => 'FY ' . now()->year,
            'company_id' => $company->id,
            'user_id' => $user->id,
            'start_date' => Carbon::create(now()->year, 1, 1),
            'end_date' => Carbon::create(now()->year, 12, 31),
            'status' => 'open',
        ]);

        // ===== Create Default Currency =====
        $currencyUSDDefault = Currency::firstOrCreate(
            ['code' => 'USD', 'company_id' => $company->id, 'user_id' => $user->id],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        $company->financial_year_id = $fiscalYear->id;
        $company->currency_id = $currencyUSDDefault->id;
        $company->save();

        // ===== Create Exchange Rates =====
        $usd = Currency::where('code', 'USD')->where('company_id', $company->id)->first();

        $exchangeRates = [
            ['currency_id' => $usd->id, 'rate' => 1.00, 'company_id' => $company->id, 'user_id' => $user->id],
        ];

        foreach ($exchangeRates as $rate) {
            ExchangeRate::firstOrCreate(
                ['currency_id' => $rate['currency_id'], 'company_id' => $rate['company_id'], 'user_id' => $rate['user_id']],
                array_merge($rate, [
                    'user_id' => $user->id,
                    'rate_date' => Carbon::today(),
                    'branch_id' => null,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ])
            );
        }

        // ===== Create Account Groups =====
        $accountGroups = [
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue'],
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'expense'],
        ];

        foreach ($accountGroups as $group) {
            AccountGroup::firstOrCreate(
                ['code' => $group['code'], 'company_id' => $company->id, 'user_id' => $user->id],
                array_merge($group, [
                    'user_id' => $user->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'currency_id' => $currencyUSDDefault->id,
                ])
            );
        }

        // ===== Create Accounts =====
        $cashGroup = AccountGroup::where('code', '1000')->where('company_id', $company->id)->first();
        $revenueGroup = AccountGroup::where('code', '4000')->where('company_id', $company->id)->first();
        $expenseGroup = AccountGroup::where('code', '5000')->where('company_id', $company->id)->first();

        $accounts = [
            ['code' => '1001', 'name' => 'Cash', 'type' => 'asset', 'account_group_id' => $cashGroup->id],
            ['code' => '1002', 'name' => 'Bank Account', 'type' => 'asset', 'account_group_id' => $cashGroup->id],
            ['code' => '4001', 'name' => 'Sales', 'type' => 'revenue', 'account_group_id' => $revenueGroup->id],
            ['code' => '5001', 'name' => 'Rent', 'type' => 'expense', 'account_group_id' => $expenseGroup->id],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(
                ['code' => $account['code'], 'company_id' => $company->id, 'user_id' => $user->id],
                array_merge($account, [
                    'user_id' => $user->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'currency_id' => $currencyUSDDefault->id,
                ])
            );
        }

        // ===== Create Tax Rates =====
        $cashAccount = Account::where('code', '1001')->where('company_id', $company->id)->first();
        $taxRates = [
            ['code' => 'STANDARD_VAT', 'name' => 'Standard VAT', 'rate' => 15.00, 'type' => 'vat', 'account_id' => $cashAccount->id],
            ['code' => 'INCOME_TAX', 'name' => 'Income Tax', 'rate' => 10.00, 'type' => 'income_tax', 'account_id' => $cashAccount->id],
        ];

        foreach ($taxRates as $rate) {
            TaxRate::firstOrCreate(
                ['code' => $rate['code'], 'company_id' => $company->id, 'user_id' => $user->id],
                array_merge($rate, [
                    'user_id' => $user->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'branch_id' => null,
                    'currency_id' => $currencyUSDDefault->id,
                ])
            );
        }
    }
}
