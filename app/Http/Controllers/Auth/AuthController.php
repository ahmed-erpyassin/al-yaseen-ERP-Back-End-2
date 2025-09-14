<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckOtpRequest;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\SendOtpNotification;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $data = $request->validated();

        $user = User::create([
            'firstname'     => $data['firstname'],
            'secondname'    => $data['secondname'],
            'email'         => $data['email'],
            'country_code'  => '+' . $data['country_code'],
            'phone'         => $data['phone'],
            'allows_emails' => $data['allows_emails'] ?? false,
            'password'      => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success'  => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['success' => true, 'user' => $user, 'token' => $token]);
    }

    public function forgotPassword(ForgetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $otp = rand(1000, 9999);

        $token = OtpService::forgetPassword($user, $otp);

        $user->notify(new SendOtpNotification($otp));

        return response()->json(['success' => true, 'message' => 'OTP sent successfully', 'token' => $token]);
    }

    public function checkOtp(CheckOtpRequest $request)
    {
        $otp = UserOtp::where('token', $request->token)->where('otp', $request->otp)->where('otp_expires_at', '>', Carbon::now())->first();
        if (! $otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 401);
        }
        return response()->json(['success' => true, 'message' => 'Password reset successfully']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {

        $user = UserOtp::where('token', $request->token)->first()->user;

        $user->password = Hash::make($request->password);

        $user->save();

        return response()->json(['success' => true,'message' => 'Password reset successfully']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
