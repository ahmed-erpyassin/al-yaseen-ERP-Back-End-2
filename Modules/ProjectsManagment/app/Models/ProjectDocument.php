<?php

namespace Modules\ProjectsManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'project_id',
        'document_number',
        'project_number',
        'project_name',
        'title',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'description',
        'document_category',
        'status',
        'upload_date',
        'version',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'upload_date' => 'date',
    ];

    // Relationships
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('document_category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFileType($query, $fileType)
    {
        return $query->where('file_type', 'like', "%{$fileType}%");
    }

    public function scopeByUploadDate($query, $date)
    {
        return $query->whereDate('upload_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('upload_date', [$startDate, $endDate]);
    }

    // Helper methods
    public function generateDocumentNumber()
    {
        if (!$this->project_id) {
            return null;
        }

        $lastDocument = static::where('project_id', $this->project_id)
            ->orderBy('document_number', 'desc')
            ->first();

        return $lastDocument ? ($lastDocument->document_number + 1) : 1;
    }

    public function getFileSizeHumanAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute()
    {
        return $this->file_name ? pathinfo($this->file_name, PATHINFO_EXTENSION) : null;
    }

    public function getDocumentCategoryOptions()
    {
        return [
            'contract' => 'Contract',
            'specification' => 'Specification',
            'drawing' => 'Drawing',
            'report' => 'Report',
            'invoice' => 'Invoice',
            'correspondence' => 'Correspondence',
            'other' => 'Other'
        ];
    }

    public function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'archived' => 'Archived',
            'deleted' => 'Deleted'
        ];
    }

    public function getDocumentCategoryLabelAttribute()
    {
        $categories = $this->getDocumentCategoryOptions();
        return $categories[$this->document_category] ?? $this->document_category;
    }

    public function getStatusLabelAttribute()
    {
        $statuses = $this->getStatusOptions();
        return $statuses[$this->status] ?? $this->status;
    }

    // Boot method for auto-generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            // Auto-generate document number
            if (!$document->document_number) {
                $document->document_number = $document->generateDocumentNumber();
            }

            // Auto-populate project information if project_id is provided
            if ($document->project_id && $document->project) {
                $document->project_number = $document->project->project_number;
                $document->project_name = $document->project->name;
            }

            // Set upload date if not provided
            if (!$document->upload_date) {
                $document->upload_date = now()->toDateString();
            }

            // Set default status if not provided
            if (!$document->status) {
                $document->status = 'active';
            }

            // Set default version if not provided
            if (!$document->version) {
                $document->version = '1.0';
            }
        });

        static::updating(function ($document) {
            // Update project information if project_id changed
            if ($document->isDirty('project_id') && $document->project) {
                $document->project_number = $document->project->project_number;
                $document->project_name = $document->project->name;
            }
        });
    }
}
