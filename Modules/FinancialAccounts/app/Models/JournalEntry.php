<?php

namespace Modules\FinancialAccounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;

class JournalEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'journals_entries';

    protected $fillable = [
        'fiscal_year_id',
        'user_id',
        'company_id',
        'branch_id',
        'journal_id',
        'document_id',
        'type',
        'entry_number',
        'entry_date',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'fiscal_year_id',
            'user_id',
            'company_id',
            'branch_id',
            'journal_id',
            'document_id',
            'type',
            'entry_number',
            'entry_date',
            'description',
            'status',
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
            'type' => null,
            'status' => null,
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('entry_number', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['type']) {
            $builder->where('type', $filters['type']);
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }

        return $builder;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function journal()
    {
        return $this->belongsTo(JournalsFinancial::class, 'journal_id');
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

    public function scopeStore(Builder $builder, array $data = [])
    {
        return $builder->create($data);
    }

    public function scopeUpdateModel(Builder $builder, $data, $id)
    {
        return $builder->where('id', $id)->update($data);
    }
}
