<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOTPRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'phone'    => 'required|string|exists:users,phone',
            'otp' => 'required|string',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'phone.required'    => __('Phone number is required.'),
            'phone.exists'      => __('This phone number is not registered.'),
            'otp.required' => __('OTP code is required.'),
        ];
    }
}
