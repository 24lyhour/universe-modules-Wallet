<?php

namespace Modules\Wallets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'wallet_number' => ['nullable', 'string', 'unique:wallets,wallet_number'], // Auto-generated if not provided
            'balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required',
            'customer_id.exists' => 'Selected customer does not exist',
            'wallet_number.unique' => 'This wallet number already exists',
            'balance.numeric' => 'Balance must be a number',
            'balance.min' => 'Balance cannot be negative',
        ];
    }
}
