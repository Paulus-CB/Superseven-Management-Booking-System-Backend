<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddBookingRequest extends FormRequest
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
            'booking_date' => 'required|date|after_or_equal:today',
            'customer_id' => 'required|integer',
            'package_id' => 'required|integer',
            'event_name' => 'required|string|max:100',
            'booking_address' => 'required|string|max:100',
            'deliverable_status' => 'nullable|integer',
            'completion_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'booking_date.required' => 'The booking date is required.',
            'booking_date.after_or_equal'=> 'The booking date must be after or equal to today.',
            'customer_id.required' => 'The customer ID is required.',
            'package_id.required' => 'The package ID is required.',
            'event_name.required' => 'The event name is required.',
            'event_name.max' => 'The event name must not exceed 100 characters.',
            'booking_address.required' => 'The booking address is required.',
            'deliverable_status.integer' => 'The deliverable status must be an integer.',
            'completion_date.date' => 'The completion date must be a valid date.',
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
