<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectsManagment\Models\ProjectDocument;
use Modules\ProjectsManagment\Models\Project;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Carbon\Carbon;

class ProjectDocumentSeeder extends Seeder
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

        if (!$user || !$company || $projects->isEmpty()) {
            $this->command->warn('⚠️  Required data not found. Please seed Projects first.');
            return;
        }

        $documents = [];

        // Documents for ERP System Development (Project 1)
        $project1 = $projects->where('code', 'PRJ-001')->first();
        if ($project1) {
            $documents = array_merge($documents, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'document_number' => 1,
                    'project_number' => $project1->project_number,
                    'project_name' => $project1->name,
                    'title' => 'Project Requirements Document',
                    'file_path' => '/documents/projects/PRJ-001/requirements.pdf',
                    'file_name' => 'ERP_Requirements_v1.0.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 2048576, // 2MB
                    'description' => 'Comprehensive requirements document for ERP system development',
                    'document_category' => 'requirements',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(28),
                    'version' => '1.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'document_number' => 2,
                    'project_number' => $project1->project_number,
                    'project_name' => $project1->name,
                    'title' => 'System Architecture Document',
                    'file_path' => '/documents/projects/PRJ-001/architecture.pdf',
                    'file_name' => 'ERP_Architecture_v2.1.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 3145728, // 3MB
                    'description' => 'Technical architecture and system design specifications',
                    'document_category' => 'technical',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(22),
                    'version' => '2.1',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'document_number' => 3,
                    'project_number' => $project1->project_number,
                    'project_name' => $project1->name,
                    'title' => 'Database Schema Design',
                    'file_path' => '/documents/projects/PRJ-001/database_schema.sql',
                    'file_name' => 'ERP_Database_Schema_v1.5.sql',
                    'file_type' => 'sql',
                    'file_size' => 512000, // 500KB
                    'description' => 'Complete database schema with tables, relationships, and indexes',
                    'document_category' => 'technical',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(18),
                    'version' => '1.5',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'document_number' => 4,
                    'project_number' => $project1->project_number,
                    'project_name' => $project1->name,
                    'title' => 'API Documentation',
                    'file_path' => '/documents/projects/PRJ-001/api_docs.html',
                    'file_name' => 'ERP_API_Documentation_v1.2.html',
                    'file_type' => 'html',
                    'file_size' => 1048576, // 1MB
                    'description' => 'RESTful API documentation with endpoints and examples',
                    'document_category' => 'documentation',
                    'status' => 'draft',
                    'upload_date' => Carbon::now()->subDays(5),
                    'version' => '1.2',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Documents for Mobile Application Development (Project 2)
        $project2 = $projects->where('code', 'PRJ-002')->first();
        if ($project2) {
            $documents = array_merge($documents, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'document_number' => 1,
                    'project_number' => $project2->project_number,
                    'project_name' => $project2->name,
                    'title' => 'Mobile App UI/UX Design',
                    'file_path' => '/documents/projects/PRJ-002/ui_design.figma',
                    'file_name' => 'Mobile_App_Design_v3.0.figma',
                    'file_type' => 'figma',
                    'file_size' => 5242880, // 5MB
                    'description' => 'Complete UI/UX design mockups and prototypes',
                    'document_category' => 'design',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(12),
                    'version' => '3.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'document_number' => 2,
                    'project_number' => $project2->project_number,
                    'project_name' => $project2->name,
                    'title' => 'Mobile Development Guidelines',
                    'file_path' => '/documents/projects/PRJ-002/dev_guidelines.md',
                    'file_name' => 'Mobile_Dev_Guidelines_v1.0.md',
                    'file_type' => 'md',
                    'file_size' => 204800, // 200KB
                    'description' => 'Development standards and coding guidelines for mobile app',
                    'document_category' => 'documentation',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(10),
                    'version' => '1.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project2->id,
                    'document_number' => 3,
                    'project_number' => $project2->project_number,
                    'project_name' => $project2->name,
                    'title' => 'Testing Strategy Document',
                    'file_path' => '/documents/projects/PRJ-002/testing_strategy.pdf',
                    'file_name' => 'Mobile_Testing_Strategy_v1.1.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 1572864, // 1.5MB
                    'description' => 'Comprehensive testing strategy for mobile application',
                    'document_category' => 'testing',
                    'status' => 'draft',
                    'upload_date' => Carbon::now()->subDays(3),
                    'version' => '1.1',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Documents for Data Migration Project (Project 3)
        $project3 = $projects->where('code', 'PRJ-003')->first();
        if ($project3) {
            $documents = array_merge($documents, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project3->id,
                    'document_number' => 1,
                    'project_number' => $project3->project_number,
                    'project_name' => $project3->name,
                    'title' => 'Data Migration Plan',
                    'file_path' => '/documents/projects/PRJ-003/migration_plan.pdf',
                    'file_name' => 'Data_Migration_Plan_v1.0.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 2621440, // 2.5MB
                    'description' => 'Detailed plan for migrating legacy data to new system',
                    'document_category' => 'planning',
                    'status' => 'draft',
                    'upload_date' => Carbon::now()->addDays(3),
                    'version' => '1.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Documents for System Integration (Project 4)
        $project4 = $projects->where('code', 'PRJ-004')->first();
        if ($project4) {
            $documents = array_merge($documents, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'document_number' => 1,
                    'project_number' => $project4->project_number,
                    'project_name' => $project4->name,
                    'title' => 'Integration Specifications',
                    'file_path' => '/documents/projects/PRJ-004/integration_specs.pdf',
                    'file_name' => 'Integration_Specifications_v2.0.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 1835008, // 1.75MB
                    'description' => 'Technical specifications for third-party system integrations',
                    'document_category' => 'technical',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(8),
                    'version' => '2.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project4->id,
                    'document_number' => 2,
                    'project_number' => $project4->project_number,
                    'project_name' => $project4->name,
                    'title' => 'API Security Guidelines',
                    'file_path' => '/documents/projects/PRJ-004/security_guidelines.pdf',
                    'file_name' => 'API_Security_Guidelines_v1.0.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 1048576, // 1MB
                    'description' => 'Security best practices for API integrations',
                    'document_category' => 'security',
                    'status' => 'approved',
                    'upload_date' => Carbon::now()->subDays(6),
                    'version' => '1.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        // Documents for Training and Documentation (Project 5)
        $project5 = $projects->where('code', 'PRJ-005')->first();
        if ($project5) {
            $documents = array_merge($documents, [
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'project_id' => $project5->id,
                    'document_number' => 1,
                    'project_number' => $project5->project_number,
                    'project_name' => $project5->name,
                    'title' => 'Training Curriculum',
                    'file_path' => '/documents/projects/PRJ-005/training_curriculum.pdf',
                    'file_name' => 'Training_Curriculum_v1.0.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 1310720, // 1.25MB
                    'description' => 'Comprehensive training curriculum for end users',
                    'document_category' => 'training',
                    'status' => 'draft',
                    'upload_date' => Carbon::now()->addDays(55),
                    'version' => '1.0',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            ]);
        }

        foreach ($documents as $documentData) {
            // Ensure all documents have required fields
            $branch = Branch::where('company_id', $company->id)->first();
            $fiscalYear = FiscalYear::where('company_id', $company->id)->first();

            $documentData['branch_id'] = $branch?->id;
            $documentData['fiscal_year_id'] = $fiscalYear?->id;

            // Fix status values to match enum
            if (isset($documentData['status'])) {
                if ($documentData['status'] === 'approved' || $documentData['status'] === 'draft') {
                    $documentData['status'] = 'active';
                } elseif ($documentData['status'] === 'pending') {
                    $documentData['status'] = 'active';
                }
            }

            ProjectDocument::create($documentData);
        }

        $this->command->info('✅ Project Documents seeded successfully!');
    }
}
