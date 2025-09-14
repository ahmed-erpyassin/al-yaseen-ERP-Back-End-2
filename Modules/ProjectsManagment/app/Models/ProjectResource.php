<?php

namespace Modules\ProjectsManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;
use Modules\Inventory\Models\Supplier;

class ProjectResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'project_id',
        'supplier_id',
        'supplier_number',
        'supplier_name',
        'project_number',
        'project_name',
        'role',
        'allocation',
        'allocation_percentage',
        'allocation_value',
        'notes',
        'status',
        'resource_type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'allocation_value' => 'decimal:2',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByResourceType($query, $type)
    {
        return $query->where('resource_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function calculateAllocationPercentage()
    {
        if (!$this->project || !$this->project->project_value || !$this->allocation_value) {
            return 0;
        }

        return ($this->allocation_value / $this->project->project_value) * 100;
    }

    public function calculateAllocationValue()
    {
        if (!$this->project || !$this->project->project_value || !$this->allocation_percentage) {
            return 0;
        }

        return ($this->allocation_percentage / 100) * $this->project->project_value;
    }

    public function getResourceTypeOptions()
    {
        return [
            'supplier' => 'Supplier',
            'internal' => 'Internal',
            'contractor' => 'Contractor',
            'consultant' => 'Consultant'
        ];
    }

    public function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'completed' => 'Completed'
        ];
    }

    public function getResourceTypeLabelAttribute()
    {
        $types = $this->getResourceTypeOptions();
        return $types[$this->resource_type] ?? $this->resource_type;
    }

    public function getStatusLabelAttribute()
    {
        $statuses = $this->getStatusOptions();
        return $statuses[$this->status] ?? $this->status;
    }

    // Boot method for auto-calculations
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($resource) {
            // Auto-populate supplier information if supplier_id is provided
            if ($resource->supplier_id && $resource->supplier) {
                $resource->supplier_number = $resource->supplier->supplier_code;
                $resource->supplier_name = $resource->supplier->supplier_name_ar ?: $resource->supplier->supplier_name_en;
            }

            // Auto-populate project information if project_id is provided
            if ($resource->project_id && $resource->project) {
                $resource->project_number = $resource->project->project_number;
                $resource->project_name = $resource->project->name;
            }

            // Auto-calculate allocation percentage if allocation_value is provided
            if ($resource->allocation_value && !$resource->allocation_percentage) {
                $resource->allocation_percentage = $resource->calculateAllocationPercentage();
            }

            // Auto-calculate allocation value if allocation_percentage is provided
            if ($resource->allocation_percentage && !$resource->allocation_value) {
                $resource->allocation_value = $resource->calculateAllocationValue();
            }
        });
    }
}
