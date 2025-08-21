<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\SendOtpNotification;
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

        $otp = rand(1000, 9999); // 4 digit OTP

        UserOtp::create([
            'user_id'           => $user->id,
            'type'              => 'forget-password',
            'otp'               => $otp,
            'otp_expires_at'    => Carbon::now()->addMinutes(5)
        ]);

        $user->notify(new SendOtpNotification($otp));

        return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
    }

    public function checkOtp(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $otp = UserOtp::where('user_id', $user->id)->where('otp', $request->otp)->where('otp_expires_at', '>', Carbon::now())->first();
        if (! $otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }
        return response()->json(['message' => 'Password reset successfully']);
    }

    public function resetPassword() {}

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
