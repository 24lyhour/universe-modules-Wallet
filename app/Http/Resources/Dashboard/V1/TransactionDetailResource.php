<?php

namespace Modules\Wallets\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
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
            'metadata' => $this->metadata,
            'is_credit' => $this->is_credit,
            'is_debit' => $this->is_debit,
            'is_reversed' => $this->is_reversed,
            'is_final' => $this->is_final,
            'can_reverse' => $this->status->canReverse() && !$this->is_reversed,
            'can_cancel' => $this->status->canCancel(),
            'related_wallet' => $this->whenLoaded('relatedWallet', fn () => $this->relatedWallet ? [
                'id' => $this->relatedWallet->id,
                'wallet_number' => $this->relatedWallet->wallet_number,
            ] : null),
            'reversed_transaction' => $this->whenLoaded('reversedTransaction', fn () => $this->reversedTransaction ? [
                'id' => $this->reversedTransaction->id,
                'reference' => $this->reversedTransaction->reference,
            ] : null),
            'reversal_transaction' => $this->whenLoaded('reversalTransaction', fn () => $this->reversalTransaction ? [
                'id' => $this->reversalTransaction->id,
                'reference' => $this->reversalTransaction->reference,
            ] : null),
            'processed_at' => $this->processed_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'failure_reason' => $this->failure_reason,
            'reversed_at' => $this->reversed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
