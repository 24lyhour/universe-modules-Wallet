<?php

namespace Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'wallet_id' => $this->wallet_id,
            'transaction_id' => $this->transaction_id,
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'net_amount' => (float) $this->net_amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'provider' => $this->provider,
            'gateway_reference' => $this->gateway_reference,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'description' => $this->description,
            'failure_reason' => $this->failure_reason,
            'is_final' => $this->is_final,
            'completed_at' => $this->completed_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
