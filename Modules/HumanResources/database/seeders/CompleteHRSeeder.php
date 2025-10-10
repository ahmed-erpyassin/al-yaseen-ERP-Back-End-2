<?php

namespace Modules\HumanResources\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\Models\Department;
use Modules\HumanResources\Models\JobTitle;
use Modules\HumanResources\Models\EmployeeContract;
use Modules\HumanResources\Models\EmployeeDocument;
use Modules\HumanResources\Models\EmployeeEvaluation;
use Modules\HumanResources\Models\EmployeeLoan;
use Modules\HumanResources\Models\EmployeePromotion;
use Modules\HumanResources\Models\Shift;
use Modules\HumanResources\Models\AttendanceRecord;
use Modules\HumanResources\Models\LeaveRequest;
use Modules\HumanResources\Models\PayrollRecord;
use Modules\HumanResources\Models\PayrollData;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;
use Carbon\Carbon;

class CompleteHRSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete HR seeding...');

        // Get required data
        $company = Company::first();
        $user = User::first();
        $branch = Branch::first();
        $currency = Currency::first();
        $fiscalYear = FiscalYear::first();

        if (!$company || !$user || !$branch || !$currency || !$fiscalYear) {
            $this->command->error('❌ Required data not found. Please seed Companies, Users, Branches, Currencies, and FiscalYears first.');
            return;
        }

        // 1. First run the existing employee seeder to ensure we have employees
        $this->call(EmployeeSeeder::class);
        
        // 2. Seed Shifts
        $this->seedShifts($company, $user, $branch, $fiscalYear);
        
        // 3. Seed Employee Contracts
        $this->seedEmployeeContracts($company, $user, $branch, $fiscalYear, $currency);
        
        // 4. Seed Employee Documents
        $this->seedEmployeeDocuments($company, $user, $branch, $fiscalYear);
        
        // 5. Seed Employee Evaluations
        $this->seedEmployeeEvaluations($company, $user, $branch, $fiscalYear);
        
        // 6. Seed Employee Loans
        $this->seedEmployeeLoans($company, $user, $branch, $fiscalYear);
        
        // 7. Seed Employee Promotions
        $this->seedEmployeePromotions($company, $user, $branch, $fiscalYear);
        
        // 8. Seed Attendance Records
        $this->seedAttendanceRecords($company, $user, $branch, $fiscalYear);
        
        // 9. Seed Leave Requests
        $this->seedLeaveRequests($company, $user, $branch, $fiscalYear);
        
        // 10. Seed Payroll Records
        $this->seedPayrollRecords($company, $user, $branch, $fiscalYear);

        // 11. Seed Payroll Data
        $this->seedPayrollData($company, $user, $branch, $fiscalYear);

        $this->command->info('✅ Complete HR seeding completed successfully!');
    }

    private function seedShifts($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('⏰ Seeding Shifts...');

        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'break_duration' => 60, // 1 hour break
                'description' => 'Regular morning shift',
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'break_duration' => 60,
                'description' => 'Evening shift',
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'break_duration' => 60,
                'description' => 'Night shift',
            ],
        ];

        foreach ($shifts as $shiftData) {
            Shift::firstOrCreate([
                'company_id' => $company->id,
                'name' => $shiftData['name']
            ], array_merge($shiftData, [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));
        }
    }

    private function seedEmployeeContracts($company, $user, $branch, $fiscalYear, $currency)
    {
        $this->command->info('📄 Seeding Employee Contracts...');

        $employees = Employee::where('company_id', $company->id)->get();

        foreach ($employees as $employee) {
            EmployeeContract::firstOrCreate([
                'company_id' => $company->id,
                'employee_id' => $employee->id
            ], [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'currency_id' => $currency->id,
                'contract_number' => 'CON-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
                'contract_type' => 'permanent',
                'start_date' => $employee->hire_date,
                'end_date' => Carbon::now()->addYear(),
                'basic_salary' => $employee->salary,
                'salary' => $employee->salary,
                'allowances' => '0',
                'deductions' => '0',
                'working_hours' => 8,
                'vacation_days' => 21,
                'status' => 'active',
                'terms_conditions' => 'Standard employment contract terms and conditions.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }

    private function seedEmployeeDocuments($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('📋 Seeding Employee Documents...');

        $employees = Employee::where('company_id', $company->id)->get();
        $documentTypes = ['CV', 'ID Copy', 'Certificate', 'Contract', 'Medical Report'];

        foreach ($employees as $employee) {
            foreach ($documentTypes as $docType) {
                EmployeeDocument::firstOrCreate([
                    'company_id' => $company->id,
                    'employee_id' => $employee->id,
                    'document_type' => $docType
                ], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'document_name' => $employee->first_name . '_' . $docType . '.pdf',
                    'file_path' => 'documents/employees/' . $employee->id . '/' . $docType . '.pdf',
                    'file_size' => rand(100, 1000) . ' KB',
                    'upload_date' => Carbon::now()->subDays(rand(1, 30)),
                    'expiry_date' => in_array($docType, ['ID Copy', 'Medical Report']) ? 
                        Carbon::now()->addYear() : null,
                    'status' => 'active',
                    'notes' => 'Document uploaded during employee onboarding.',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }

    private function seedEmployeeEvaluations($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('⭐ Seeding Employee Evaluations...');

        $employees = Employee::where('company_id', $company->id)->get();

        foreach ($employees as $employee) {
            // Create 2 evaluations per employee (quarterly)
            for ($i = 1; $i <= 2; $i++) {
                $overallScore = rand(75, 90);
                EmployeeEvaluation::firstOrCreate([
                    'company_id' => $company->id,
                    'employee_id' => $employee->id,
                    'evaluation_period' => 'Q' . $i . ' 2024'
                ], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'evaluator_id' => $user->id,
                    'evaluation_date' => Carbon::now()->subMonths(6 - ($i * 3)),
                    'score' => $overallScore,
                    'performance_score' => rand(70, 95),
                    'goals_achievement' => rand(75, 90),
                    'communication_skills' => rand(80, 95),
                    'teamwork' => rand(75, 90),
                    'leadership' => rand(70, 85),
                    'overall_rating' => $overallScore,
                    'strengths' => 'Good technical skills, reliable, punctual',
                    'areas_for_improvement' => 'Communication skills, time management',
                    'goals_next_period' => 'Improve project delivery time, enhance team collaboration',
                    'evaluator_comments' => 'Overall good performance with room for improvement.',
                    'employee_comments' => 'Thank you for the feedback. Will work on the mentioned areas.',
                    'status' => 'completed',
                    'notes' => 'Quarterly performance evaluation completed.',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }

    private function seedEmployeeLoans($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('💰 Seeding Employee Loans...');

        $employees = Employee::where('company_id', $company->id)->take(3)->get(); // Only 3 employees have loans

        foreach ($employees as $employee) {
            $loanAmount = rand(1000, 5000);
            $monthlyDeduction = $loanAmount / 12; // 12 months loan

            $totalPaid = $monthlyDeduction * rand(1, 6);
            EmployeeLoan::firstOrCreate([
                'company_id' => $company->id,
                'employee_id' => $employee->id
            ], [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'loan_number' => 'LOAN-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
                'loan_type' => 'personal',
                'loan_amount' => $loanAmount,
                'interest_rate' => 5.0,
                'loan_date' => Carbon::now()->subMonths(rand(1, 6)),
                'repayment_period' => 12,
                'monthly_deduction' => $monthlyDeduction,
                'total_paid' => $totalPaid,
                'remaining_balance' => $loanAmount - $totalPaid,
                'status' => 'active',
                'purpose' => 'Personal financial needs',
                'guarantor_name' => 'John Doe',
                'guarantor_phone' => '+970591234999',
                'notes' => 'Approved loan for employee',
                'old_amount' => $loanAmount,
                'old_remaining' => $loanAmount - $totalPaid,
                'old_installments' => 12,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }

    private function seedEmployeePromotions($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('🚀 Seeding Employee Promotions...');

        $employees = Employee::where('company_id', $company->id)->take(2)->get(); // Only 2 employees got promoted
        $jobTitles = JobTitle::where('company_id', $company->id)->get();

        foreach ($employees as $employee) {
            // Get a different job title for promotion
            $newJobTitle = $jobTitles->where('id', '!=', $employee->job_title_id)->first();

            if ($newJobTitle) {
                $oldSalary = $employee->salary;
                $newSalary = $oldSalary * 1.15; // 15% salary increase

                EmployeePromotion::firstOrCreate([
                    'company_id' => $company->id,
                    'employee_id' => $employee->id
                ], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'promotion_date' => Carbon::now()->subMonths(rand(1, 3)),
                    'old_job_title_id' => $employee->job_title_id,
                    'new_job_title_id' => $newJobTitle->id,
                    'old_salary' => $oldSalary,
                    'new_salary' => $newSalary,
                    'promotion_reason' => 'Excellent performance and dedication',
                    'effective_date' => Carbon::now()->subMonths(rand(1, 3)),
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'notes' => 'Well-deserved promotion based on performance evaluation',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);

                // Update employee's current job title and salary
                $employee->update([
                    'job_title_id' => $newJobTitle->id,
                    'salary' => $newSalary,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }

    private function seedAttendanceRecords($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('📅 Seeding Attendance Records...');

        $employees = Employee::where('company_id', $company->id)->get();
        $shifts = Shift::where('company_id', $company->id)->get();
        $morningShift = $shifts->first();

        foreach ($employees as $employee) {
            // Create attendance records for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);

                // Skip weekends (Friday and Saturday in Palestine)
                if ($date->isFriday() || $date->isSaturday()) {
                    continue;
                }

                $checkIn = $date->copy()->setTime(8, rand(0, 30), 0); // 8:00-8:30 AM
                $checkOut = $date->copy()->setTime(16, rand(0, 30), 0); // 4:00-4:30 PM
                $workingHours = $checkOut->diffInHours($checkIn) - 1; // Minus 1 hour break

                AttendanceRecord::firstOrCreate([
                    'company_id' => $company->id,
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d')
                ], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'shift_id' => $morningShift->id ?? null,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'break_start' => $checkIn->copy()->addHours(4),
                    'break_end' => $checkIn->copy()->addHours(5),
                    'working_hours' => $workingHours,
                    'overtime_hours' => rand(0, 2),
                    'old_worked_hours' => $workingHours,
                    'old_overtime_hours' => rand(0, 2),
                    'status' => 'present',
                    'notes' => 'Regular attendance',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }

    private function seedLeaveRequests($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('🏖️ Seeding Leave Requests...');

        // Create leave types first if they don't exist
        $leaveTypeData = [
            ['name' => 'Annual Leave', 'days_per_year' => 21],
            ['name' => 'Sick Leave', 'days_per_year' => 10],
            ['name' => 'Maternity Leave', 'days_per_year' => 90],
            ['name' => 'Emergency Leave', 'days_per_year' => 5],
        ];

        $leaveTypeIds = [];
        foreach ($leaveTypeData as $data) {
            $existingType = DB::table('leave_types')->where('name', $data['name'])->first();
            if (!$existingType) {
                $leaveTypeId = DB::table('leave_types')->insertGetId([
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'name' => $data['name'],
                    'days_per_year' => $data['days_per_year'],
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $leaveTypeIds[] = $leaveTypeId;
            } else {
                $leaveTypeIds[] = $existingType->id;
            }
        }

        $employees = Employee::where('company_id', $company->id)->get();
        $leaveTypes = ['annual', 'sick', 'emergency', 'maternity'];

        foreach ($employees as $employee) {
            // Create 2-3 leave requests per employee
            for ($i = 0; $i < rand(2, 3); $i++) {
                $startDate = Carbon::now()->subDays(rand(10, 60));
                $daysToAdd = rand(1, 5);
                $endDate = $startDate->copy()->addDays($daysToAdd);
                $daysCount = $daysToAdd + 1;

                LeaveRequest::firstOrCreate([
                    'company_id' => $company->id,
                    'employee_id' => $employee->id,
                    'start_date' => $startDate->format('Y-m-d')
                ], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'leave_type_id' => $leaveTypeIds[array_rand($leaveTypeIds)],
                    'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                    'end_date' => $endDate,
                    'days_requested' => $daysCount,
                    'days_count' => $daysCount,
                    'previous_balance' => 21,
                    'deducted' => $daysCount,
                    'remaining_balance' => 21 - $daysCount,
                    'reason' => 'Personal leave request',
                    'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                    'approved_by' => rand(0, 1) ? $user->id : null,
                    'approved_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,
                    'notes' => 'Leave request submitted by employee',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }

    private function seedPayrollRecords($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('💵 Seeding Payroll Records...');

        // Create payroll records for the last 3 months
        for ($i = 1; $i <= 3; $i++) {
            $payrollDate = Carbon::now()->subMonths($i)->endOfMonth();
            $payrollNumber = 'PAY-' . $payrollDate->format('Y-m') . '-' . str_pad($company->id, 3, '0', STR_PAD_LEFT);

            PayrollRecord::firstOrCreate([
                'company_id' => $company->id,
                'payroll_number' => $payrollNumber
            ], [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'fiscal_year_id' => $fiscalYear->id,
                'date' => $payrollDate->format('Y-m-d'),
                'currency_id' => 1, // Default currency
                'currency_rate' => 1.0000,
                'account_number' => 'ACC-' . rand(1000, 9999),
                'account_name' => 'Payroll Account',
                'payment_account' => 'Bank Transfer',
                'salaries_wages_period' => 'Salaries and wages for ' . $payrollDate->format('F Y'),
                'total_salaries' => rand(50000, 100000),
                'total_income_tax_deductions' => rand(5000, 10000),
                'total_payable_amount' => rand(45000, 90000),
                'total_salaries_paid_cash' => rand(10000, 20000),
                'status' => 'paid',
                'notes' => 'Monthly payroll processed',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }

    private function seedPayrollData($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('💰 Seeding Payroll Data...');

        // Get all payroll records
        $payrollRecords = PayrollRecord::where('company_id', $company->id)->get();

        if ($payrollRecords->isEmpty()) {
            $this->command->warn('⚠️  No payroll records found. Skipping payroll data seeding.');
            return;
        }

        // Get all employees
        $employees = Employee::where('company_id', $company->id)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('⚠️  No employees found. Skipping payroll data seeding.');
            return;
        }

        $this->command->info("📊 Creating payroll data for {$employees->count()} employees across {$payrollRecords->count()} payroll records...");

        foreach ($payrollRecords as $payrollRecord) {
            foreach ($employees as $employee) {
                // Check if payroll data already exists for this employee and payroll record
                $existingPayrollData = PayrollData::where('payroll_record_id', $payrollRecord->id)
                    ->where('employee_id', $employee->id)
                    ->first();

                if ($existingPayrollData) {
                    continue; // Skip if already exists
                }

                // Calculate employee duration
                $duration = '';
                if ($employee->hire_date) {
                    $years = $employee->hire_date->diffInYears(now());
                    $months = $employee->hire_date->diffInMonths(now()) % 12;
                    $duration = "{$years} years, {$months} months";
                }

                // Determine marital status
                $maritalStatus = $employee->wives_count > 0 ? 'married' : 'single';

                // Calculate salary components
                $basicSalary = $employee->salary ?? rand(3000, 8000);
                $allowances = rand(200, 1000);
                $overtimeHours = rand(0, 20);
                $overtimeRate = rand(15, 30);
                $overtimeAmount = $overtimeHours * $overtimeRate;
                $deductions = rand(100, 500);

                // Calculate income tax (10% of basic salary)
                $incomeTax = $basicSalary * 0.10;

                // Calculate salary for payment
                $totalSalary = $basicSalary + $allowances + $overtimeAmount;
                $totalDeductions = $incomeTax + $deductions;
                $salaryForPayment = $totalSalary - $totalDeductions;

                // Calculate paid in cash (random percentage of salary for payment)
                $paidInCash = $salaryForPayment * (rand(20, 80) / 100);

                PayrollData::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'payroll_record_id' => $payrollRecord->id,
                    'employee_id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'employee_name' => $employee->full_name,
                    'national_id' => $employee->national_id,
                    'marital_status' => $maritalStatus,
                    'job_title' => $employee->jobTitle ? $employee->jobTitle->name : 'General Employee',
                    'duration' => $duration,
                    'basic_salary' => $basicSalary,
                    'income_tax' => $incomeTax,
                    'salary_for_payment' => $salaryForPayment,
                    'paid_in_cash' => $paidInCash,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'overtime_hours' => $overtimeHours,
                    'overtime_rate' => $overtimeRate,
                    'overtime_amount' => $overtimeAmount,
                    'status' => 'active',
                    'notes' => 'Payroll data for ' . $payrollRecord->salaries_wages_period,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }

        $totalPayrollData = PayrollData::where('company_id', $company->id)->count();
        $this->command->info("✅ Created {$totalPayrollData} payroll data records successfully!");
    }
}
