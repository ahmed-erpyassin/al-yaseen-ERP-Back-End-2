<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'document_type',
        'document_date',
        'file_path',
        'related_type',
        'related_id'
    ];

    public function related() {

        return $this->morphTo();

    }
}
