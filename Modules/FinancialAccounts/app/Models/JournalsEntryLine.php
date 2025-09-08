<?php

namespace Modules\FinancialAccounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\FinancialAccounts\Database\Factories\JournalsEntryLineFactory;

class JournalsEntryLine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): JournalsEntryLineFactory
    // {
    //     // return JournalsEntryLineFactory::new();
    // }
}
