<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\ProjectRisk;
use Modules\ProjectsManagment\Models\Project;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\HumanResources\Models\Employee;

class ProjectRiskSeeder extends Seeder
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
        // Try to get first employee, but handle if Employee model doesn't exist or has no soft deletes
        try {
            $employee = Employee::withoutGlobalScopes()->first();
        } catch (\Exception $e) {
            $employee = null;
        }

        if (!$user || !$company || $projects->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Projects first.');
            return;
        }

        $risks = [];

        // Risks for ERP System Development (Project 1)
        $project1 = $projects->where('code', 'PRJ-001')->first();
        if ($project1) {
            $risks = array_merge($risks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'title' => 'Technical Complexity Risk',
                    'description' => 'The ERP system integration may be more complex than initially estimated, leading to delays and cost overruns.',
                    'impact' => 'high',
                    'probability' => 'medium',
                    'mitigation_plan' => 'Conduct thorough technical analysis, allocate additional buffer time, and engage senior developers for complex modules.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'title' => 'Resource Availability Risk',
                    'description' => 'Key developers may become unavailable due to other commitments or personal reasons.',
                    'impact' => 'high',
                    'probability' => 'low',
                    'mitigation_plan' => 'Maintain backup developer pool, cross-train team members, and document all development processes.',
                    'status' => 'mitigated',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'title' => 'Data Migration Risk',
                    'description' => 'Legacy data may be corrupted or incompatible with new system structure.',
                    'impact' => 'medium',
                    'probability' => 'medium',
                    'mitigation_plan' => 'Perform comprehensive data audit, create data validation scripts, and maintain backup of original data.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'title' => 'Performance Risk',
                    'description' => 'System may not meet performance requirements under high load conditions.',
                    'impact' => 'medium',
                    'probability' => 'low',
                    'mitigation_plan' => 'Implement performance testing early, optimize database queries, and plan for scalable infrastructure.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Risks for Mobile Application Development (Project 2)
        $project2 = $projects->where('code', 'PRJ-002')->first();
        if ($project2) {
            $risks = array_merge($risks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'title' => 'Platform Compatibility Risk',
                    'description' => 'App may not work consistently across different mobile platforms and device versions.',
                    'impact' => 'medium',
                    'probability' => 'medium',
                    'mitigation_plan' => 'Extensive testing on multiple devices, use platform-specific optimizations, and maintain device compatibility matrix.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'title' => 'App Store Approval Risk',
                    'description' => 'Mobile app may face rejection from app stores due to policy violations or technical issues.',
                    'impact' => 'high',
                    'probability' => 'low',
                    'mitigation_plan' => 'Follow app store guidelines strictly, conduct pre-submission reviews, and prepare for potential resubmissions.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'title' => 'User Experience Risk',
                    'description' => 'Mobile interface may not meet user expectations or usability standards.',
                    'impact' => 'medium',
                    'probability' => 'low',
                    'mitigation_plan' => 'Conduct user testing sessions, gather feedback early, and iterate on design based on user input.',
                    'status' => 'mitigated',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Risks for Data Migration Project (Project 3)
        $project3 = $projects->where('code', 'PRJ-003')->first();
        if ($project3) {
            $risks = array_merge($risks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'title' => 'Data Loss Risk',
                    'description' => 'Critical data may be lost during the migration process due to technical failures or human error.',
                    'impact' => 'high',
                    'probability' => 'low',
                    'mitigation_plan' => 'Create multiple backups, implement rollback procedures, and conduct migration in phases with validation checkpoints.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'title' => 'Data Quality Risk',
                    'description' => 'Legacy data may contain inconsistencies, duplicates, or invalid entries that affect migration quality.',
                    'impact' => 'medium',
                    'probability' => 'high',
                    'mitigation_plan' => 'Implement data cleansing procedures, create validation rules, and establish data quality metrics.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'title' => 'Downtime Risk',
                    'description' => 'Extended system downtime during migration may impact business operations.',
                    'impact' => 'high',
                    'probability' => 'medium',
                    'mitigation_plan' => 'Schedule migration during off-peak hours, implement parallel systems, and prepare contingency plans.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Risks for System Integration (Project 4)
        $project4 = $projects->where('code', 'PRJ-004')->first();
        if ($project4) {
            $risks = array_merge($risks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'title' => 'Third-party API Risk',
                    'description' => 'External APIs may change without notice, causing integration failures.',
                    'impact' => 'medium',
                    'probability' => 'medium',
                    'mitigation_plan' => 'Monitor API versions, implement error handling, and maintain fallback mechanisms.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'title' => 'Security Risk',
                    'description' => 'Integration points may introduce security vulnerabilities or data exposure risks.',
                    'impact' => 'high',
                    'probability' => 'low',
                    'mitigation_plan' => 'Implement security best practices, conduct security audits, and use encrypted communication channels.',
                    'status' => 'mitigated',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Risks for Training and Documentation (Project 5)
        $project5 = $projects->where('code', 'PRJ-005')->first();
        if ($project5) {
            $risks = array_merge($risks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project5->id,
                    'title' => 'User Adoption Risk',
                    'description' => 'End users may resist adopting the new system due to complexity or change resistance.',
                    'impact' => 'high',
                    'probability' => 'medium',
                    'mitigation_plan' => 'Develop comprehensive training programs, provide ongoing support, and implement gradual rollout strategy.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project5->id,
                    'title' => 'Documentation Quality Risk',
                    'description' => 'Documentation may be incomplete or unclear, leading to user confusion and support issues.',
                    'impact' => 'medium',
                    'probability' => 'low',
                    'mitigation_plan' => 'Implement documentation review process, gather user feedback, and maintain living documentation.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        foreach ($risks as $riskData) {
            // Ensure all risks have required fields
            $branch = Branch::where('company_id', $company->id)->first();
            $fiscalYear = FiscalYear::where('company_id', $company->id)->first();

            $riskData['branch_id'] = $branch?->id;
            $riskData['fiscal_year_id'] = $fiscalYear?->id;

            ProjectRisk::create($riskData);
        }

        $this->command->info('✅ Project Risks seeded successfully!');
    }
}
