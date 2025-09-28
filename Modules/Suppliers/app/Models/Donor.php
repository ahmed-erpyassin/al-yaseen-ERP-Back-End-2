<?php

namespace Modules\Suppliers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Company;

class Donor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'donor_number',
        'donor_name_ar',
        'donor_name_en',
        'donor_code',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'website',
        'address',
        'country_id',
        'region_id',
        'city_id',
        'donor_type',
        'category',
        'total_donations',
        'current_year_donations',
        'currency_id',
        'notes',
        'first_donation_date',
        'last_donation_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total_donations' => 'decimal:2',
        'current_year_donations' => 'decimal:2',
        'first_donation_date' => 'date',
        'last_donation_date' => 'date',
        'donor_type' => 'string',
        'category' => 'string',
        'status' => 'string',
    ];

    // Constants for donor types
    const DONOR_TYPE_OPTIONS = [
        'individual' => 'Individual',
        'organization' => 'Organization',
        'government' => 'Government',
        'international' => 'International',
    ];

    // Constants for categories
    const CATEGORY_OPTIONS = [
        'major' => 'Major Donors',
        'medium' => 'Medium Donors',
        'minor' => 'Minor Donors',
    ];

    /**
     * Get the user that owns the donor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the donor.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the donor.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the donor.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate the next sequential donor number.
     */
    public static function generateDonorNumber(): string
    {
        $lastDonor = self::orderBy('id', 'desc')->first();

        if (!$lastDonor) {
            return 'DON-0001';
        }

        // Extract number from last donor number (assuming format DON-XXXX)
        $lastNumber = (int) substr($lastDonor->donor_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'DON-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope to get active donors only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get donors for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
