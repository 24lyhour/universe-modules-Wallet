<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use Modules\Wallets\Models\Wallet;

class CreateWalletAction
{
    public function __construct(
        protected GenerateWalletIdAction $generateWalletIdAction,
        protected GenerateWalletNumberAction $generateWalletNumberAction
    ) {}

    /**
     * Create a new wallet with auto-generated IDs.
     */
    public function execute(array $data): Wallet
    {
        // Auto-generate wallet_id if not provided
        if (empty($data['wallet_id'])) {
            $data['wallet_id'] = $this->generateWalletIdAction->execute();
        }

        // Auto-generate wallet_number if not provided
        if (empty($data['wallet_number'])) {
            $data['wallet_number'] = $this->generateWalletNumberAction->execute();
        }

        return Wallet::create($data);
    }
}
