<?php

namespace Modules\Suppliers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        // User and Company Relations
        'user_id',
        'company_id',
        'branch_id',
        'currency_id',
        'employee_id',
        'department_id',
        'project_id',
        'donor_id',
        'sales_representative_id',

        // Location Information
        'country_id',
        'region_id',
        'city_id',

        // Basic Supplier Information
        'supplier_name_ar',
        'supplier_name_en',
        'supplier_code',
        'supplier_number',
        'supplier_type',
        'contact_person',

        // Personal Names
        'first_name',
        'second_name',
        'contact_name',

        // Contact Information
        'email',
        'phone',
        'mobile',
        'website',

        // Address Information
        'address_one',
        'address_two',
        'address',
        'postal_code',

        // Financial Information
        'tax_number',
        'commercial_register',
        'credit_limit',
        'payment_terms',
        'balance',
        'last_transaction_date',

        // Account Data
        'code_number',
        'barcode_type_id',

        // Classification
        'classification',
        'custom_classification',

        // Additional Information
        'notes',

        // Status
        'status',
        'active',

        // Audit Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'payment_terms' => 'integer',
        'active' => 'boolean',
        'last_transaction_date' => 'date',
        'supplier_type' => 'string',
        'classification' => 'string',
        'status' => 'string',
    ];

    // Constants for supplier types
    const SUPPLIER_TYPE_OPTIONS = [
        'individual' => 'Individual',
        'business' => 'Business',
    ];

    // Constants for classification
    const CLASSIFICATION_OPTIONS = [
        'major' => 'Major Suppliers',
        'medium' => 'Medium Suppliers',
        'minor' => 'Minor Suppliers',
    ];

    /**
     * Get the user that owns the supplier.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the supplier.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the supplier.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\Branch::class);
    }

    /**
     * Get the currency for the supplier.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(\Modules\FinancialAccounts\Models\Currency::class);
    }

    /**
     * Get the department for the supplier.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(\Modules\HumanResources\Models\Department::class);
    }

    /**
     * Get the project for the supplier.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(\Modules\ProjectsManagment\Models\Project::class);
    }

    /**
     * Get the donor for the supplier.
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get the sales representative for the supplier.
     */
    public function salesRepresentative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class, 'sales_representative_id');
    }

    /**
     * Get the country for the supplier.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\Country::class);
    }

    /**
     * Get the region for the supplier.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\Region::class);
    }

    /**
     * Get the city for the supplier.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\City::class);
    }

    /**
     * Get the barcode type for the supplier.
     */
    public function barcodeType(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\BarcodeType::class);
    }

    /**
     * Get the user who created the supplier.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the supplier.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the supplier.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Generate the next sequential supplier number.
     */
    public static function generateSupplierNumber(): string
    {
        $lastSupplier = self::orderBy('id', 'desc')->first();

        if (!$lastSupplier) {
            return 'SUP-0001';
        }

        // Extract number from last supplier number (assuming format SUP-XXXX)
        $lastNumber = (int) substr($lastSupplier->supplier_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'SUP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get supplier type display name.
     */
    public function getSupplierTypeDisplayAttribute(): string
    {
        return self::SUPPLIER_TYPE_OPTIONS[$this->supplier_type] ?? $this->supplier_type;
    }

    /**
     * Get classification display name.
     */
    public function getClassificationDisplayAttribute(): string
    {
        if ($this->custom_classification) {
            return $this->custom_classification;
        }

        return self::CLASSIFICATION_OPTIONS[$this->classification] ?? $this->classification;
    }

    /**
     * Scope to get active suppliers only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get suppliers for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get suppliers by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('supplier_type', $type);
    }

    /**
     * Scope to get suppliers by classification.
     */
    public function scopeByClassification($query, $classification)
    {
        return $query->where('classification', $classification);
    }
}
