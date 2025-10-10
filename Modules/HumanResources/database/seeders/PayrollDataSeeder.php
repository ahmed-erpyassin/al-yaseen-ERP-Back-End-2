<?php

namespace Modules\HumanResources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HumanResources\Models\PayrollData;
use Modules\HumanResources\Models\PayrollRecord;
use Modules\HumanResources\Models\Employee;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;
use Carbon\Carbon;

class PayrollDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💰 Starting Payroll Data seeding...');

        // Get required data
        $company = Company::first();
        $user = User::first();
        $branch = Branch::first();
        $fiscalYear = FiscalYear::first();

        if (!$company || !$user || !$branch || !$fiscalYear) {
            $this->command->error('❌ Required data not found. Please seed Companies, Users, Branches, and FiscalYears first.');
            return;
        }

        $this->seedPayrollData($company, $user, $branch, $fiscalYear);

        $this->command->info('✅ Payroll Data seeding completed successfully!');
    }

    private function seedPayrollData($company, $user, $branch, $fiscalYear)
    {
        $this->command->info('💰 Seeding Payroll Data...');

        // Clear existing payroll data for this company
        PayrollData::where('company_id', $company->id)->delete();

        // Get all payroll records
        $payrollRecords = PayrollRecord::where('company_id', $company->id)->get();
        
        if ($payrollRecords->isEmpty()) {
            $this->command->warn('⚠️  No payroll records found. Please seed payroll records first.');
            return;
        }

        // Get all employees
        $employees = Employee::where('company_id', $company->id)->get();
        
        if ($employees->isEmpty()) {
            $this->command->warn('⚠️  No employees found. Please seed employees first.');
            return;
        }

        $this->command->info("📊 Creating payroll data for {$employees->count()} employees across {$payrollRecords->count()} payroll records...");

        $createdCount = 0;

        foreach ($payrollRecords as $payrollRecord) {
            $this->command->info("Processing payroll record: {$payrollRecord->payroll_number}");
            
            foreach ($employees as $employee) {
                // Calculate employee duration
                $duration = '';
                if ($employee->hire_date) {
                    $years = $employee->hire_date->diffInYears(now());
                    $months = $employee->hire_date->diffInMonths(now()) % 12;
                    $duration = "{$years} years, {$months} months";
                } else {
                    $duration = "0 years, 0 months";
                }

                // Determine marital status based on wives count
                $maritalStatus = ($employee->wives_count && $employee->wives_count > 0) ? 'married' : 'single';

                // Calculate salary components with realistic values
                $basicSalary = $employee->salary ?? rand(3000, 8000);
                $allowances = rand(200, 1000); // Transportation, housing, etc.
                $overtimeHours = rand(0, 20); // 0-20 hours overtime
                $overtimeRate = rand(15, 30); // Hourly rate for overtime
                $overtimeAmount = $overtimeHours * $overtimeRate;
                $deductions = rand(100, 500); // Insurance, loans, etc.
                
                // Calculate income tax (10% of basic salary)
                $incomeTax = round($basicSalary * 0.10, 2);
                
                // Calculate salary for payment
                $totalSalary = $basicSalary + $allowances + $overtimeAmount;
                $totalDeductions = $incomeTax + $deductions;
                $salaryForPayment = $totalSalary - $totalDeductions;
                
                // Calculate paid in cash (random percentage of salary for payment)
                $paidInCash = round($salaryForPayment * (rand(20, 80) / 100), 2);

                // Get job title
                $jobTitle = 'General Employee';
                if ($employee->jobTitle) {
                    $jobTitle = $employee->jobTitle->name;
                } elseif ($employee->job_title) {
                    $jobTitle = $employee->job_title;
                }

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
                    'job_title' => $jobTitle,
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

                $createdCount++;
            }
        }

        $this->command->info("✅ Created {$createdCount} payroll data records successfully!");
        
        // Update payroll record totals
        foreach ($payrollRecords as $payrollRecord) {
            $payrollRecord->calculateTotals();
        }
        
        $this->command->info("✅ Updated payroll record totals!");
    }
}
