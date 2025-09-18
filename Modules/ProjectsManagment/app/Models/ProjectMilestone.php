<?php

namespace Modules\ProjectsManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class ProjectMilestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'project_id',
        'milestone_number',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'progress',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'decimal:2',
        'milestone_number' => 'integer',
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

    // Helper methods
    public function generateMilestoneNumber()
    {
        $lastMilestone = static::where('project_id', $this->project_id)
            ->orderBy('milestone_number', 'desc')
            ->first();

        return $lastMilestone ? ($lastMilestone->milestone_number + 1) : 1;
    }

    public function getStatusOptions()
    {
        return [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed'
        ];
    }

    public function getStatusLabelAttribute()
    {
        $statuses = $this->getStatusOptions();
        return $statuses[$this->status] ?? $this->status;
    }

    // Boot method for auto-generating milestone number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($milestone) {
            if (empty($milestone->milestone_number)) {
                $milestone->milestone_number = $milestone->generateMilestoneNumber();
            }
        });
    }
}
