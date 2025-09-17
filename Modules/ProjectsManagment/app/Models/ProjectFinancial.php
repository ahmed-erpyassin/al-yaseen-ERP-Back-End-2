<?php

namespace Modules\ProjectsManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;
use Modules\FinancialAccounts\Models\Currency;

class ProjectFinancial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'currency_id',
        'project_id',
        'exchange_rate',
        'reference_type',
        'reference_id',
        'amount',
        'date',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
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

    public function scopeByReferenceType($query, $referenceType)
    {
        return $query->where('reference_type', $referenceType);
    }

    public function scopeByReferenceId($query, $referenceId)
    {
        return $query->where('reference_id', $referenceId);
    }

    public function scopeByDateRange($query, $dateFrom, $dateTo)
    {
        return $query->whereDate('date', '>=', $dateFrom)
                    ->whereDate('date', '<=', $dateTo);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->where('amount', '>=', $minAmount)
                    ->where('amount', '<=', $maxAmount);
    }

    public function scopeByCurrency($query, $currencyId)
    {
        return $query->where('currency_id', $currencyId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('reference_type', 'like', "%{$search}%")
              ->orWhere('reference_id', 'like', "%{$search}%")
              ->orWhere('amount', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('project', function ($projectQuery) use ($search) {
                  $projectQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('project_number', 'like', "%{$search}%");
              })
              ->orWhereHas('currency', function ($currencyQuery) use ($search) {
                  $currencyQuery->where('currency_code', 'like', "%{$search}%")
                               ->orWhere('currency_name_ar', 'like', "%{$search}%")
                               ->orWhere('currency_name_en', 'like', "%{$search}%");
              });
        });
    }

    // Helper Methods
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('d/m/Y') : null;
    }

    public function getFormattedExchangeRateAttribute()
    {
        return number_format($this->exchange_rate, 4);
    }

    public function getReferenceDisplayAttribute()
    {
        return $this->reference_type . ' - ' . $this->reference_id;
    }

    public function getAmountWithCurrencyAttribute()
    {
        if ($this->relationLoaded('currency') && $this->currency) {
            return $this->currency->currency_code . ' ' . $this->formatted_amount;
        }
        return $this->formatted_amount;
    }

    // Static Methods
    public static function getTotalByProject($projectId, $companyId)
    {
        return static::forCompany($companyId)
            ->forProject($projectId)
            ->sum('amount');
    }

    public static function getTotalByReferenceType($referenceType, $companyId)
    {
        return static::forCompany($companyId)
            ->byReferenceType($referenceType)
            ->sum('amount');
    }

    public static function getCountByProject($projectId, $companyId)
    {
        return static::forCompany($companyId)
            ->forProject($projectId)
            ->count();
    }

    public static function getReferenceTypes($companyId)
    {
        return static::forCompany($companyId)
            ->distinct()
            ->pluck('reference_type')
            ->filter()
            ->values();
    }

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($projectFinancial) {
            // Set default exchange rate if not provided
            if (empty($projectFinancial->exchange_rate)) {
                $projectFinancial->exchange_rate = 1.0000;
            }
        });

        static::updating(function ($projectFinancial) {
            // Update exchange rate if currency changed and no new rate provided
            if ($projectFinancial->isDirty('currency_id') && !$projectFinancial->isDirty('exchange_rate')) {
                $projectFinancial->exchange_rate = 1.0000; // You can implement logic to get actual rate
            }
        });
    }
}
