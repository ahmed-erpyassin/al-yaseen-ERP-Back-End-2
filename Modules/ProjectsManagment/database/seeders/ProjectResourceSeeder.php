<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\ProjectResource;
use Modules\ProjectsManagment\Models\Project;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Suppliers\Models\Supplier;

class ProjectResourceSeeder extends Seeder
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
        $suppliers = Supplier::all();

        if (!$user || !$company || $projects->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Projects first.');
            return;
        }

        if ($suppliers->isEmpty()) {
            $this->command->warn('⚠️  No suppliers found. Please seed Suppliers first.');
            return;
        }

        // Helper function to get supplier by index (cycling through available suppliers)
        $getSupplier = function($index) use ($suppliers) {
            return $suppliers->get($index % $suppliers->count());
        };

        $resources = [];

        // Resources for ERP System Development (Project 1)
        $project1 = $projects->where('code', 'PRJ-001')->first();
        if ($project1) {
            $resources = array_merge($resources, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'supplier_id' => $getSupplier(0)->id,
                    'role' => 'Senior Developer',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 8000.00,
                    'notes' => 'Lead developer for backend development',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'supplier_id' => $getSupplier(1)->id,
                    'role' => 'Frontend Developer',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 6500.00,
                    'notes' => 'Vue.js specialist for frontend development',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'supplier_id' => $getSupplier(2)->id,
                    'role' => 'Database Administrator',
                    'allocation' => 'Part-time',
                    'allocation_percentage' => 50.00,
                    'allocation_value' => 3500.00,
                    'notes' => 'Database optimization and maintenance',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'supplier_id' => $getSupplier(3)->id,
                    'role' => 'Development Server',
                    'allocation' => 'Dedicated',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 500.00,
                    'notes' => 'AWS EC2 instance for development environment',
                    'status' => 'active',
                    'resource_type' => 'equipment',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Resources for Mobile Application Development (Project 2)
        $project2 = $projects->where('code', 'PRJ-002')->first();
        if ($project2) {
            $resources = array_merge($resources, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'supplier_id' => $getSupplier(4)->id,
                    'role' => 'Mobile Developer',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 7000.00,
                    'notes' => 'React Native specialist',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'supplier_id' => $getSupplier(0)->id,
                    'role' => 'UI/UX Designer',
                    'allocation' => 'Part-time',
                    'allocation_percentage' => 30.00,
                    'allocation_value' => 2000.00,
                    'notes' => 'Mobile interface design specialist',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'supplier_id' => $getSupplier(1)->id,
                    'role' => 'Testing Devices',
                    'allocation' => 'Shared',
                    'allocation_percentage' => 25.00,
                    'allocation_value' => 800.00,
                    'notes' => 'iOS and Android devices for testing',
                    'status' => 'active',
                    'resource_type' => 'equipment',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Resources for Data Migration Project (Project 3)
        $project3 = $projects->where('code', 'PRJ-003')->first();
        if ($project3) {
            $resources = array_merge($resources, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'supplier_id' => $getSupplier(2)->id,
                    'role' => 'Data Analyst',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 5500.00,
                    'notes' => 'Legacy data analysis and mapping',
                    'status' => 'planned',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'supplier_id' => $getSupplier(3)->id,
                    'role' => 'Migration Specialist',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 6000.00,
                    'notes' => 'ETL processes and data validation',
                    'status' => 'planned',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'supplier_id' => $getSupplier(4)->id,
                    'role' => 'Migration Server',
                    'allocation' => 'Temporary',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 1200.00,
                    'notes' => 'High-performance server for data processing',
                    'status' => 'planned',
                    'resource_type' => 'equipment',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Resources for System Integration (Project 4)
        $project4 = $projects->where('code', 'PRJ-004')->first();
        if ($project4) {
            $resources = array_merge($resources, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'supplier_id' => $getSupplier(0)->id,
                    'role' => 'Integration Specialist',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 7500.00,
                    'notes' => 'API development and third-party integrations',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'supplier_id' => $getSupplier(1)->id,
                    'role' => 'Systems Analyst',
                    'allocation' => 'Part-time',
                    'allocation_percentage' => 60.00,
                    'allocation_value' => 4200.00,
                    'notes' => 'Requirements analysis and system design',
                    'status' => 'active',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'supplier_id' => $getSupplier(2)->id,
                    'role' => 'Integration Tools',
                    'allocation' => 'Licensed',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 2000.00,
                    'notes' => 'API testing and monitoring tools',
                    'status' => 'active',
                    'resource_type' => 'software',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Resources for Training and Documentation (Project 5)
        $project5 = $projects->where('code', 'PRJ-005')->first();
        if ($project5) {
            $resources = array_merge($resources, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project5->id,
                    'supplier_id' => $getSupplier(3)->id,
                    'role' => 'Technical Writer',
                    'allocation' => 'Full-time',
                    'allocation_percentage' => 100.00,
                    'allocation_value' => 4500.00,
                    'notes' => 'Documentation and training materials creation',
                    'status' => 'planned',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project5->id,
                    'supplier_id' => $getSupplier(4)->id,
                    'role' => 'Training Coordinator',
                    'allocation' => 'Part-time',
                    'allocation_percentage' => 50.00,
                    'allocation_value' => 2500.00,
                    'notes' => 'Training session planning and execution',
                    'status' => 'planned',
                    'resource_type' => 'human',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        foreach ($resources as $resourceData) {
            // Ensure all resources have required fields
            $branch = Branch::where('company_id', $company->id)->first();
            $fiscalYear = FiscalYear::where('company_id', $company->id)->first();

            $resourceData['branch_id'] = $branch?->id;
            $resourceData['fiscal_year_id'] = $fiscalYear?->id;

            // Fix resource_type values to match enum
            if (isset($resourceData['resource_type'])) {
                if ($resourceData['resource_type'] === 'human') {
                    $resourceData['resource_type'] = 'internal';
                } elseif ($resourceData['resource_type'] === 'equipment') {
                    $resourceData['resource_type'] = 'supplier';
                } elseif ($resourceData['resource_type'] === 'software') {
                    $resourceData['resource_type'] = 'supplier';
                }
            }

            // Fix status values to match enum
            if (isset($resourceData['status'])) {
                if ($resourceData['status'] === 'planned') {
                    $resourceData['status'] = 'active';
                }
            }

            ProjectResource::create($resourceData);
        }

        $this->command->info('✅ Project Resources seeded successfully!');
    }
}
