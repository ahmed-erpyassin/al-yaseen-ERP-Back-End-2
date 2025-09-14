<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingShipment extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'notebook',
        'invoice_number',
        'invoice_date',
        'invoice_time',
        'due_date',
        'client_id',
        'notes',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
