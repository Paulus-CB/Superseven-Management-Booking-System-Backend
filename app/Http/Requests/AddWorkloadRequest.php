<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddWorkloadRequest extends FormRequest
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
            'completion_date' => 'required|date|after_or_equal:today',
            'deliverable_status' => 'nullable|integer',
            'link' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'completion_date.required' => 'The completion date is required.',
            'completion_date.date' => 'The completion date must be a valid date.',
            'completion_date.after_or_equal' => 'The completion date must be after or equal to today.',
            'deliverable_status.integer' => 'The deliverable status must be an integer.',
            'link.required' => 'The link is required.',
            'link.url' => 'The link must be a valid URL.',
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
