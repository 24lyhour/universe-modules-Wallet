<?php

namespace Modules\Wallets\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'wallet_id' => $this->wallet_id,
            'wallet_number' => $this->whenLoaded('wallet', fn () => $this->wallet?->wallet_number),
            'customer_name' => $this->whenLoaded('wallet', fn () => $this->wallet?->customer?->name ?? 'N/A'),
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'type_color' => $this->type->color(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_variant' => $this->status->variant(),
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'net_amount' => (float) $this->net_amount,
            'signed_amount' => (float) $this->signed_amount,
            'balance_before' => (float) $this->balance_before,
            'balance_after' => (float) $this->balance_after,
            'currency' => $this->currency,
            'description' => $this->description,
            'external_reference' => $this->external_reference,
            'payment_method' => $this->payment_method,
            'is_credit' => $this->is_credit,
            'is_debit' => $this->is_debit,
            'is_reversed' => $this->is_reversed,
            'related_wallet' => $this->whenLoaded('relatedWallet', fn () => $this->relatedWallet ? [
                'id' => $this->relatedWallet->id,
                'wallet_number' => $this->relatedWallet->wallet_number,
            ] : null),
            'created_at' => $this->created_at->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}
