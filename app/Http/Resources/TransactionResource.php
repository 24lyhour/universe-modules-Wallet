<?php

namespace Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type->value ?? $this->type,
            'status' => $this->status->value ?? $this->status,
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'net_amount' => (float) $this->net_amount,
            'signed_amount' => (float) $this->signed_amount,
            'balance_before' => (float) $this->balance_before,
            'balance_after' => (float) $this->balance_after,
            'currency' => $this->currency,
            'description' => $this->description,
            'payment_method' => $this->payment_method,
            'is_credit' => $this->is_credit,
            'is_debit' => $this->is_debit,
            'created_at' => $this->created_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}
