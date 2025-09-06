<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Modules\Users\Models\User;

class AuthController extends Controller
{
    // تسجيل مستخدم جديد
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'first_name'  => 'required|string|max:255',
                'second_name' => 'required|string|max:255',
                'email'       => 'required|email|unique:users,email',
                'phone'       => 'nullable|string|unique:users,phone',
                'password'    => ['required', 'string', Password::min(8)],
            ]);

            $user = User::create([
                'first_name' => $data['first_name'],
                'second_name' => $data['second_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?? null,
                'password'   => Hash::make($data['password']),
                'status'     => 'active',
                'type'       => 'customer',
                'created_by' => 1, // لو أول مستخدم ممكن تعطيه 1
                'updated_by' => 1,
            ]);

            $user->update(['created_by' => $user->id, 'updated_by' => $user->id]);

            event(new Registered($user));

            // إرسال OTP للهاتف
            $otp = random_int(1000, 9999);
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
            ]);

            // هنا ترسل OTP عبر SMS (Twilio, Vonage...)

            DB::commit();
            return response()->json([
                'message' => __('Account created successfully. Please check your email to activate your account.'),
                'user'  => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => __('An error occurred during registration.'), 'error' => $e->getMessage()], 500);
        }
    }

    // تسجيل الدخول
    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                DB::rollBack();
                return response()->json(['message' => __('Invalid login credentials.')], 422);
            }

            if ($user->status !== 'active') {
                DB::rollBack();
                return response()->json(['message' => __('Account is not activated.')], 403);
            }

            // التحقق من تفعيل البريد
            if (!$user->hasVerifiedEmail()) {
                DB::rollBack();
                return response()->json([
                    'message' => __('Please verify your email before logging in.'),
                    'email' => $user->email
                ], 403);
            }

            if (!$user->phone_verified_at) {
                DB::rollBack();
                return response()->json(['message' => __('Please verify your phone before logging in.')], 403);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'user'  => $user,
                'token' => $token,
                'type'  => 'Bearer',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('An error occurred during login.'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // تسجيل الدخول بالهاتف + OTP (اختياري)
    public function sendOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'phone' => 'required|string|exists:users,phone',
            ]);

            $user = User::where('phone', $data['phone'])->first();

            $otp = random_int(1000, 9999);
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
            ]);

            // هنا تبعت الكود عبر SMS (Twilio, Vonage, إلخ)

            DB::commit();
            return response()->json(['message' => __('Verification code sent successfully.')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => __('An error occurred while sending the verification code.'), 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'phone' => 'required|string|exists:users,phone',
                'otp'   => 'required|string|size:4',
            ]);

            $user = User::where('phone', $data['phone'])->first();

            if (! $user || $user->otp_code !== $data['otp'] || $user->otp_expires_at->isPast()) {
                DB::rollBack();
                return response()->json(['message' => __('Invalid verification code.')], 422);
            }

            // تحديث حالة التحقق فقط
            $user->update([
                'otp_code' => null,
                'otp_expires_at' => null,
                'phone_verified_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => __('OTP verified successfully.'),
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('An error occurred during OTP verification.'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // المستخدم الحالي
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // تسجيل الخروج
    public function logout(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->user()->currentAccessToken()->delete();

            DB::commit();
            return response()->json(['message' => __('Logged out successfully.')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => __('An error occurred during logout.'), 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['message' => __('Invalid verification link.')], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('Email already verified.')]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => __('Email verified successfully.')]);
    }

    public function resendEmailVerification(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => __('Verification link sent to your email.')]);
    }
}
