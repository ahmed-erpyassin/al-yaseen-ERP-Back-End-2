<?php

namespace Modules\Purchases\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Purchases\Models\Purchase;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Suppliers\Models\Supplier;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Billing\Models\Journal;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Purchases...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $branch = Branch::first();
        $suppliers = Supplier::all();
        $customers = Customer::all();
        $currency = Currency::where('code', 'SAR')->first() ?? Currency::first();
        $taxRate = TaxRate::first();
        $journal = Journal::first();

        if (!$user || !$company || !$branch || $suppliers->isEmpty() || !$currency) {
            $this->command->warn('⚠️  Required data not found. Please seed Users, Companies, Branches, Suppliers, and Currencies first.');
            return;
        }

        $purchases = [
            // Purchase Invoice
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'currency_id' => $currency->id,
                'employee_id' => $user->id,
                'supplier_id' => $suppliers->first()->id,
                'journal_id' => $journal?->id ?? 1,
                'journal_number' => 1001,
                'quotation_number' => 'QUO-001',
                'invoice_number' => 'INV-001',
                'date' => Carbon::now()->subDays(30),
                'time' => '10:00:00',
                'due_date' => Carbon::now()->subDays(15),
                'supplier_name' => $suppliers->first()->name ?? 'Test Supplier',
                'supplier_email' => $suppliers->first()->email ?? 'supplier@test.com',
                'licensed_operator' => 'John Doe',
                'ledger_code' => 'LED-001',
                'ledger_number' => 1,
                'type' => 'invoice',
                'status' => 'approved',
                'cash_paid' => 5000.00,
                'checks_paid' => 0.00,
                'allowed_discount' => 500.00,
                'discount_percentage' => 5.00,
                'discount_amount' => 500.00,
                'total_without_tax' => 9500.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 1425.00,
                'remaining_balance' => 5925.00,
                'exchange_rate' => 1.0000,
                'currency_rate' => 1.0000,
                'total_foreign' => 10925.00,
                'total_local' => 10925.00,
                'total_amount' => 10925.00,
                'grand_total' => 10925.00,
                'notes' => 'Purchase invoice for office supplies and equipment',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'deleted_by' => $user->id,
            ],
            // Purchase Order
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'currency_id' => $currency->id,
                'employee_id' => $user->id,
                'supplier_id' => $suppliers->count() > 1 ? $suppliers->skip(1)->first()->id : $suppliers->first()->id,
                'journal_id' => $journal?->id ?? 1,
                'journal_number' => 1002,
                'quotation_number' => 'QUO-002',
                'invoice_number' => 'INV-002',
                'date' => Carbon::now()->subDays(20),
                'time' => '14:30:00',
                'due_date' => Carbon::now()->subDays(5),
                'supplier_name' => $suppliers->count() > 1 ? $suppliers->skip(1)->first()->name : $suppliers->first()->name,
                'supplier_email' => $suppliers->count() > 1 ? $suppliers->skip(1)->first()->email : $suppliers->first()->email,
                'licensed_operator' => 'Jane Smith',
                'ledger_code' => 'LED-002',
                'ledger_number' => 2,
                'type' => 'order',
                'status' => 'sent',
                'cash_paid' => 0.00,
                'checks_paid' => 0.00,
                'allowed_discount' => 0.00,
                'discount_percentage' => 0.00,
                'discount_amount' => 0.00,
                'total_without_tax' => 15000.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 2250.00,
                'remaining_balance' => 17250.00,
                'exchange_rate' => 1.0000,
                'currency_rate' => 1.0000,
                'total_foreign' => 17250.00,
                'total_local' => 17250.00,
                'total_amount' => 17250.00,
                'grand_total' => 17250.00,
                'notes' => 'Purchase order for inventory items',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'deleted_by' => $user->id,
            ],
            // Outgoing Order (if customers exist)
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'currency_id' => $currency->id,
                'employee_id' => $user->id,
                'supplier_id' => $suppliers->first()->id,
                'customer_id' => $customers->isNotEmpty() ? $customers->first()->id : null,
                'journal_id' => $journal?->id ?? 1,
                'journal_number' => 1003,
                'date' => Carbon::now()->subDays(10),
                'time' => '09:15:00',
                'customer_number' => $customers->isNotEmpty() ? $customers->first()->customer_number ?? 'CUST-001' : 'CUST-001',
                'customer_name' => $customers->isNotEmpty() ? $customers->first()->name ?? 'Test Customer' : 'Test Customer',
                'customer_email' => $customers->isNotEmpty() ? $customers->first()->email ?? 'customer@test.com' : 'customer@test.com',
                'customer_mobile' => $customers->isNotEmpty() ? $customers->first()->phone ?? '+966501234567' : '+966501234567',
                'supplier_name' => $suppliers->first()->name ?? 'Test Supplier',
                'ledger_code' => 'LED-003',
                'ledger_number' => 3,
                'type' => 'order',
                'status' => 'draft',
                'cash_paid' => 0.00,
                'checks_paid' => 0.00,
                'allowed_discount' => 200.00,
                'discount_percentage' => 2.00,
                'discount_amount' => 200.00,
                'total_without_tax' => 9800.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 1470.00,
                'remaining_balance' => 11270.00,
                'exchange_rate' => 1.0000,
                'currency_rate' => 1.0000,
                'total_foreign' => 11270.00,
                'total_local' => 11270.00,
                'total_amount' => 11270.00,
                'grand_total' => 11270.00,
                'notes' => 'Outgoing order for customer requirements',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'deleted_by' => $user->id,
            ],
            // Expense Purchase
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'currency_id' => $currency->id,
                'employee_id' => $user->id,
                'supplier_id' => $suppliers->first()->id,
                'journal_id' => $journal?->id ?? 1,
                'journal_number' => 1004,
                'date' => Carbon::now()->subDays(5),
                'time' => '16:45:00',
                'supplier_name' => $suppliers->first()->name ?? 'Test Supplier',
                'supplier_email' => $suppliers->first()->email ?? 'supplier@test.com',
                'ledger_code' => 'LED-004',
                'ledger_number' => 4,
                'type' => 'expense',
                'status' => 'approved',
                'cash_paid' => 2500.00,
                'checks_paid' => 0.00,
                'allowed_discount' => 0.00,
                'discount_percentage' => 0.00,
                'discount_amount' => 0.00,
                'total_without_tax' => 2500.00,
                'tax_percentage' => 15.00,
                'tax_amount' => 375.00,
                'remaining_balance' => 375.00,
                'exchange_rate' => 1.0000,
                'currency_rate' => 1.0000,
                'total_foreign' => 2875.00,
                'total_local' => 2875.00,
                'total_amount' => 2875.00,
                'grand_total' => 2875.00,
                'notes' => 'Office expenses and utilities',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'deleted_by' => $user->id,
            ],
        ];

        foreach ($purchases as $purchaseData) {
            Purchase::create($purchaseData);
        }

        $this->command->info('✅ Purchases seeded successfully!');
    }
}
