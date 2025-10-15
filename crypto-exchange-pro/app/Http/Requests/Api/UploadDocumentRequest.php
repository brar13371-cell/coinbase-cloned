<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
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
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // 10MB max
            'type' => ['required', 'in:front,back,selfie'],
            'document_type' => ['nullable', 'in:passport,drivers_license,national_id'],
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
            'document.required' => 'Document file is required.',
            'document.file' => 'Document must be a file.',
            'document.mimes' => 'Document must be a JPG, PNG, or PDF file.',
            'document.max' => 'Document size must not exceed 10MB.',
            'type.required' => 'Document type is required.',
            'type.in' => 'Document type must be front, back, or selfie.',
        ];
    }
}