<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
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
            'currency' => ['required', 'string', 'max:10'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
            'fee' => ['required', 'numeric', 'min:0'],
            'to_address' => ['required', 'string', 'max:255'],
            'memo' => ['nullable', 'string', 'max:255'],
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
            'currency.required' => 'Currency is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than 0.',
            'fee.required' => 'Fee is required.',
            'fee.numeric' => 'Fee must be a number.',
            'fee.min' => 'Fee must be greater than or equal to 0.',
            'to_address.required' => 'Destination address is required.',
        ];
    }
}