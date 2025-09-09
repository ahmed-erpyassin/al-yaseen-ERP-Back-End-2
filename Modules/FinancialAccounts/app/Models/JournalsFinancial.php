<?php

namespace Modules\FinancialAccounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\FinancialAccounts\Database\Factories\JournalsFinancialFactory;

class JournalsFinancial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): JournalsFinancialFactory
    // {
    //     // return JournalsFinancialFactory::new();
    // }
}
