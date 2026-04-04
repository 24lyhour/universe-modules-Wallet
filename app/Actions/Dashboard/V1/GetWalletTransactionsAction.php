<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use Modules\Wallets\Enums\TransactionStatus;
use Modules\Wallets\Enums\TransactionType;
use Modules\Wallets\Http\Resources\Dashboard\V1\TransactionResource;
use Modules\Wallets\Http\Resources\Dashboard\V1\WalletSummaryResource;
use Modules\Wallets\Models\Wallet;

class GetWalletTransactionsAction
{
    /**
     * Get transactions for a specific wallet with filters.
     */
    public function execute(Wallet $wallet, int $perPage, array $filters): array
    {
        $wallet->load('customer');

        $query = $wallet->transactions()->with('relatedWallet')->latest();

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $transactions = $query->paginate($perPage);

        $stats = [
            'total_transactions' => $wallet->transactions()->count(),
            'completed' => $wallet->transactions()->completed()->count(),
            'pending' => $wallet->transactions()->pending()->count(),
            'failed' => $wallet->transactions()->failed()->count(),
            'total_credits' => $wallet->getTotalCredits(),
            'total_debits' => $wallet->getTotalDebits(),
        ];

        return [
            'wallet' => (new WalletSummaryResource($wallet))->resolve(),
            'transactions' => TransactionResource::collection($transactions)->response()->getData(true),
            'filters' => $filters,
            'stats' => $stats,
            'transactionTypes' => TransactionType::options(),
            'transactionStatuses' => TransactionStatus::options(),
        ];
    }
}
