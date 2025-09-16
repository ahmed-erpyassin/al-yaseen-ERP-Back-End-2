<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\ProjectMilestone;
use Modules\ProjectsManagment\Models\Project;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Carbon\Carbon;

class ProjectMilestoneSeeder extends Seeder
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
        $branch = Branch::where('company_id', $company->id)->first();
        $fiscalYear = FiscalYear::where('company_id', $company->id)->first();

        if (!$user || !$company || $projects->isEmpty() || !$branch || !$fiscalYear) {
            $this->command->warn('⚠️  Required data not found. Please seed Projects, Branch, and FiscalYear first.');
            return;
        }

        $milestones = [];

        // Milestones for ERP System Development (Project 1)
        $project1 = $projects->where('code', 'PRJ-001')->first();
        if ($project1) {
            $milestones = array_merge($milestones, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'project_id' => $project1->id,
                    'milestone_number' => 1,
                    'name' => 'Requirements Analysis',
                    'description' => 'Complete analysis of system requirements and specifications',
                    'start_date' => Carbon::now()->subDays(30),
                    'end_date' => Carbon::now()->subDays(20),
                    'status' => 'completed',
                    'progress' => 100.00,
                    'notes' => 'All requirements documented and approved',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'project_id' => $project1->id,
                    'milestone_number' => 2,
                    'name' => 'System Design',
                    'description' => 'Database design and system architecture',
                    'start_date' => Carbon::now()->subDays(20),
                    'end_date' => Carbon::now()->subDays(10),
                    'status' => 'completed',
                    'progress' => 100.00,
                    'notes' => 'Architecture approved by technical team',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'milestone_number' => 3,
                    'name' => 'Core Development',
                    'description' => 'Development of core modules and functionality',
                    'start_date' => Carbon::now()->subDays(10),
                    'end_date' => Carbon::now()->addDays(30),
                    'status' => 'active',
                    'progress' => 45.00,
                    'notes' => 'Currently developing inventory module',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'milestone_number' => 4,
                    'name' => 'Testing & QA',
                    'description' => 'Comprehensive testing and quality assurance',
                    'start_date' => Carbon::now()->addDays(30),
                    'end_date' => Carbon::now()->addDays(60),
                    'status' => 'pending',
                    'progress' => 0.00,
                    'notes' => 'Waiting for core development completion',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Milestones for Mobile Application Development (Project 2)
        $project2 = $projects->where('code', 'PRJ-002')->first();
        if ($project2) {
            $milestones = array_merge($milestones, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'milestone_number' => 1,
                    'name' => 'UI/UX Design',
                    'description' => 'Mobile application user interface and experience design',
                    'start_date' => Carbon::now()->subDays(15),
                    'end_date' => Carbon::now()->subDays(5),
                    'status' => 'completed',
                    'progress' => 100.00,
                    'notes' => 'Design approved by client',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'milestone_number' => 2,
                    'name' => 'Frontend Development',
                    'description' => 'Mobile app frontend development using React Native',
                    'start_date' => Carbon::now()->subDays(5),
                    'end_date' => Carbon::now()->addDays(25),
                    'status' => 'active',
                    'progress' => 30.00,
                    'notes' => 'Basic screens implemented',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'milestone_number' => 3,
                    'name' => 'API Integration',
                    'description' => 'Integration with backend APIs',
                    'start_date' => Carbon::now()->addDays(20),
                    'end_date' => Carbon::now()->addDays(45),
                    'status' => 'pending',
                    'progress' => 0.00,
                    'notes' => 'Waiting for API documentation',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Milestones for Data Migration Project (Project 3)
        $project3 = $projects->where('code', 'PRJ-003')->first();
        if ($project3) {
            $milestones = array_merge($milestones, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'milestone_number' => 1,
                    'name' => 'Data Analysis',
                    'description' => 'Analysis of legacy data structure and quality',
                    'start_date' => Carbon::now()->addDays(5),
                    'end_date' => Carbon::now()->addDays(15),
                    'status' => 'pending',
                    'progress' => 0.00,
                    'notes' => 'Scheduled to start next week',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'milestone_number' => 2,
                    'name' => 'Migration Scripts',
                    'description' => 'Development of data migration scripts',
                    'start_date' => Carbon::now()->addDays(15),
                    'end_date' => Carbon::now()->addDays(30),
                    'status' => 'pending',
                    'progress' => 0.00,
                    'notes' => 'Depends on data analysis completion',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'milestone_number' => 3,
                    'name' => 'Data Migration',
                    'description' => 'Execute data migration and validation',
                    'start_date' => Carbon::now()->addDays(30),
                    'end_date' => Carbon::now()->addDays(45),
                    'status' => 'pending',
                    'progress' => 0.00,
                    'notes' => 'Final migration phase',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Milestones for System Integration (Project 4)
        $project4 = $projects->where('code', 'PRJ-004')->first();
        if ($project4) {
            $milestones = array_merge($milestones, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'milestone_number' => 1,
                    'name' => 'API Development',
                    'description' => 'Development of integration APIs',
                    'start_date' => Carbon::now()->subDays(10),
                    'end_date' => Carbon::now()->addDays(10),
                    'status' => 'active',
                    'progress' => 70.00,
                    'notes' => 'Most APIs completed and tested',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'milestone_number' => 2,
                    'name' => 'Third-party Integration',
                    'description' => 'Integration with external accounting systems',
                    'start_date' => Carbon::now()->addDays(10),
                    'end_date' => Carbon::now()->addDays(30),
                    'status' => 'pending',
                    'progress' => 0.00,
                    'notes' => 'Waiting for third-party credentials',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        foreach ($milestones as $milestoneData) {
            // Ensure all milestones have required fields
            $milestoneData['branch_id'] = $branch->id;
            $milestoneData['fiscal_year_id'] = $fiscalYear->id;

            // Fix status values
            if (isset($milestoneData['status'])) {
                if ($milestoneData['status'] === 'active') {
                    $milestoneData['status'] = 'in_progress';
                } elseif ($milestoneData['status'] === 'pending') {
                    $milestoneData['status'] = 'not_started';
                }
            }

            ProjectMilestone::create($milestoneData);
        }

        $this->command->info('✅ Project Milestones seeded successfully!');
    }
}
