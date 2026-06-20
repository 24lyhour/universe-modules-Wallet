<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Modules\Wallets\Enums\TopUpStatus;
use Modules\Wallets\Models\TopUp;
use Modules\Wallets\Models\Wallet;

class CreateTopUpAction
{
    /**
     * Create a top-up record for a wallet.
     *
     * Manual top-ups are completed immediately (credits the wallet).
     * Gateway top-ups are left PENDING for the provider callback to finalize.
     */
    public function execute(Wallet $wallet, array $data): TopUp
    {
        $topup = TopUp::create([
            'wallet_id' => $wallet->id,
            'customer_id' => $wallet->customer_id,
            'amount' => $data['amount'],
            'currency' => $wallet->currency,
            'payment_method' => $data['payment_method'],
            'provider' => $data['provider'] ?? null,
            'gateway_reference' => $data['gateway_reference'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'status' => TopUpStatus::PENDING,
            'created_by' => Auth::id(),
        ]);

        if (($data['payment_method'] ?? null) === 'manual') {
            $topup->complete();
            $topup->refresh();
        }

        return $topup;
    }
}
