<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Journal;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Journals...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $currency = Currency::first();
        $branch = Branch::first();

        if (!$user || !$company || !$currency) {
            $this->command->error('❌ Required data not found. Please seed Users, Companies, and Currencies first.');
            return;
        }

        $journals = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'currency_id' => $currency->id,
                'employee_id' => null,
                'name' => 'Sales Journal',
                'type' => 'sales',
                'code' => 'SJ-001',
                'max_documents' => 100,
                'current_number' => 1,
                'status' => 'active',
                'notes' => 'Main sales journal for recording sales transactions',
                'financial_journal_id' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'currency_id' => $currency->id,
                'employee_id' => null,
                'name' => 'Purchase Journal',
                'type' => 'purchase',
                'code' => 'PJ-001',
                'max_documents' => 100,
                'current_number' => 1,
                'status' => 'active',
                'notes' => 'Main purchase journal for recording purchase transactions',
                'financial_journal_id' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'currency_id' => $currency->id,
                'employee_id' => null,
                'name' => 'Quotations Journal',
                'type' => 'sales',
                'code' => 'QJ-001',
                'max_documents' => 200,
                'current_number' => 1,
                'status' => 'active',
                'notes' => 'Journal for recording sales quotations and offers',
                'financial_journal_id' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($journals as $journalData) {
            Journal::firstOrCreate([
                'company_id' => $journalData['company_id'],
                'code' => $journalData['code']
            ], $journalData);
        }

        $this->command->info('✅ Journals seeded successfully!');
    }
}
