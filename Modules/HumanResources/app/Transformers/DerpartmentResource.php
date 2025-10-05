<?php

namespace Modules\HumanResources\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DerpartmentResource extends JsonResource
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
            'name'                => $this->name,
            'number'              => $this->number,
            'manager_id'          => $this->manager_id,
            'address'             => $this->address,
            'work_phone'          => $this->work_phone,
            'home_phone'          => $this->home_phone,
            'fax'                 => $this->fax,
            'statement'           => $this->statement,
            'statement_en'        => $this->statement_en,
            'parent_id'           => $this->parent_id,
            'funder_id'           => $this->funder_id,
            'project_status'      => $this->project_status,
            'status'              => $this->status,
            'proposed_start_date' => $this->proposed_start_date,
            'proposed_end_date'   => $this->proposed_end_date,
            'actual_start_date'   => $this->actual_start_date,
            'actual_end_date'     => $this->actual_end_date,
            'budget_id'           => $this->budget_id,
            'notes'               => $this->notes,
            'created_by'          => $this->created_by,
            'updated_by'          => $this->updated_by,
            'deleted_by'          => $this->deleted_by,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,

            // Relationships
            'company'             => $this->whenLoaded('company'),
            'user'                => $this->whenLoaded('user'),
            'branch'              => $this->whenLoaded('branch'),
            'fiscal_year'         => $this->whenLoaded('fiscalYear'),
            'manager'             => $this->whenLoaded('manager'),
            'parent_department'   => $this->whenLoaded('parent'),
            'budget'              => $this->whenLoaded('budget'),
            'funder'              => $this->whenLoaded('funder'),
            'creator'             => $this->whenLoaded('creator'),
            'updater'             => $this->whenLoaded('updater'),
            'deleter'             => $this->whenLoaded('deleter'),

            // Reverse relationships
            'employees'           => $this->whenLoaded('employees'),
            'job_titles'          => $this->whenLoaded('jobTitles'),
            'child_departments'   => $this->whenLoaded('childDepartments'),
        ];
    }
}
