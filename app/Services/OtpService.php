<?php


namespace App\Services;

use App\Models\User;
use App\Models\UserOtp;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class OtpService
{


    public static function forgetPassword(User $user,int $otp)
    {

        try {

            UserOtp::where('user_id', $user->id)->delete();


            $token = Str::random(60);

            UserOtp::create([
                'user_id'           => $user->id,
                'type'              => 'forget-password',
                'otp'               => $otp,
                'otp_expires_at'    => Carbon::now()->addMinutes(5),
                'token'             => $token
            ]);

            return $token;

        } catch (Exception $exception) {

            return response([
                'success'   => false,
                'message'   => $exception->getMessage()
            ], 500);
        }
    }
}
