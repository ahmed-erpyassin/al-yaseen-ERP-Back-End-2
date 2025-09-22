<?php

namespace Modules\HumanResources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\Models\Department;
use Modules\HumanResources\Models\JobTitle;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\FinancialAccounts\Models\Currency;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Employees...');

        // Get required dependencies
        $user = User::first();
        $company = Company::first();
        $currency = Currency::first();
        $branch = Branch::first();
        $fiscalYear = FiscalYear::first();

        // Check for required dependencies
        if (!$user || !$company) {
            $this->command->warn('⚠️  Required data not found. Please seed Users and Companies modules first.');
            return;
        }

        // Create default currency if not found
        if (!$currency) {
            $currency = Currency::firstOrCreate([
                'company_id' => $company->id,
                'code' => 'USD'
            ], [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }

        // Create default branch if not found
        if (!$branch) {
            $branch = Branch::firstOrCreate([
                'company_id' => $company->id,
                'code' => 'BR-001'
            ], [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'currency_id' => $currency->id,
                'manager_id' => $user->id,
                'code' => 'BR-001',
                'name' => 'Main Branch',
                'address' => 'Main Office Address',
                'landline' => '+966112345678',
                'mobile' => '+966501234567',
                'email' => 'branch@company.com',
                'tax_number' => 'TAX-001',
                'timezone' => 'Asia/Riyadh',
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }

        // Create default fiscal year if not found
        if (!$fiscalYear) {
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
        }

        // Check if departments already exist, if not create them
        $existingDepartments = Department::withoutGlobalScopes()->where('company_id', $company->id)->get();

        if ($existingDepartments->count() >= 4) {
            // Use existing departments
            $createdDepartments = $existingDepartments->take(4)->all();
            $this->command->info('✅ Using existing departments');
        } else {
            // Create departments first
            $departments = [
            [
                'name' => 'Information Technology',
                'number' => 1001,
                'address' => 'IT Department, 3rd Floor',
                'work_phone' => '+966112345001',
                'home_phone' => '+966112345002',
                'fax' => '+966112345003',
                'statement' => 'قسم تقنية المعلومات',
                'statement_en' => 'Information Technology Department',
                'project_status' => 'inprogress',
                'status' => 'active',
                'proposed_start_date' => Carbon::create(2024, 1, 1),
                'proposed_end_date' => Carbon::create(2024, 12, 31),
                'actual_start_date' => Carbon::create(2024, 1, 1),
                'actual_end_date' => Carbon::create(2024, 12, 31),
            ],
            [
                'name' => 'Human Resources',
                'number' => 1002,
                'address' => 'HR Department, 2nd Floor',
                'work_phone' => '+966112345011',
                'home_phone' => '+966112345012',
                'fax' => '+966112345013',
                'statement' => 'قسم الموارد البشرية',
                'statement_en' => 'Human Resources Department',
                'project_status' => 'inprogress',
                'status' => 'active',
                'proposed_start_date' => Carbon::create(2024, 1, 1),
                'proposed_end_date' => Carbon::create(2024, 12, 31),
                'actual_start_date' => Carbon::create(2024, 1, 1),
                'actual_end_date' => Carbon::create(2024, 12, 31),
            ],
            [
                'name' => 'Finance',
                'number' => 1003,
                'address' => 'Finance Department, 1st Floor',
                'work_phone' => '+966112345021',
                'home_phone' => '+966112345022',
                'fax' => '+966112345023',
                'statement' => 'قسم المالية',
                'statement_en' => 'Finance Department',
                'project_status' => 'inprogress',
                'status' => 'active',
                'proposed_start_date' => Carbon::create(2024, 1, 1),
                'proposed_end_date' => Carbon::create(2024, 12, 31),
                'actual_start_date' => Carbon::create(2024, 1, 1),
                'actual_end_date' => Carbon::create(2024, 12, 31),
            ],
            [
                'name' => 'Sales & Marketing',
                'number' => 1004,
                'address' => 'Sales Department, Ground Floor',
                'work_phone' => '+966112345031',
                'home_phone' => '+966112345032',
                'fax' => '+966112345033',
                'statement' => 'قسم المبيعات والتسويق',
                'statement_en' => 'Sales & Marketing Department',
                'project_status' => 'inprogress',
                'status' => 'active',
                'proposed_start_date' => Carbon::create(2024, 1, 1),
                'proposed_end_date' => Carbon::create(2024, 12, 31),
                'actual_start_date' => Carbon::create(2024, 1, 1),
                'actual_end_date' => Carbon::create(2024, 12, 31),
            ],
        ];

        $createdDepartments = [];
        foreach ($departments as $deptData) {
            // Use withoutGlobalScopes to bypass soft delete constraints
            $department = Department::withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'number' => $deptData['number']
                ],
                array_merge($deptData, [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'manager_id' => $user->id,
                    'parent_id' => 1,
                    'funder_id' => 1,
                    'budget_id' => 1,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'deleted_by' => $user->id,
                ])
            );
            $createdDepartments[] = $department;
        }
        }

        // Create job titles for each department
        $jobTitles = [];
        foreach ($createdDepartments as $index => $department) {
            $titles = [
                0 => ['Software Developer', 'System Administrator', 'IT Manager'],
                1 => ['HR Manager', 'HR Specialist', 'Recruiter'],
                2 => ['Financial Manager', 'Accountant', 'Financial Analyst'],
                3 => ['Sales Manager', 'Sales Representative', 'Marketing Specialist'],
            ];

            $deptTitles = $titles[$index] ?? ['General Employee'];

            foreach ($deptTitles as $titleName) {
                $jobTitle = JobTitle::withoutGlobalScopes()->firstOrCreate([
                    'company_id' => $company->id,
                    'department_id' => $department->id,
                    'name' => $titleName
                ], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'deleted_by' => $user->id,
                ]);
                $jobTitles[] = $jobTitle;
            }
        }

        // Employee data array
        $employees = [
            [
                'employee_number' => 'EMP-001',
                'code' => 'TECH-001',
                'nickname' => 'أحمد',
                'first_name' => 'أحمد',
                'second_name' => 'محمد',
                'third_name' => 'علي',
                'phone1' => '0599123456',
                'phone2' => '082345678',
                'email' => 'ahmed.ali@company.com',
                'birth_date' => Carbon::create(1990, 5, 15),
                'address' => 'رام الله، فلسطين',
                'national_id' => '123456789',
                'id_number' => 'ID-001',
                'gender' => 'male',
                'wives_count' => 1,
                'children_count' => 2,
                'dependents_count' => '3',
                'car_number' => 'ABC-123',
                'is_driver' => true,
                'is_sales' => false,
                'hire_date' => Carbon::create(2023, 1, 15),
                'employee_code' => 'TECH-001',
                'employee_identifier' => 'TECH-ID-001',
                'job_address' => 'IT Department, 3rd Floor',
                'salary' => '5000.00',
                'billing_rate' => 75.00,
                'monthly_discount' => 0.00,
                'notes' => 'Senior Software Developer with 5+ years experience',
                'department_index' => 0, // IT Department
            ],
            [
                'employee_number' => 'EMP-002',
                'code' => 'HR-001',
                'nickname' => 'فاطمة',
                'first_name' => 'فاطمة',
                'second_name' => 'أحمد',
                'third_name' => 'محمود',
                'phone1' => '0599234567',
                'phone2' => '082456789',
                'email' => 'fatima.mahmoud@company.com',
                'birth_date' => Carbon::create(1988, 8, 22),
                'address' => 'غزة، فلسطين',
                'national_id' => '987654321',
                'id_number' => 'ID-002',
                'gender' => 'female',
                'wives_count' => 0,
                'children_count' => 1,
                'dependents_count' => '1',
                'car_number' => null,
                'is_driver' => false,
                'is_sales' => false,
                'hire_date' => Carbon::create(2022, 6, 1),
                'employee_code' => 'HR-001',
                'employee_identifier' => 'HR-ID-001',
                'job_address' => 'HR Department, 2nd Floor',
                'salary' => '4500.00',
                'billing_rate' => 65.00,
                'monthly_discount' => 0.00,
                'notes' => 'HR Manager with expertise in recruitment and employee relations',
                'department_index' => 1, // HR Department
            ],
            [
                'employee_number' => 'EMP-003',
                'code' => 'FIN-001',
                'nickname' => 'محمد',
                'first_name' => 'محمد',
                'second_name' => 'عبدالله',
                'third_name' => 'الأحمد',
                'phone1' => '0599345678',
                'phone2' => '082567890',
                'email' => 'mohammed.ahmed@company.com',
                'birth_date' => Carbon::create(1985, 12, 10),
                'address' => 'الخليل، فلسطين',
                'national_id' => '456789123',
                'id_number' => 'ID-003',
                'gender' => 'male',
                'wives_count' => 1,
                'children_count' => 3,
                'dependents_count' => '4',
                'car_number' => 'XYZ-456',
                'is_driver' => true,
                'is_sales' => false,
                'hire_date' => Carbon::create(2021, 3, 10),
                'employee_code' => 'FIN-001',
                'employee_identifier' => 'FIN-ID-001',
                'job_address' => 'Finance Department, 1st Floor',
                'salary' => '6000.00',
                'billing_rate' => 85.00,
                'monthly_discount' => 100.00,
                'notes' => 'Chief Financial Officer with CPA certification',
                'department_index' => 2, // Finance Department
            ],
            [
                'employee_number' => 'EMP-004',
                'code' => 'TECH-002',
                'nickname' => 'سارة',
                'first_name' => 'سارة',
                'second_name' => 'خالد',
                'third_name' => 'محمد',
                'phone1' => '0599456789',
                'phone2' => null,
                'email' => 'sara.khalid@company.com',
                'birth_date' => Carbon::create(1992, 3, 8),
                'address' => 'نابلس، فلسطين',
                'national_id' => '789123456',
                'id_number' => 'ID-004',
                'gender' => 'female',
                'wives_count' => 0,
                'children_count' => 0,
                'dependents_count' => '0',
                'car_number' => null,
                'is_driver' => false,
                'is_sales' => false,
                'hire_date' => Carbon::create(2023, 9, 1),
                'employee_code' => 'TECH-002',
                'employee_identifier' => 'TECH-ID-002',
                'job_address' => 'IT Department, 3rd Floor',
                'salary' => '4000.00',
                'billing_rate' => 60.00,
                'monthly_discount' => 0.00,
                'notes' => 'Junior Frontend Developer specializing in React',
                'department_index' => 0, // IT Department
            ],
            [
                'employee_number' => 'EMP-005',
                'code' => 'SALES-001',
                'nickname' => 'عمر',
                'first_name' => 'عمر',
                'second_name' => 'يوسف',
                'third_name' => 'الخالدي',
                'phone1' => '0599567890',
                'phone2' => '082678901',
                'email' => 'omar.khalidi@company.com',
                'birth_date' => Carbon::create(1987, 7, 25),
                'address' => 'بيت لحم، فلسطين',
                'national_id' => '321654987',
                'id_number' => 'ID-005',
                'gender' => 'male',
                'wives_count' => 1,
                'children_count' => 1,
                'dependents_count' => '2',
                'car_number' => 'DEF-789',
                'is_driver' => true,
                'is_sales' => true,
                'hire_date' => Carbon::create(2022, 11, 15),
                'employee_code' => 'SALES-001',
                'employee_identifier' => 'SALES-ID-001',
                'job_address' => 'Sales Department, Ground Floor',
                'salary' => '3800.00',
                'billing_rate' => 55.00,
                'monthly_discount' => 50.00,
                'notes' => 'Senior Sales Representative with excellent customer relations',
                'department_index' => 3, // Sales & Marketing Department
            ],
            [
                'employee_number' => 'EMP-006',
                'code' => 'TECH-003',
                'nickname' => 'يوسف',
                'first_name' => 'يوسف',
                'second_name' => 'أحمد',
                'third_name' => 'الزهراني',
                'phone1' => '0599678901',
                'phone2' => '082789012',
                'email' => 'youssef.zahrani@company.com',
                'birth_date' => Carbon::create(1989, 11, 3),
                'address' => 'جنين، فلسطين',
                'national_id' => '654987321',
                'id_number' => 'ID-006',
                'gender' => 'male',
                'wives_count' => 0,
                'children_count' => 0,
                'dependents_count' => '0',
                'car_number' => 'GHI-012',
                'is_driver' => true,
                'is_sales' => false,
                'hire_date' => Carbon::create(2023, 4, 20),
                'employee_code' => 'TECH-003',
                'employee_identifier' => 'TECH-ID-003',
                'job_address' => 'IT Department, 3rd Floor',
                'salary' => '4800.00',
                'billing_rate' => 70.00,
                'monthly_discount' => 0.00,
                'notes' => 'Backend Developer specializing in Laravel and PHP',
                'department_index' => 0, // IT Department
            ],
        ];

        // Create employees
        foreach ($employees as $empData) {
            $department = $createdDepartments[$empData['department_index']];
            unset($empData['department_index']);

            Employee::withoutGlobalScopes()->firstOrCreate(
                ['employee_number' => $empData['employee_number']],
                array_merge($empData, [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'department_id' => $department->id,
                    'job_title_id' => $jobTitles[0]->id ?? 1, // Use first job title or default
                    'manager_id' => $user->id,
                    'currency_id' => $currency->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ])
            );
        }

        $this->command->info('✅ Employees seeded successfully!');
    }
}
