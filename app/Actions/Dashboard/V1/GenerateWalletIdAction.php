<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use App\Models\Setting;
use Modules\Wallets\Models\Wallet;

class GenerateWalletIdAction
{
    /**
     * Generate a unique wallet ID.
     * Format is configurable via Settings (Dashboard > Settings > Wallet)
     * Default: W + 8 digits (e.g., W00000001)
     */
    public function execute(): string
    {
        $prefix = Setting::getValue('wallet', 'id_prefix', 'W');
        $padding = Setting::getValue('wallet', 'id_padding', 8);

        $lastWallet = Wallet::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastWallet ? ($lastWallet->id + 1) : 1;

        // Ensure uniqueness
        do {
            $walletId = $prefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Wallet::where('wallet_id', $walletId)->exists());

        return $walletId;
    }
}
