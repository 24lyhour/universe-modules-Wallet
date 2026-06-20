<?php

namespace Modules\Wallets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:10000'],
            'payment_method' => ['required', 'string', 'max:50'],
            'provider' => ['nullable', 'string', 'max:50'],
            'gateway_reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Top-up amount must be at least 1.',
            'amount.max' => 'Top-up amount cannot exceed 10,000.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
