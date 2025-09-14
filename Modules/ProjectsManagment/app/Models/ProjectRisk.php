<?php

namespace Modules\ProjectsManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;
use Modules\HumanResources\Models\Employee;

class ProjectRisk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'project_id',
        'title',
        'description',
        'impact',
        'probability',
        'mitigation_plan',
        'status',
        'assigned_to',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
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

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByImpact($query, $impact)
    {
        return $query->where('impact', $impact);
    }

    public function scopeByProbability($query, $probability)
    {
        return $query->where('probability', $probability);
    }

    public function scopeAssignedTo($query, $employeeId)
    {
        return $query->where('assigned_to', $employeeId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('mitigation_plan', 'like', "%{$search}%")
              ->orWhereHas('project', function ($projectQuery) use ($search) {
                  $projectQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('project_number', 'like', "%{$search}%");
              })
              ->orWhereHas('assignedEmployee', function ($employeeQuery) use ($search) {
                  $employeeQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('second_name', 'like', "%{$search}%")
                               ->orWhere('third_name', 'like', "%{$search}%")
                               ->orWhere('employee_number', 'like', "%{$search}%");
              });
        });
    }

    // Helper Methods
    public function getImpactLevelAttribute()
    {
        $levels = [
            'low' => 1,
            'medium' => 2,
            'high' => 3
        ];
        return $levels[$this->impact] ?? 0;
    }

    public function getProbabilityLevelAttribute()
    {
        $levels = [
            'low' => 1,
            'medium' => 2,
            'high' => 3
        ];
        return $levels[$this->probability] ?? 0;
    }

    public function getRiskScoreAttribute()
    {
        return $this->impact_level * $this->probability_level;
    }

    public function getRiskLevelAttribute()
    {
        $score = $this->risk_score;
        if ($score <= 2) return 'Low';
        if ($score <= 6) return 'Medium';
        return 'High';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'open' => 'red',
            'mitigated' => 'yellow',
            'closed' => 'green'
        ];
        return $colors[$this->status] ?? 'gray';
    }

    // Static Methods
    public static function getImpactOptions()
    {
        return [
            ['value' => 'low', 'label' => 'Low'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'high', 'label' => 'High']
        ];
    }

    public static function getProbabilityOptions()
    {
        return [
            ['value' => 'low', 'label' => 'Low'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'high', 'label' => 'High']
        ];
    }

    public static function getStatusOptions()
    {
        return [
            ['value' => 'open', 'label' => 'Open'],
            ['value' => 'mitigated', 'label' => 'Mitigated'],
            ['value' => 'closed', 'label' => 'Closed']
        ];
    }
}
