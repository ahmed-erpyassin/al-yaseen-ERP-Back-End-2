<?php

namespace Modules\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Region;
use Modules\Companies\Models\City;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Sales\Models\Sale;
use Modules\Billing\Models\Invoice;
use Illuminate\Support\Facades\DB;
// use Modules\Customers\Database\Factories\CustomerFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the customer.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the currency for the customer.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the country for the customer.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the region for the customer.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the city for the customer.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the user who created the customer.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the customer.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the customer.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the sales for this customer.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }

    /**
     * Get the invoices for this customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    /**
     * Get the employee (sales representative) for this customer.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the last transaction date for this customer.
     */
    public function getLastTransactionDateAttribute()
    {
        $lastSale = $this->sales()->latest('created_at')->first();
        $lastInvoice = $this->invoices()->latest('created_at')->first();

        $lastSaleDate = $lastSale ? $lastSale->created_at : null;
        $lastInvoiceDate = $lastInvoice ? $lastInvoice->created_at : null;

        if ($lastSaleDate && $lastInvoiceDate) {
            return $lastSaleDate->gt($lastInvoiceDate) ? $lastSaleDate : $lastInvoiceDate;
        }

        return $lastSaleDate ?: $lastInvoiceDate;
    }

    /**
     * Scope a query to only include customers for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to search by customer number range.
     */
    public function scopeByCustomerNumberRange($query, $from = null, $to = null)
    {
        if ($from && $to) {
            return $query->whereBetween('customer_number', [$from, $to]);
        } elseif ($from) {
            return $query->where('customer_number', '>=', $from);
        } elseif ($to) {
            return $query->where('customer_number', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope to search by customer name.
     */
    public function scopeByName($query, $name)
    {
        if ($name) {
            return $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', '%' . $name . '%')
                  ->orWhere('second_name', 'like', '%' . $name . '%')
                  ->orWhere('company_name', 'like', '%' . $name . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', second_name) LIKE ?", ['%' . $name . '%']);
            });
        }

        return $query;
    }

    /**
     * Scope to filter by sales representative (employee).
     */
    public function scopeBySalesRepresentative($query, $employeeId)
    {
        if ($employeeId) {
            return $query->where('employee_id', $employeeId);
        }

        return $query;
    }

    /**
     * Scope to filter by currency.
     */
    public function scopeByCurrency($query, $currencyId)
    {
        if ($currencyId) {
            return $query->where('currency_id', $currencyId);
        }

        return $query;
    }

    /**
     * Scope to filter by last transaction date.
     */
    public function scopeByLastTransactionDate($query, $date = null, $dateFrom = null, $dateTo = null)
    {
        if ($date) {
            // Exact date search
            return $query->whereHas('sales', function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            })->orWhereHas('invoices', function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            });
        } elseif ($dateFrom && $dateTo) {
            // Date range search
            return $query->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('sales', function ($subQ) use ($dateFrom, $dateTo) {
                    $subQ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
                })->orWhereHas('invoices', function ($subQ) use ($dateFrom, $dateTo) {
                    $subQ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
                });
            });
        } elseif ($dateFrom) {
            // From date only
            return $query->where(function ($q) use ($dateFrom) {
                $q->whereHas('sales', function ($subQ) use ($dateFrom) {
                    $subQ->whereDate('created_at', '>=', $dateFrom);
                })->orWhereHas('invoices', function ($subQ) use ($dateFrom) {
                    $subQ->whereDate('created_at', '>=', $dateFrom);
                });
            });
        } elseif ($dateTo) {
            // To date only
            return $query->where(function ($q) use ($dateTo) {
                $q->whereHas('sales', function ($subQ) use ($dateTo) {
                    $subQ->whereDate('created_at', '<=', $dateTo);
                })->orWhereHas('invoices', function ($subQ) use ($dateTo) {
                    $subQ->whereDate('created_at', '<=', $dateTo);
                });
            });
        }

        return $query;
    }

    // protected static function newFactory(): CustomerFactory
    // {
    //     // return CustomerFactory::new();
    // }
}
