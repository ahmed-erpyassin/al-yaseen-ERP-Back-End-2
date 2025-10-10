<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\Project;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\FinancialAccounts\Models\CostCenter;
use Modules\Companies\Models\Country;
use Modules\Customers\Models\Customer;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing projects
        $this->command->info('🧹 Clearing existing projects...');
        Project::query()->delete();

        // Get basic required data
        $user = User::first();
        $company = Company::first();

        if (!$user || !$company) {
            $this->command->error('❌ Users or Companies not found. Please seed these first.');
            return;
        }

        // Get or create currency
        $currency = Currency::first();
        if (!$currency) {
            $currency = Currency::create([
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.00,
                'is_base' => true,
                'status' => 'active',
            ]);
            $this->command->info('💰 Created default USD currency');
        }

        // Get customers
        $customers = Customer::take(5)->get();
        if ($customers->isEmpty()) {
            $this->command->error('❌ No customers found. Please seed Customers module first.');
            return;
        }

        // Get country
        $country = Country::first();

        $this->command->info("✅ Ready to seed projects:");
        $this->command->info("   - Currency ID: {$currency->id} ({$currency->code})");
        $this->command->info("   - Customers: {$customers->count()} available");

        // Create required Branch if not exists
        $branch = Branch::firstOrCreate([
            'company_id' => $company->id,
            'code' => 'BR-001'
        ], [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'currency_id' => $currency->id,
            'manager_id' => $user->id,
            'financial_year_id' => null,
            'country_id' => $country?->id,
            'region_id' => null,
            'city_id' => null,
            'code' => 'BR-001',
            'name' => 'Main Branch',
            'branch_name_ar' => 'الفرع الرئيسي',
            'address' => 'Main Office Address',
            'landline' => '+966112345678',
            'mobile' => '+966501234567',
            'email' => 'branch@company.com',
            'logo' => null,
            'tax_number' => 'TAX-001',
            'timezone' => 'Asia/Riyadh',
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create required FiscalYear if not exists
        $fiscalYear = FiscalYear::firstOrCreate([
            'company_id' => $company->id,
            'name' => '2024-2025'
        ], [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'name' => '2024-2025',
            'start_date' => Carbon::create(2024, 1, 1),
            'end_date' => Carbon::create(2024, 12, 31),
            'status' => 'open',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create required CostCenter if not exists
        $costCenter = CostCenter::firstOrCreate([
            'company_id' => $company->id,
            'code' => 'CC-001'
        ], [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'fiscal_year_id' => $fiscalYear->id,
            'parent_id' => null,
            'code' => 'CC-001',
            'name' => 'General Operations',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create projects with proper customer_id and currency_id
        $projectsData = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'cost_center_id' => $costCenter->id,
                'manager_id' => $user->id,
                'customer_id' => $customers->first()->id,
                'currency_id' => $currency->id,
                'country_id' => $country?->id,
                'code' => 'PRJ-001',
                'project_number' => 'P2024-001',
                'name' => 'ERP System Development',
                'description' => 'Complete ERP system development with inventory and project management modules',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(90),
                'status' => 'open',
                'budget' => 150000.00,
                'project_value' => 180000.00,
                'actual_cost' => 45000.00,
                'progress' => 25.50,
                'customer_name' => $customers->first()->first_name ?? 'عميل تجريبي',
                'customer_email' => $customers->first()->email,
                'customer_phone' => $customers->first()->phone,
                'licensed_operator' => 'Tech Solutions Ltd',
                'currency_price' => 1.00,
                'include_vat' => true,
                'project_manager_name' => 'Ahmed Al-Mansouri',
                'notes' => 'High priority project with tight deadlines',
                'project_date' => Carbon::now()->subDays(30),
                'project_time' => Carbon::now()->setTime(9, 0),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'cost_center_id' => $costCenter->id,
                'manager_id' => $user->id,
                'customer_id' => $customers->get(0)?->id ?? $customers->first()->id,
                'currency_id' => $currency->id,
                'country_id' => $country?->id,
                'code' => 'PRJ-002',
                'project_number' => 'P2024-002',
                'name' => 'Mobile Application Development',
                'description' => 'Cross-platform mobile application for inventory management',
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(60),
                'status' => 'open',
                'budget' => 75000.00,
                'project_value' => 90000.00,
                'actual_cost' => 18000.00,
                'progress' => 15.75,
                'customer_name' => $customers->first()->first_name ?? 'عميل تجريبي',
                'customer_email' => $customers->first()->email,
                'customer_phone' => $customers->first()->phone,
                'licensed_operator' => 'Mobile Solutions Inc',
                'currency_price' => 1.00,
                'include_vat' => true,
                'project_manager_name' => 'Sara Al-Zahra',
                'notes' => 'Focus on user experience and performance',
                'project_date' => Carbon::now()->subDays(15),
                'project_time' => Carbon::now()->setTime(10, 30),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'cost_center_id' => $costCenter->id,
                'manager_id' => $user->id,
                'customer_id' => $customers->first()->id,
                'currency_id' => $currency->id,
                'country_id' => $country?->id,
                'code' => 'PRJ-003',
                'project_number' => 'P2024-003',
                'name' => 'Data Migration Project',
                'description' => 'Migration of legacy data to new ERP system',
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(45),
                'status' => 'draft',
                'budget' => 35000.00,
                'project_value' => 42000.00,
                'actual_cost' => 0.00,
                'progress' => 0.00,
                'customer_name' => $customers->first()->first_name ?? 'عميل تجريبي',
                'customer_email' => $customers->first()->email,
                'customer_phone' => $customers->first()->phone,
                'licensed_operator' => 'Data Solutions LLC',
                'currency_price' => 1.00,
                'include_vat' => false,
                'project_manager_name' => 'Omar Al-Rashid',
                'notes' => 'Critical data integrity requirements',
                'project_date' => Carbon::now()->addDays(5),
                'project_time' => Carbon::now()->setTime(8, 0),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'cost_center_id' => $costCenter->id,
                'manager_id' => $user->id,
                'customer_id' => $customers->first()->id,
                'currency_id' => $currency->id,
                'country_id' => $country?->id,
                'code' => 'PRJ-004',
                'project_number' => 'P2024-004',
                'name' => 'System Integration',
                'description' => 'Integration with third-party accounting systems',
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(30),
                'status' => 'open',
                'budget' => 60000.00,
                'project_value' => 72000.00,
                'actual_cost' => 24000.00,
                'progress' => 40.00,
                'customer_name' => $customers->first()->first_name ?? 'عميل تجريبي',
                'customer_email' => $customers->first()->email,
                'customer_phone' => $customers->first()->phone,
                'licensed_operator' => 'Integration Partners',
                'currency_price' => 1.00,
                'include_vat' => true,
                'project_manager_name' => 'Fatima Al-Nouri',
                'notes' => 'Complex API integrations required',
                'project_date' => Carbon::now()->subDays(10),
                'project_time' => Carbon::now()->setTime(14, 0),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'cost_center_id' => $costCenter->id,
                'manager_id' => $user->id,
                'customer_id' => $customers->first()->id,
                'currency_id' => $currency->id,
                'country_id' => $country?->id,
                'code' => 'PRJ-005',
                'project_number' => 'P2024-005',
                'name' => 'Training and Documentation',
                'description' => 'User training and system documentation creation',
                'start_date' => Carbon::now()->addDays(60),
                'end_date' => Carbon::now()->addDays(90),
                'status' => 'draft',
                'budget' => 25000.00,
                'project_value' => 30000.00,
                'actual_cost' => 0.00,
                'progress' => 0.00,
                'customer_name' => $customers->first()->first_name ?? 'عميل تجريبي',
                'customer_email' => $customers->first()->email,
                'customer_phone' => $customers->first()->phone,
                'licensed_operator' => 'Education Partners',
                'currency_price' => 1.00,
                'include_vat' => false,
                'project_manager_name' => 'Khalid Al-Mutairi',
                'notes' => 'Comprehensive training materials needed',
                'project_date' => Carbon::now()->addDays(60),
                'project_time' => Carbon::now()->setTime(9, 30),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        // Create each project
        foreach ($projectsData as $projectData) {
            $project = Project::create($projectData);
            $this->command->info("   ✓ Created project: {$project->name} (Customer: {$project->customer_name}, Currency ID: {$project->currency_id})");
        }

        $this->command->info('✅ All 5 projects seeded successfully with customer_id and currency_id!');
    }
}
