<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\ProjectFinancial;
use Modules\ProjectsManagment\Models\Project;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\FinancialAccounts\Models\Currency;
use Carbon\Carbon;

class ProjectFinancialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $user = User::first();
        $company = Company::first();
        $projects = Project::all();
        $currency = Currency::first();

        if (!$user || !$company || $projects->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Projects first.');
            return;
        }

        $financials = [];

        // Financial records for ERP System Development (Project 1)
        $project1 = $projects->where('code', 'PRJ-001')->first();
        if ($project1) {
            $financials = array_merge($financials, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project1->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'initial_budget',
                    'reference_id' => null,
                    'amount' => 150000.00,
                    'date' => Carbon::now()->subDays(30),
                    'description' => 'Initial project budget allocation',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project1->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -15000.00,
                    'date' => Carbon::now()->subDays(25),
                    'description' => 'Development tools and software licenses',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project1->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -18000.00,
                    'date' => Carbon::now()->subDays(20),
                    'description' => 'Developer salaries - Month 1',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project1->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -12000.00,
                    'date' => Carbon::now()->subDays(10),
                    'description' => 'Infrastructure and hosting costs',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project1->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'milestone_payment',
                    'reference_id' => null,
                    'amount' => 45000.00,
                    'date' => Carbon::now()->subDays(15),
                    'description' => 'Milestone 1 & 2 completion payment',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Financial records for Mobile Application Development (Project 2)
        $project2 = $projects->where('code', 'PRJ-002')->first();
        if ($project2) {
            $financials = array_merge($financials, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project2->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'initial_budget',
                    'reference_id' => null,
                    'amount' => 75000.00,
                    'date' => Carbon::now()->subDays(15),
                    'description' => 'Mobile app development budget',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project2->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -8000.00,
                    'date' => Carbon::now()->subDays(12),
                    'description' => 'Mobile development tools and licenses',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project2->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -10000.00,
                    'date' => Carbon::now()->subDays(8),
                    'description' => 'UI/UX design and mobile developer costs',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project2->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'milestone_payment',
                    'reference_id' => null,
                    'amount' => 22500.00,
                    'date' => Carbon::now()->subDays(5),
                    'description' => 'UI/UX design completion payment',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Financial records for Data Migration Project (Project 3)
        $project3 = $projects->where('code', 'PRJ-003')->first();
        if ($project3) {
            $financials = array_merge($financials, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project3->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'initial_budget',
                    'reference_id' => null,
                    'amount' => 35000.00,
                    'date' => Carbon::now()->addDays(5),
                    'description' => 'Data migration project budget',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Financial records for System Integration (Project 4)
        $project4 = $projects->where('code', 'PRJ-004')->first();
        if ($project4) {
            $financials = array_merge($financials, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project4->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'initial_budget',
                    'reference_id' => null,
                    'amount' => 60000.00,
                    'date' => Carbon::now()->subDays(10),
                    'description' => 'System integration project budget',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project4->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -12000.00,
                    'date' => Carbon::now()->subDays(8),
                    'description' => 'Integration tools and API licenses',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project4->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'expense',
                    'reference_id' => null,
                    'amount' => -12000.00,
                    'date' => Carbon::now()->subDays(5),
                    'description' => 'Integration specialist salary',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project4->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'milestone_payment',
                    'reference_id' => null,
                    'amount' => 28800.00,
                    'date' => Carbon::now()->subDays(3),
                    'description' => 'API development milestone payment',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Financial records for Training and Documentation (Project 5)
        $project5 = $projects->where('code', 'PRJ-005')->first();
        if ($project5) {
            $financials = array_merge($financials, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'currency_id' => $currency?->id,
                    'project_id' => $project5->id,
                    'exchange_rate' => 1.0000,
                    'reference_type' => 'initial_budget',
                    'reference_id' => null,
                    'amount' => 25000.00,
                    'date' => Carbon::now()->addDays(60),
                    'description' => 'Training and documentation budget',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        foreach ($financials as $financialData) {
            // Ensure all financials have required fields
            $branch = Branch::where('company_id', $company->id)->first();
            $fiscalYear = FiscalYear::where('company_id', $company->id)->first();

            $financialData['branch_id'] = $branch?->id;
            $financialData['fiscal_year_id'] = $fiscalYear?->id;

            // Add currency_id if not present
            if (!isset($financialData['currency_id']) || $financialData['currency_id'] === null) {
                // Create or get a default currency
                $defaultCurrency = Currency::firstOrCreate([
                    'code' => 'USD'
                ], [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'name' => 'US Dollar',
                    'symbol' => '$',
                    'decimal_places' => 2,
                    'created_by' => $user->id
                ]);
                $financialData['currency_id'] = $defaultCurrency->id;
            }

            // Add reference_id if not present
            if (!isset($financialData['reference_id']) || $financialData['reference_id'] === null) {
                $financialData['reference_id'] = $financialData['project_id'] ?? 1; // Use project_id as reference
            }

            ProjectFinancial::create($financialData);
        }

        $this->command->info('✅ Project Financials seeded successfully!');
    }
}
