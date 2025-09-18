<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $table = 'budget_items';

    protected $fillable = [
        'budget_id',
        'account_number',
        'dapertment',
        'amount',
        'expense',
        'allocated',
        'notes',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
