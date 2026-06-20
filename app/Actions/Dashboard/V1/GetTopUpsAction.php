<?php

namespace Modules\Wallets\Actions\Dashboard\V1;

use Modules\Wallets\Enums\TopUpStatus;
use Modules\Wallets\Http\Resources\TopUpResource;
use Modules\Wallets\Models\TopUp;
use Modules\Wallets\Models\Wallet;

class GetTopUpsAction
{
    /**
     * Get paginated top-ups with filters + stats + wallet picker data.
     */
    public function execute(int $perPage, array $filters): array
    {
        $query = TopUp::with(['wallet.customer'])->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('gateway_reference', 'like', "%{$search}%")
                  ->orWhereHas('wallet', fn ($q) => $q->where('wallet_number', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['wallet_id'])) {
            $query->where('wallet_id', $filters['wallet_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $topups = $query->paginate($perPage);

        $stats = [
            'total' => TopUp::count(),
            'pending' => TopUp::pending()->count(),
            'completed' => TopUp::completed()->count(),
            'failed' => TopUp::failed()->count(),
            'total_completed_amount' => (float) TopUp::completed()->sum('amount'),
            'total_pending_amount' => (float) TopUp::pending()->sum('amount'),
        ];

        $wallets = Wallet::with('customer')
            ->orderBy('wallet_number')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'customer_name' => $w->customer?->name ?? 'N/A',
                'currency' => $w->currency,
            ]);

        return [
            'topups' => TopUpResource::collection($topups)->response()->getData(true),
            'filters' => $filters,
            'stats' => $stats,
            'wallets' => $wallets,
            'topupStatuses' => TopUpStatus::options(),
        ];
    }
}
