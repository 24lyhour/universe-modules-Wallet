<?php

namespace Modules\Wallets\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_number' => $this->wallet_number,
            'balance' => (float) $this->balance,
            'locked_amount' => (float) $this->locked_amount,
            'available_balance' => (float) $this->available_balance,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'can_transact' => $this->canTransact(),
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ] : null),
        ];
    }
}
