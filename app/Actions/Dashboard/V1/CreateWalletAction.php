<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use Modules\Wallets\Models\Wallet;

class CreateWalletAction
{
    public function __construct(
        protected GenerateWalletNumberAction $generateWalletNumberAction
    ) {}

    /**
     * Create a new wallet with auto-generated wallet number.
     */
    public function execute(array $data): Wallet
    {
        // Auto-generate wallet_number if not provided
        if (empty($data['wallet_number'])) {
            $data['wallet_number'] = $this->generateWalletNumberAction->execute();
        }

        return Wallet::create($data);
    }
}
