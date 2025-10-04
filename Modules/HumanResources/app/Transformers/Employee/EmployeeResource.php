<?php

namespace Modules\HumanResources\Transformers\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // Basic Information
            'id' => $this->id,
            'employee_number' => $this->employee_number,
            'code' => $this->code,
            'employee_type' => $this->employee_type,
            'full_name' => $this->full_name,
            
            // Company & Organization
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'title' => $this->company->title,
                ];
            }),
            
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
            
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),
            
            'job_title_id' => $this->job_title_id,
            'job_title' => $this->whenLoaded('jobTitle', function () {
                return [
                    'id' => $this->jobTitle->id,
                    'title' => $this->jobTitle->title,
                ];
            }),
            
            'category' => $this->category,
            'fiscal_year_id' => $this->fiscal_year_id,
            
            // Manager Information
            'manager_id' => $this->manager_id,
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'employee_number' => $this->manager->employee_number,
                    'full_name' => $this->manager->full_name,
                ];
            }),
            
            // Personal Information
            'nickname' => $this->nickname,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'second_name' => $this->second_name,
            'third_name' => $this->third_name,
            
            // Contact Information
            'phone1' => $this->phone1,
            'phone2' => $this->phone2,
            'email' => $this->email,
            'address' => $this->address,
            
            // Personal Details
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'national_id' => $this->national_id,
            'id_number' => $this->id_number,
            'gender' => $this->gender,
            
            // Family Information
            'wives_count' => (int) $this->wives_count,
            'children_count' => (int) $this->children_count,
            'dependents_count' => $this->dependents_count,
            'students_count' => (int) $this->students_count,
            
            // Employment Type
            'is_driver' => (bool) $this->is_driver,
            'is_sales' => (bool) $this->is_sales,
            'car_number' => $this->car_number,
            
            // Job Information
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'employee_code' => $this->employee_code,
            'employee_identifier' => $this->employee_identifier,
            'job_address' => $this->job_address,
            
            // Financial Information
            'salary' => (float) $this->salary,
            'billing_rate' => (float) $this->billing_rate,
            'monthly_discount' => (float) $this->monthly_discount,
            'balance' => (float) $this->balance,
            
            // Currency Information
            'currency_id' => $this->currency_id,
            'currency_rate' => (float) $this->currency_rate,
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'name' => $this->currency->name,
                    'symbol' => $this->currency->symbol,
                ];
            }),
            
            // User Association
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'second_name' => $this->user->second_name,
                    'email' => $this->user->email,
                ];
            }),
            
            // Additional Information
            'notes' => $this->notes,
            
            // Audit Information
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'first_name' => $this->creator->first_name,
                    'second_name' => $this->creator->second_name,
                    'email' => $this->creator->email,
                ];
            }),
            
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'first_name' => $this->updater->first_name,
                    'second_name' => $this->updater->second_name,
                    'email' => $this->updater->email,
                ];
            }),
            
            // Subordinates count
            'subordinates_count' => $this->whenLoaded('subordinates', function () {
                return $this->subordinates->count();
            }),
            
            // Formatted values for display
            'formatted' => [
                'birth_date' => $this->birth_date?->format('d/m/Y'),
                'hire_date' => $this->hire_date?->format('d/m/Y'),
                'salary' => number_format($this->salary, 2),
                'balance' => number_format($this->balance, 2),
                'billing_rate' => number_format($this->billing_rate, 2),
                'monthly_discount' => number_format($this->monthly_discount, 2),
                'currency_rate' => number_format($this->currency_rate, 4),
                'created_at' => $this->created_at?->format('d/m/Y H:i'),
                'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
                'gender_label' => ucfirst($this->gender),
                'employee_type_label' => $this->getEmployeeTypeLabel(),
                'full_address' => $this->getFullAddress(),
                'employment_duration' => $this->getEmploymentDuration(),
                'age' => $this->getAge(),
            ],

            // Additional computed fields
            'computed' => [
                'total_family_members' => $this->getTotalFamilyMembers(),
                'net_salary' => $this->getNetSalary(),
                'is_manager' => $this->subordinates_count > 0,
                'employment_status' => $this->getEmploymentStatus(),
                'contact_info' => $this->getContactInfo(),
            ],
        ];
    }
    
    /**
     * Get employee type label
     */
    private function getEmployeeTypeLabel(): string
    {
        return match ($this->employee_type) {
            'driver' => 'Driver',
            'sales' => 'Sales Representative',
            'driver_sales' => 'Driver & Sales Representative',
            default => 'Employee',
        };
    }

    /**
     * Get full address
     */
    private function getFullAddress(): string
    {
        $addresses = array_filter([
            $this->address,
            $this->job_address
        ]);

        return implode(' | ', $addresses);
    }

    /**
     * Get employment duration
     */
    private function getEmploymentDuration(): string
    {
        if (!$this->hire_date) {
            return 'N/A';
        }

        $hireDate = $this->hire_date;
        $now = now();

        $years = $hireDate->diffInYears($now);
        $months = $hireDate->diffInMonths($now) % 12;
        $days = $hireDate->diffInDays($now) % 30;

        $duration = [];
        if ($years > 0) $duration[] = $years . ' year' . ($years > 1 ? 's' : '');
        if ($months > 0) $duration[] = $months . ' month' . ($months > 1 ? 's' : '');
        if (empty($duration) && $days > 0) $duration[] = $days . ' day' . ($days > 1 ? 's' : '');

        return empty($duration) ? 'Less than a day' : implode(', ', $duration);
    }

    /**
     * Get age
     */
    private function getAge(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /**
     * Get total family members
     */
    private function getTotalFamilyMembers(): int
    {
        return (int)$this->wives_count + (int)$this->children_count + (int)$this->students_count;
    }

    /**
     * Get net salary (salary - monthly discount)
     */
    private function getNetSalary(): float
    {
        return (float)$this->salary - (float)$this->monthly_discount;
    }

    /**
     * Get employment status
     */
    private function getEmploymentStatus(): string
    {
        if ($this->deleted_at) {
            return 'Terminated';
        }

        if ($this->balance < 0) {
            return 'In Debt';
        }

        if ($this->balance > 0) {
            return 'Credit Balance';
        }

        return 'Active';
    }

    /**
     * Get contact information
     */
    private function getContactInfo(): array
    {
        $contacts = [];

        if ($this->phone1) {
            $contacts[] = ['type' => 'Primary Phone', 'value' => $this->phone1];
        }

        if ($this->phone2) {
            $contacts[] = ['type' => 'Secondary Phone', 'value' => $this->phone2];
        }

        if ($this->email) {
            $contacts[] = ['type' => 'Email', 'value' => $this->email];
        }

        return $contacts;
    }
}
