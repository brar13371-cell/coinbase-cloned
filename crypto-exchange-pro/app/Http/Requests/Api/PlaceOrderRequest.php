<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
            'pair_id' => ['required', 'integer', 'exists:trading_pairs,id'],
            'side' => ['required', 'in:buy,sell'],
            'type' => ['required', 'in:market,limit,stop,stop_limit'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
            'price' => ['required_if:type,limit,stop_limit', 'numeric', 'min:0.00000001'],
            'stop_price' => ['required_if:type,stop,stop_limit', 'numeric', 'min:0.00000001'],
            'time_in_force' => ['nullable', 'in:GTC,IOC,FOK,GTD'],
            'expires_at' => ['nullable', 'date', 'after:now'],
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
            'pair_id.required' => 'Trading pair is required.',
            'pair_id.exists' => 'Invalid trading pair.',
            'side.required' => 'Order side is required.',
            'side.in' => 'Order side must be buy or sell.',
            'type.required' => 'Order type is required.',
            'type.in' => 'Invalid order type.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than 0.',
            'price.required_if' => 'Price is required for limit and stop-limit orders.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be greater than 0.',
            'stop_price.required_if' => 'Stop price is required for stop and stop-limit orders.',
            'stop_price.numeric' => 'Stop price must be a number.',
            'stop_price.min' => 'Stop price must be greater than 0.',
            'time_in_force.in' => 'Invalid time in force value.',
            'expires_at.date' => 'Expiration date must be a valid date.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }
}