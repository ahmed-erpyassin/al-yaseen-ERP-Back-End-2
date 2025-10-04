<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\Account;
use Modules\FinancialAccounts\Models\AccountGroup;
use Modules\FinancialAccounts\Models\Currency;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get USD currency
        $usd = Currency::where('code', 'USD')->first();
        if (!$usd) {
            $this->command->error('USD currency not found. Please run Currency seeder first.');
            return;
        }

        $accounts = [
            // CASH AND CASH EQUIVALENTS (1100)
            [
                'code' => '1101',
                'name' => 'Petty Cash',
                'type' => 'asset',
                'group_code' => '1100',
            ],
            [
                'code' => '1102',
                'name' => 'Cash in Bank - Main Account',
                'type' => 'asset',
                'group_code' => '1100',
            ],
            [
                'code' => '1103',
                'name' => 'Cash in Bank - Savings Account',
                'type' => 'asset',
                'group_code' => '1100',
            ],
            [
                'code' => '1104',
                'name' => 'Foreign Currency Cash',
                'type' => 'asset',
                'group_code' => '1100',
            ],

            // ACCOUNTS RECEIVABLE (1200)
            [
                'code' => '1201',
                'name' => 'Trade Receivables',
                'type' => 'asset',
                'group_code' => '1200',
            ],
            [
                'code' => '1202',
                'name' => 'Employee Advances',
                'type' => 'asset',
                'group_code' => '1200',
            ],
            [
                'code' => '1203',
                'name' => 'Other Receivables',
                'type' => 'asset',
                'group_code' => '1200',
            ],
            [
                'code' => '1204',
                'name' => 'Allowance for Doubtful Accounts',
                'type' => 'asset',
                'group_code' => '1200',
            ],

            // INVENTORY (1300)
            [
                'code' => '1301',
                'name' => 'Raw Materials',
                'type' => 'asset',
                'group_code' => '1300',
            ],
            [
                'code' => '1302',
                'name' => 'Work in Progress',
                'type' => 'asset',
                'group_code' => '1300',
            ],
            [
                'code' => '1303',
                'name' => 'Finished Goods',
                'type' => 'asset',
                'group_code' => '1300',
            ],
            [
                'code' => '1304',
                'name' => 'Merchandise Inventory',
                'type' => 'asset',
                'group_code' => '1300',
            ],

            // PREPAID EXPENSES (1400)
            [
                'code' => '1401',
                'name' => 'Prepaid Insurance',
                'type' => 'asset',
                'group_code' => '1400',
            ],
            [
                'code' => '1402',
                'name' => 'Prepaid Rent',
                'type' => 'asset',
                'group_code' => '1400',
            ],
            [
                'code' => '1403',
                'name' => 'Prepaid Utilities',
                'type' => 'asset',
                'group_code' => '1400',
            ],

            // PROPERTY, PLANT & EQUIPMENT (1510)
            [
                'code' => '1511',
                'name' => 'Land',
                'type' => 'asset',
                'group_code' => '1510',
            ],
            [
                'code' => '1512',
                'name' => 'Buildings',
                'type' => 'asset',
                'group_code' => '1510',
            ],
            [
                'code' => '1513',
                'name' => 'Machinery & Equipment',
                'type' => 'asset',
                'group_code' => '1510',
            ],
            [
                'code' => '1514',
                'name' => 'Vehicles',
                'type' => 'asset',
                'group_code' => '1510',
            ],
            [
                'code' => '1515',
                'name' => 'Furniture & Fixtures',
                'type' => 'asset',
                'group_code' => '1510',
            ],
            [
                'code' => '1516',
                'name' => 'Computer Equipment',
                'type' => 'asset',
                'group_code' => '1510',
            ],

            // ACCUMULATED DEPRECIATION (1520)
            [
                'code' => '1521',
                'name' => 'Accumulated Depreciation - Buildings',
                'type' => 'asset',
                'group_code' => '1520',
            ],
            [
                'code' => '1522',
                'name' => 'Accumulated Depreciation - Machinery',
                'type' => 'asset',
                'group_code' => '1520',
            ],
            [
                'code' => '1523',
                'name' => 'Accumulated Depreciation - Vehicles',
                'type' => 'asset',
                'group_code' => '1520',
            ],
            [
                'code' => '1524',
                'name' => 'Accumulated Depreciation - Furniture',
                'type' => 'asset',
                'group_code' => '1520',
            ],

            // ACCOUNTS PAYABLE (2100)
            [
                'code' => '2101',
                'name' => 'Trade Payables',
                'type' => 'liability',
                'group_code' => '2100',
            ],
            [
                'code' => '2102',
                'name' => 'Accrued Expenses Payable',
                'type' => 'liability',
                'group_code' => '2100',
            ],
            [
                'code' => '2103',
                'name' => 'Notes Payable - Short Term',
                'type' => 'liability',
                'group_code' => '2100',
            ],

            // ACCRUED EXPENSES (2200)
            [
                'code' => '2201',
                'name' => 'Accrued Salaries',
                'type' => 'liability',
                'group_code' => '2200',
            ],
            [
                'code' => '2202',
                'name' => 'Accrued Interest',
                'type' => 'liability',
                'group_code' => '2200',
            ],
            [
                'code' => '2203',
                'name' => 'Accrued Utilities',
                'type' => 'liability',
                'group_code' => '2200',
            ],

            // TAX LIABILITIES (2400)
            [
                'code' => '2401',
                'name' => 'VAT Payable',
                'type' => 'liability',
                'group_code' => '2400',
            ],
            [
                'code' => '2402',
                'name' => 'Income Tax Payable',
                'type' => 'liability',
                'group_code' => '2400',
            ],
            [
                'code' => '2403',
                'name' => 'Withholding Tax Payable',
                'type' => 'liability',
                'group_code' => '2400',
            ],

            // LONG-TERM DEBT (2510)
            [
                'code' => '2511',
                'name' => 'Bank Loans - Long Term',
                'type' => 'liability',
                'group_code' => '2510',
            ],
            [
                'code' => '2512',
                'name' => 'Mortgage Payable',
                'type' => 'liability',
                'group_code' => '2510',
            ],

            // CAPITAL (3100)
            [
                'code' => '3101',
                'name' => 'Owner\'s Capital',
                'type' => 'equity',
                'group_code' => '3100',
            ],
            [
                'code' => '3102',
                'name' => 'Additional Paid-in Capital',
                'type' => 'equity',
                'group_code' => '3100',
            ],

            // RETAINED EARNINGS (3200)
            [
                'code' => '3201',
                'name' => 'Retained Earnings',
                'type' => 'equity',
                'group_code' => '3200',
            ],

            // CURRENT YEAR EARNINGS (3300)
            [
                'code' => '3301',
                'name' => 'Current Year Profit/Loss',
                'type' => 'equity',
                'group_code' => '3300',
            ],

            // SALES REVENUE (4100)
            [
                'code' => '4101',
                'name' => 'Product Sales',
                'type' => 'revenue',
                'group_code' => '4100',
            ],
            [
                'code' => '4102',
                'name' => 'Merchandise Sales',
                'type' => 'revenue',
                'group_code' => '4100',
            ],
            [
                'code' => '4103',
                'name' => 'Export Sales',
                'type' => 'revenue',
                'group_code' => '4100',
            ],

            // SERVICE REVENUE (4200)
            [
                'code' => '4201',
                'name' => 'Consulting Services',
                'type' => 'revenue',
                'group_code' => '4200',
            ],
            [
                'code' => '4202',
                'name' => 'Maintenance Services',
                'type' => 'revenue',
                'group_code' => '4200',
            ],
            [
                'code' => '4203',
                'name' => 'Installation Services',
                'type' => 'revenue',
                'group_code' => '4200',
            ],

            // OTHER REVENUE (4300)
            [
                'code' => '4301',
                'name' => 'Interest Income',
                'type' => 'revenue',
                'group_code' => '4300',
            ],
            [
                'code' => '4302',
                'name' => 'Rental Income',
                'type' => 'revenue',
                'group_code' => '4300',
            ],
            [
                'code' => '4303',
                'name' => 'Miscellaneous Income',
                'type' => 'revenue',
                'group_code' => '4300',
            ],

            // DIRECT MATERIALS (5100)
            [
                'code' => '5101',
                'name' => 'Raw Material Purchases',
                'type' => 'expense',
                'group_code' => '5100',
            ],
            [
                'code' => '5102',
                'name' => 'Material Freight In',
                'type' => 'expense',
                'group_code' => '5100',
            ],

            // DIRECT LABOR (5200)
            [
                'code' => '5201',
                'name' => 'Direct Labor Wages',
                'type' => 'expense',
                'group_code' => '5200',
            ],
            [
                'code' => '5202',
                'name' => 'Direct Labor Benefits',
                'type' => 'expense',
                'group_code' => '5200',
            ],

            // MANUFACTURING OVERHEAD (5300)
            [
                'code' => '5301',
                'name' => 'Factory Utilities',
                'type' => 'expense',
                'group_code' => '5300',
            ],
            [
                'code' => '5302',
                'name' => 'Factory Supplies',
                'type' => 'expense',
                'group_code' => '5300',
            ],
            [
                'code' => '5303',
                'name' => 'Factory Depreciation',
                'type' => 'expense',
                'group_code' => '5300',
            ],

            // ADMINISTRATIVE EXPENSES (6100)
            [
                'code' => '6101',
                'name' => 'Office Salaries',
                'type' => 'expense',
                'group_code' => '6100',
            ],
            [
                'code' => '6102',
                'name' => 'Office Rent',
                'type' => 'expense',
                'group_code' => '6100',
            ],
            [
                'code' => '6103',
                'name' => 'Office Utilities',
                'type' => 'expense',
                'group_code' => '6100',
            ],
            [
                'code' => '6104',
                'name' => 'Office Supplies',
                'type' => 'expense',
                'group_code' => '6100',
            ],
            [
                'code' => '6105',
                'name' => 'Professional Fees',
                'type' => 'expense',
                'group_code' => '6100',
            ],
            [
                'code' => '6106',
                'name' => 'Insurance Expense',
                'type' => 'expense',
                'group_code' => '6100',
            ],

            // SELLING EXPENSES (6200)
            [
                'code' => '6201',
                'name' => 'Sales Salaries',
                'type' => 'expense',
                'group_code' => '6200',
            ],
            [
                'code' => '6202',
                'name' => 'Sales Commissions',
                'type' => 'expense',
                'group_code' => '6200',
            ],
            [
                'code' => '6203',
                'name' => 'Advertising Expense',
                'type' => 'expense',
                'group_code' => '6200',
            ],
            [
                'code' => '6204',
                'name' => 'Marketing Expense',
                'type' => 'expense',
                'group_code' => '6200',
            ],
            [
                'code' => '6205',
                'name' => 'Travel & Entertainment',
                'type' => 'expense',
                'group_code' => '6200',
            ],

            // GENERAL EXPENSES (6300)
            [
                'code' => '6301',
                'name' => 'Bank Charges',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6302',
                'name' => 'Interest Expense',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6303',
                'name' => 'Depreciation Expense',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6304',
                'name' => 'Bad Debt Expense',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6305',
                'name' => 'Repairs & Maintenance',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6306',
                'name' => 'Telephone & Internet',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6307',
                'name' => 'Training & Development',
                'type' => 'expense',
                'group_code' => '6300',
            ],
            [
                'code' => '6308',
                'name' => 'Miscellaneous Expense',
                'type' => 'expense',
                'group_code' => '6300',
            ],
        ];

        foreach ($accounts as $account) {
            // Find the account group
            $accountGroup = AccountGroup::where('code', $account['group_code'])->first();

            if ($accountGroup) {
                Account::firstOrCreate(
                    ['code' => $account['code']],
                    [
                        'code' => $account['code'],
                        'name' => $account['name'],
                        'type' => $account['type'],
                        'company_id' => 1,
                        'user_id' => 1,
                        'fiscal_year_id' => 1,
                        'currency_id' => $usd->id,
                        'account_group_id' => $accountGroup->id,
                        'parent_id' => null,
                        'created_by' => 1,
                    ]
                );
            } else {
                $this->command->warn("Account group {$account['group_code']} not found for account {$account['code']}");
            }
        }

        $this->command->info('Accounts seeded successfully!');
    }
}
