<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\JournalsFinancial;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;

class JournalsFinancialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Financial Journals...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $branch = Branch::first();
        $fiscalYear = FiscalYear::first();

        if (!$user || !$company) {
            $this->command->error('❌ Required data not found. Please seed Users and Companies first.');
            return;
        }

        $financialJournals = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'code' => 'FJ-001',
                'name' => 'General Journal',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'code' => 'FJ-002',
                'name' => 'Sales Journal',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'code' => 'FJ-003',
                'name' => 'Purchase Journal',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'code' => 'FJ-004',
                'name' => 'Cash Receipts Journal',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'code' => 'FJ-005',
                'name' => 'Cash Payments Journal',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'code' => 'FJ-006',
                'name' => 'Adjusting Entries Journal',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($financialJournals as $journalData) {
            JournalsFinancial::firstOrCreate([
                'company_id' => $journalData['company_id'],
                'code' => $journalData['code']
            ], $journalData);
        }

        $this->command->info('✅ Financial Journals seeded successfully!');
    }
}
