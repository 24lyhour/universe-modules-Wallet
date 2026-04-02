<?php

namespace Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_number' => $this->wallet_number,
            'balance' => (float) $this->balance,
            'available_balance' => (float) $this->available_balance,
            'locked_amount' => (float) $this->locked_amount,
            'currency' => $this->currency,
            'status' => $this->status->value ?? $this->status,
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
