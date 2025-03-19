<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:30',
            'mid_name' => 'nullable|string|max:30',
            'last_name' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email,' . request()->user()->id,
            'contact_no' => [
                'required',
                'regex:/^(09\d{9}|\\+639\d{9})$/'
            ],
            'address' => 'nullable|string|max:100',
            'customer_type' => 'nullable|integer',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name must not exceed 30 characters.',
            'mid_name.string' => 'Middle name must be a string.',
            'mid_name.max' => 'Middle name must not exceed 30 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name must not exceed 30 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email is already taken.',
            'contact_no.required' => 'Contact number is required.',
            'contact_no.regex' => 'Contact number must be a valid phone number.',
            'address.string' => 'Address must be a string.',
            'address.max' => 'Address must not exceed 100 characters.',
            'customer_type.integer' => 'Customer type must be an integer.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
