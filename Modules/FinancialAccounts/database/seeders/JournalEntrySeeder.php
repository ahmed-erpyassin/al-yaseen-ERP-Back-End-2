<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\JournalEntry;
use Modules\FinancialAccounts\Models\JournalsFinancial;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Carbon\Carbon;

class JournalEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Journal Entries...');

        // Get required data
        $user = User::first();
        $company = Company::first();
        $branch = Branch::first();
        $fiscalYear = FiscalYear::first();
        $journals = JournalsFinancial::limit(3)->get();

        if (!$user || !$company || $journals->isEmpty()) {
            $this->command->error('❌ Required data not found. Please seed Users, Companies, and Financial Journals first.');
            return;
        }

        $entryNumber = 1;
        $journalEntries = [];

        // Create sample entries for each journal
        foreach ($journals as $journal) {
            // Sales entry
            if (str_contains($journal->name, 'Sales')) {
                $journalEntries[] = [
                    'fiscal_year_id' => $fiscalYear?->id,
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'journal_id' => $journal->id,
                    'document_id' => null,
                    'type' => 'sales',
                    'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                    'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                    'description' => 'Sales transaction entry for customer invoice',
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }

            // Purchase entry
            if (str_contains($journal->name, 'Purchase')) {
                $journalEntries[] = [
                    'fiscal_year_id' => $fiscalYear?->id,
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'journal_id' => $journal->id,
                    'document_id' => null,
                    'type' => 'purchase',
                    'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                    'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                    'description' => 'Purchase transaction entry for supplier invoice',
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }

            // General entry
            if (str_contains($journal->name, 'General')) {
                $journalEntries[] = [
                    'fiscal_year_id' => $fiscalYear?->id,
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'journal_id' => $journal->id,
                    'document_id' => null,
                    'type' => 'adjustment',
                    'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                    'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                    'description' => 'General adjusting entry for month-end adjustments',
                    'status' => 'draft',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }

            // Cash receipt entry
            if (str_contains($journal->name, 'Cash Receipts')) {
                $journalEntries[] = [
                    'fiscal_year_id' => $fiscalYear?->id,
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'journal_id' => $journal->id,
                    'document_id' => null,
                    'type' => 'receipt',
                    'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                    'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                    'description' => 'Cash receipt from customer payment',
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }

            // Cash payment entry
            if (str_contains($journal->name, 'Cash Payments')) {
                $journalEntries[] = [
                    'fiscal_year_id' => $fiscalYear?->id,
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch?->id,
                    'journal_id' => $journal->id,
                    'document_id' => null,
                    'type' => 'payment',
                    'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                    'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                    'description' => 'Cash payment to supplier',
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];
            }
        }

        // Add some additional sample entries
        $additionalEntries = [
            [
                'fiscal_year_id' => $fiscalYear?->id,
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'journal_id' => $journals->first()->id,
                'document_id' => null,
                'type' => 'inventory',
                'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                'description' => 'Inventory adjustment entry',
                'status' => 'draft',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'fiscal_year_id' => $fiscalYear?->id,
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'journal_id' => $journals->first()->id,
                'document_id' => null,
                'type' => 'production',
                'entry_number' => 'JE-' . str_pad($entryNumber++, 4, '0', STR_PAD_LEFT),
                'entry_date' => Carbon::now()->subDays(rand(1, 30)),
                'description' => 'Production cost allocation entry',
                'status' => 'posted',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        $journalEntries = array_merge($journalEntries, $additionalEntries);

        foreach ($journalEntries as $entryData) {
            JournalEntry::firstOrCreate([
                'entry_number' => $entryData['entry_number']
            ], $entryData);
        }

        $this->command->info('✅ Journal Entries seeded successfully!');
    }
}
