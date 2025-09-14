<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{

    protected $fillable = [
        'user_id',
        'type',
        'otp',
        'otp_expires_at',
        'token'
    ];

    protected $table = 'user_otps';


    public function user() {

        return $this->belongsTo(User::class, 'user_id');

    }

}
