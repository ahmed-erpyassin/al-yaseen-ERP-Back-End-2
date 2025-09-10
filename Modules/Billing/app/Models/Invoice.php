<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Suppliers\Models\Supplier;
use Modules\Users\Models\User;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'financial_year_id',
        'company_id',
        'branch_id',
        'currency_id',
        'customer_id',
        'supplier_id',
        'invoice_type',
        'invoice_number',
        'journal_id',
        'journal_entry_id',
        'exchange_rate',
        'invoice_date',
        'due_date',
        'discount',
        'subtotal',
        'tax_total',
        'total',
        'paid_amount',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'financial_year_id',
            'company_id',
            'branch_id',
            'currency_id',
            'customer_id',
            'supplier_id',
            'invoice_type',
            'invoice_number',
            'journal_id',
            'journal_entry_id',
            'exchange_rate',
            'invoice_date',
            'due_date',
            'discount',
            'subtotal',
            'tax_total',
            'total',
            'paid_amount',
            'status',
            'approved_by',
            'approved_at',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at'
        ]);
    }

    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'status' => null,
            'invoice_type' => null,
            'customer_id' => null,
            'supplier_id' => null,
            'date_from' => null,
            'date_to' => null,
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('invoice_number', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }

        if ($filters['invoice_type']) {
            $builder->where('invoice_type', $filters['invoice_type']);
        }

        if ($filters['customer_id']) {
            $builder->where('customer_id', $filters['customer_id']);
        }

        if ($filters['supplier_id']) {
            $builder->where('supplier_id', $filters['supplier_id']);
        }

        if ($filters['date_from']) {
            $builder->whereDate('invoice_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $builder->whereDate('invoice_date', '<=', $filters['date_to']);
        }
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // المنشئ
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // المحدث
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // المحذف
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function financialYear()
    {
        return $this->belongsTo(FiscalYear::class, 'financial_year_id');
    }

    // دفاتر الفواتير
    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    // رأس القيد المحاسبي
    public function journalEntry()
    {
        // return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
