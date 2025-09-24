<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name'  => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'nullable|string|unique:users,phone',
            'password'    => ['required', 'string', Password::min(8)],
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
            'first_name.required'  => __('First name is required.'),
            'second_name.required' => __('Second name is required.'),
            'email.required'       => __('Email is required.'),
            'email.email'          => __('Email must be a valid email address.'),
            'email.unique'         => __('This email is already registered.'),
            'phone.unique'         => __('This phone number is already registered.'),
            'password.required'    => __('Password is required.'),
            'password.min'         => __('Password must be at least 8 characters long.'),
        ];
    }
}
