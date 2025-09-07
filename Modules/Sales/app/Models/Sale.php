<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales';

    protected $guarded = ['id'];

    /**
     * get user who created the record
     */

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }


}
