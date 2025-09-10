<?php

namespace Modules\ProjectsManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Country;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\FinancialAccounts\Models\CostCenter;
use Modules\Users\Models\User;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Foreign Keys
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'cost_center_id',
        'manager_id',
        'customer_id',
        'currency_id',
        'country_id',

        // Project Basic Information
        'code',
        'project_number',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'budget',
        'project_value',
        'actual_cost',
        'progress',

        // Customer Information (auto-populated)
        'customer_name',
        'customer_email',
        'customer_phone',
        'licensed_operator',

        // Currency and Pricing
        'currency_price',
        'include_vat',

        // Project Manager Information
        'project_manager_name',

        // Additional Information
        'notes',
        'project_date',
        'project_time',

        // System fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'project_date' => 'datetime',
        'project_time' => 'datetime:H:i',
        'budget' => 'decimal:2',
        'project_value' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'currency_price' => 'decimal:2',
        'progress' => 'decimal:2',
        'include_vat' => 'boolean',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // System relationships
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

    // Project Management relationships
    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function resources()
    {
        return $this->hasMany(ProjectResource::class);
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function financials()
    {
        return $this->hasMany(ProjectFinancial::class);
    }

    public function risks()
    {
        return $this->hasMany(ProjectRisk::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Advanced Search Scopes
    public function scopeSearch($query, $filters = [])
    {
        // Project Number search
        if (!empty($filters['project_number'])) {
            $query->where('project_number', 'like', '%' . $filters['project_number'] . '%');
        }

        // Project Name search
        if (!empty($filters['project_name'])) {
            $query->where('name', 'like', '%' . $filters['project_name'] . '%');
        }

        // Customer Name search
        if (!empty($filters['customer_name'])) {
            $query->where('customer_name', 'like', '%' . $filters['customer_name'] . '%');
        }

        // Status search
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Project Manager Name search
        if (!empty($filters['project_manager_name'])) {
            $query->where('project_manager_name', 'like', '%' . $filters['project_manager_name'] . '%');
        }

        // Exact date search
        if (!empty($filters['exact_date'])) {
            $query->whereDate('project_date', $filters['exact_date']);
        }

        // Date range search (from/to)
        if (!empty($filters['date_from'])) {
            $query->whereDate('project_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('project_date', '<=', $filters['date_to']);
        }

        // Start date range search
        if (!empty($filters['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filters['start_date_from']);
        }

        if (!empty($filters['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filters['start_date_to']);
        }

        // End date range search
        if (!empty($filters['end_date_from'])) {
            $query->whereDate('end_date', '>=', $filters['end_date_from']);
        }

        if (!empty($filters['end_date_to'])) {
            $query->whereDate('end_date', '<=', $filters['end_date_to']);
        }

        // General text search across multiple fields
        if (!empty($filters['general_search'])) {
            $searchTerm = $filters['general_search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('project_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('customer_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('project_manager_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('notes', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query;
    }

    // Sorting scope
    public function scopeSortBy($query, $field = 'created_at', $direction = 'desc')
    {
        $allowedFields = [
            'id', 'code', 'project_number', 'name', 'customer_name', 'project_manager_name',
            'status', 'project_value', 'currency_price', 'start_date', 'end_date',
            'project_date', 'created_at', 'updated_at'
        ];

        if (in_array($field, $allowedFields)) {
            return $query->orderBy($field, $direction);
        }

        return $query->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function generateProjectCode()
    {
        $company = $this->company;
        $year = date('Y');
        $lastProject = static::where('company_id', $this->company_id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastProject ? (intval(substr($lastProject->code, -4)) + 1) : 1;

        return 'PRJ-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function calculateVATAmount()
    {
        if (!$this->include_vat || !$this->currency_price) {
            return 0;
        }

        $vatRate = $this->company->vat_rate ?? 0;
        return $this->currency_price * ($vatRate / 100);
    }

    public function getTotalPriceWithVAT()
    {
        return $this->currency_price + $this->calculateVATAmount();
    }

    // Boot method for auto-generating code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->code)) {
                $project->code = $project->generateProjectCode();
            }

            if (empty($project->project_date)) {
                $project->project_date = now();
            }

            if (empty($project->project_time)) {
                $project->project_time = now()->format('H:i');
            }
        });
    }
}
