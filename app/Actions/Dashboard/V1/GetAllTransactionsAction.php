<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use Modules\Wallets\Enums\TransactionStatus;
use Modules\Wallets\Enums\TransactionType;
use Modules\Wallets\Http\Resources\Dashboard\V1\TransactionResource;
use Modules\Wallets\Models\Transaction;
use Modules\Wallets\Models\Wallet;

class GetAllTransactionsAction
{
    /**
     * Get all transactions across all wallets with filters.
     */
    public function execute(int $perPage, array $filters): array
    {
        $query = Transaction::with(['wallet.customer', 'relatedWallet'])->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('wallet', fn ($q) => $q->where('wallet_number', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['wallet_id'])) {
            $query->where('wallet_id', $filters['wallet_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $transactions = $query->paginate($perPage);

        $stats = [
            'total_transactions' => Transaction::count(),
            'completed' => Transaction::completed()->count(),
            'pending' => Transaction::pending()->count(),
            'failed' => Transaction::failed()->count(),
            'total_credits' => (float) Transaction::completed()->credits()->sum('amount'),
            'total_debits' => (float) Transaction::completed()->debits()->sum('amount'),
        ];

        $wallets = Wallet::with('customer')
            ->orderBy('wallet_number')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'customer_name' => $w->customer?->name ?? 'N/A',
            ]);

        return [
            'transactions' => TransactionResource::collection($transactions)->response()->getData(true),
            'filters' => $filters,
            'stats' => $stats,
            'wallets' => $wallets,
            'transactionTypes' => TransactionType::options(),
            'transactionStatuses' => TransactionStatus::options(),
        ];
    }
}
