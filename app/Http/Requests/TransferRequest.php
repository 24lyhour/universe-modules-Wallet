<?php

namespace Modules\Wallets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_wallet_id' => [
                'required',
                'integer',
                'exists:wallets,id',
                Rule::notIn([$this->route('wallet')->id]),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_wallet_id.not_in' => 'Cannot transfer to the same wallet.',
        ];
    }
}
