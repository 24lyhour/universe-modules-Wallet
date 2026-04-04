<?php

namespace Modules\Wallets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $wallet = $this->route('wallet');
        $walletId = $wallet instanceof \Modules\Wallets\Models\Wallet ? $wallet->id : $wallet;

        return [
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'wallet_number' => ['sometimes', 'string', 'unique:wallets,wallet_number,' . $walletId],
            'balance' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:3'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
            'description' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Selected customer does not exist',
            'wallet_number.unique' => 'This wallet number already exists',
            'balance.numeric' => 'Balance must be a number',
            'balance.min' => 'Balance cannot be negative',
            'status.in' => 'Status must be active or inactive',
        ];
    }
}
