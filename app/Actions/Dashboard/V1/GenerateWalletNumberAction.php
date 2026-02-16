<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use App\Models\Setting;
use Modules\Wallets\Models\Wallet;

class GenerateWalletNumberAction
{
    /**
     * Generate a unique wallet number.
     * Format is configurable via Settings (Dashboard > Settings > Wallet)
     * Default: WLT-YYYYMMDD-XXXXX (e.g., WLT-20260216-A3B5C)
     */
    public function execute(): string
    {
        $prefix = Setting::getValue('wallet', 'number_prefix', 'WLT');
        $separator = Setting::getValue('wallet', 'number_separator', '-');
        $dateFormat = Setting::getValue('wallet', 'number_date_format', 'Ymd');
        $randomLength = Setting::getValue('wallet', 'number_random_length', 5);

        $date = now()->format($dateFormat);

        do {
            $random = substr(bin2hex(random_bytes(ceil($randomLength / 2))), 0, $randomLength);
            $random = strtoupper($random);
            $walletNumber = "{$prefix}{$separator}{$date}{$separator}{$random}";
        } while (Wallet::where('wallet_number', $walletNumber)->exists());

        return $walletNumber;
    }
}
