<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\ProjectTask;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Models\ProjectMilestone;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Carbon\Carbon;

class ProjectTaskSeeder extends Seeder
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
        $milestones = ProjectMilestone::all();

        if (!$user || !$company || $projects->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Projects and Milestones first.');
            return;
        }

        $tasks = [];

        // Tasks for ERP System Development (Project 1)
        $project1 = $projects->where('code', 'PRJ-001')->first();
        $milestone1 = $milestones->where('project_id', $project1?->id)->where('milestone_number', 3)->first();
        
        if ($project1 && $milestone1) {
            $tasks = array_merge($tasks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'milestone_id' => $milestone1->id,
                    'assigned_to' => $user->id,
                    'title' => 'Database Schema Design',
                    'task_name' => 'Design and implement database schema for inventory module',
                    'description' => 'Create comprehensive database schema including tables, relationships, and indexes for the inventory management module',
                    'notes' => 'Focus on performance optimization and data integrity',
                    'records' => ['Database design document created', 'Schema reviewed by team'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'start_date' => Carbon::now()->subDays(8),
                    'due_date' => Carbon::now()->subDays(3),
                    'estimated_hours' => 40,
                    'actual_hours' => 38,
                    'progress' => 100.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'milestone_id' => $milestone1->id,
                    'assigned_to' => $user->id,
                    'title' => 'API Development',
                    'task_name' => 'Develop REST APIs for inventory operations',
                    'description' => 'Create RESTful APIs for CRUD operations on inventory items, stock movements, and warehouse management',
                    'notes' => 'Include proper validation and error handling',
                    'records' => ['API endpoints defined', 'Authentication implemented'],
                    'priority' => 'high',
                    'status' => 'in_progress',
                    'start_date' => Carbon::now()->subDays(5),
                    'due_date' => Carbon::now()->addDays(10),
                    'estimated_hours' => 60,
                    'actual_hours' => 25,
                    'progress' => 65.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'milestone_id' => $milestone1->id,
                    'assigned_to' => $user->id,
                    'title' => 'Frontend Components',
                    'task_name' => 'Develop Vue.js components for inventory management',
                    'description' => 'Create reusable Vue.js components for inventory item management, stock tracking, and reporting',
                    'notes' => 'Ensure responsive design and accessibility',
                    'records' => ['Component structure planned'],
                    'priority' => 'medium',
                    'status' => 'pending',
                    'start_date' => Carbon::now()->addDays(5),
                    'due_date' => Carbon::now()->addDays(20),
                    'estimated_hours' => 50,
                    'actual_hours' => 0,
                    'progress' => 0.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'milestone_id' => $milestone1->id,
                    'assigned_to' => $user->id,
                    'title' => 'Unit Testing',
                    'task_name' => 'Write comprehensive unit tests',
                    'description' => 'Develop unit tests for all inventory module functionality with minimum 90% code coverage',
                    'notes' => 'Use PHPUnit for backend and Jest for frontend',
                    'records' => [],
                    'priority' => 'medium',
                    'status' => 'pending',
                    'start_date' => Carbon::now()->addDays(15),
                    'due_date' => Carbon::now()->addDays(25),
                    'estimated_hours' => 30,
                    'actual_hours' => 0,
                    'progress' => 0.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Tasks for Mobile Application Development (Project 2)
        $project2 = $projects->where('code', 'PRJ-002')->first();
        $milestone2 = $milestones->where('project_id', $project2?->id)->where('milestone_number', 2)->first();
        
        if ($project2 && $milestone2) {
            $tasks = array_merge($tasks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'milestone_id' => $milestone2->id,
                    'assigned_to' => $user->id,
                    'title' => 'Login Screen',
                    'task_name' => 'Implement user authentication screen',
                    'description' => 'Create login screen with biometric authentication support',
                    'notes' => 'Support both fingerprint and face recognition',
                    'records' => ['UI design completed', 'Basic authentication implemented'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'start_date' => Carbon::now()->subDays(5),
                    'due_date' => Carbon::now()->subDays(1),
                    'estimated_hours' => 16,
                    'actual_hours' => 18,
                    'progress' => 100.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'milestone_id' => $milestone2->id,
                    'assigned_to' => $user->id,
                    'title' => 'Inventory Dashboard',
                    'task_name' => 'Create mobile inventory dashboard',
                    'description' => 'Develop responsive dashboard showing inventory statistics and quick actions',
                    'notes' => 'Include charts and real-time data updates',
                    'records' => ['Dashboard layout designed'],
                    'priority' => 'high',
                    'status' => 'in_progress',
                    'start_date' => Carbon::now()->subDays(2),
                    'due_date' => Carbon::now()->addDays(8),
                    'estimated_hours' => 24,
                    'actual_hours' => 8,
                    'progress' => 35.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'milestone_id' => $milestone2->id,
                    'assigned_to' => $user->id,
                    'title' => 'Barcode Scanner',
                    'task_name' => 'Implement barcode scanning functionality',
                    'description' => 'Add camera-based barcode scanning for inventory items',
                    'notes' => 'Support multiple barcode formats',
                    'records' => [],
                    'priority' => 'medium',
                    'status' => 'pending',
                    'start_date' => Carbon::now()->addDays(8),
                    'due_date' => Carbon::now()->addDays(15),
                    'estimated_hours' => 20,
                    'actual_hours' => 0,
                    'progress' => 0.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Tasks for System Integration (Project 4)
        $project4 = $projects->where('code', 'PRJ-004')->first();
        $milestone4 = $milestones->where('project_id', $project4?->id)->where('milestone_number', 1)->first();
        
        if ($project4 && $milestone4) {
            $tasks = array_merge($tasks, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'milestone_id' => $milestone4->id,
                    'assigned_to' => $user->id,
                    'title' => 'Accounting API',
                    'task_name' => 'Develop accounting system integration API',
                    'description' => 'Create API endpoints for synchronizing financial data with external accounting systems',
                    'notes' => 'Ensure data consistency and error handling',
                    'records' => ['API specification completed', 'Authentication implemented'],
                    'priority' => 'high',
                    'status' => 'in_progress',
                    'start_date' => Carbon::now()->subDays(8),
                    'due_date' => Carbon::now()->addDays(5),
                    'estimated_hours' => 45,
                    'actual_hours' => 32,
                    'progress' => 75.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'milestone_id' => $milestone4->id,
                    'assigned_to' => $user->id,
                    'title' => 'Data Mapping',
                    'task_name' => 'Create data mapping configuration',
                    'description' => 'Develop flexible data mapping system for different accounting software formats',
                    'notes' => 'Support QuickBooks, SAP, and Oracle formats',
                    'records' => ['Mapping structure defined'],
                    'priority' => 'medium',
                    'status' => 'in_progress',
                    'start_date' => Carbon::now()->subDays(5),
                    'due_date' => Carbon::now()->addDays(8),
                    'estimated_hours' => 25,
                    'actual_hours' => 12,
                    'progress' => 50.00,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        foreach ($tasks as $taskData) {
            // Ensure all tasks have required fields
            $branch = Branch::where('company_id', $company->id)->first();
            $fiscalYear = FiscalYear::where('company_id', $company->id)->first();

            $taskData['branch_id'] = $branch?->id;
            $taskData['fiscal_year_id'] = $fiscalYear?->id;

            // Fix status values to match enum
            if (isset($taskData['status'])) {
                if ($taskData['status'] === 'completed') {
                    $taskData['status'] = 'done';
                } elseif ($taskData['status'] === 'active') {
                    $taskData['status'] = 'in_progress';
                } elseif ($taskData['status'] === 'pending') {
                    $taskData['status'] = 'to_do';
                }
            }

            ProjectTask::create($taskData);
        }

        $this->command->info('✅ Project Tasks seeded successfully!');
    }
}
