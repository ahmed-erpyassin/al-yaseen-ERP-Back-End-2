<?php

namespace Modules\HumanResources\Transformers;

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
            'id'                  => $this->id,
            'company_id'          => $this->company_id,
            'user_id'             => $this->user_id,
            'branch_id'           => $this->branch_id,
            'fiscal_year_id'      => $this->fiscal_year_id,
            'department_id'       => $this->department_id,
            'job_title_id'        => $this->job_title_id,
            'manager_id'          => $this->manager_id,

            'employee_number'     => $this->employee_number,
            'code'                => $this->code,

            'nickname'            => $this->nickname,
            'first_name'          => $this->first_name,
            'second_name'         => $this->second_name,
            'third_name'          => $this->third_name,
            'phone1'              => $this->phone1,
            'phone2'              => $this->phone2,
            'email'               => $this->email,

            'birth_date'          => $this->birth_date,
            'address'             => $this->address,
            'national_id'         => $this->national_id,
            'id_number'           => $this->id_number,
            'gender'              => $this->gender,

            'wives_count'         => $this->wives_count,
            'children_count'      => $this->children_count,
            'dependents_count'    => $this->dependents_count,

            'car_number'          => $this->car_number,
            'is_driver'           => $this->is_driver,
            'is_sales'            => $this->is_sales,

            'hire_date'           => $this->hire_date,
            'employee_code'       => $this->employee_code,
            'employee_identifier' => $this->employee_identifier,
            'job_address'         => $this->job_address,

            'salary'              => $this->salary,
            'billing_rate'        => $this->billing_rate,
            'monthly_discount'    => $this->monthly_discount,

            'currency_id'         => $this->currency_id,
            'notes'               => $this->notes,

            'created_by'          => $this->created_by,
            'updated_by'          => $this->updated_by,
            'deleted_by'          => $this->deleted_by,
        ];
    }
}
