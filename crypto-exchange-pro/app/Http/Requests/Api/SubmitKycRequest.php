<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SubmitKycRequest extends FormRequest
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
            'document_type' => ['required', 'in:passport,drivers_license,national_id'],
            'document_front' => ['required', 'string', 'max:255'],
            'document_back' => ['nullable', 'string', 'max:255'],
            'selfie' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'Document type is required.',
            'document_type.in' => 'Invalid document type.',
            'document_front.required' => 'Front document is required.',
            'selfie.required' => 'Selfie is required.',
        ];
    }
}