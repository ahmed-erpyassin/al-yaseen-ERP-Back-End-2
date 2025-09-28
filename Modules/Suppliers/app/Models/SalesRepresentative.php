<?php

namespace Modules\Suppliers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Company;

class SalesRepresentative extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'department_id',
        'representative_number',
        'employee_number',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'mobile',
        'emergency_contact',
        'address',
        'country_id',
        'region_id',
        'city_id',
        'hire_date',
        'termination_date',
        'employment_type',
        'base_salary',
        'commission_rate',
        'sales_target',
        'current_sales',
        'total_commission',
        'customers_count',
        'suppliers_count',
        'territory',
        'specialization',
        'notes',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'base_salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'sales_target' => 'decimal:2',
        'current_sales' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'customers_count' => 'integer',
        'suppliers_count' => 'integer',
        'territory' => 'array',
        'specialization' => 'array',
        'employment_type' => 'string',
        'status' => 'string',
    ];

    // Constants for employment types
    const EMPLOYMENT_TYPE_OPTIONS = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract' => 'Contract',
        'freelance' => 'Freelance',
    ];

    // Constants for status
    const STATUS_OPTIONS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'on_leave' => 'On Leave',
        'terminated' => 'Terminated',
    ];

    /**
     * Get the user that owns the sales representative.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the sales representative.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the sales representative.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the sales representative.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate the next sequential representative number.
     */
    public static function generateRepresentativeNumber(): string
    {
        $lastRep = self::orderBy('id', 'desc')->first();

        if (!$lastRep) {
            return 'REP-0001';
        }

        // Extract number from last representative number (assuming format REP-XXXX)
        $lastNumber = (int) substr($lastRep->representative_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'REP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Scope to get active representatives only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get representatives for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
