<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBookingRequest extends FormRequest
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
            'booking_date' => 'nullable|date|after_or_equal:+30 days',
            'event_name' => 'nullable|string|max:100',
            'booking_address' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'booking_date.date' => 'The booking date must be a valid date.',
            'booking_date.after_or_equal'=> 'The booking date must be at least 30 days from today.',
            'event_name.max' => 'The event name must not exceed 100 characters.',
            'booking_address.max' => 'The booking address must not exceed 100 characters.',
        ];
    }

        /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
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
