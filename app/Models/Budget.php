<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $table = 'budgets';

    protected $fillable = [
        'company_id',
        'user_id',
        'number',
        'date',
        'start_date',
        'end_date',
        'currency_id',
        'description',
        'description_en',
        'notes',
        'total_budget',
        'total_income',
    ];

    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }
}
