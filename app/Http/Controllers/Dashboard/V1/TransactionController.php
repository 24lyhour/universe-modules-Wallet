<?php

namespace Modules\Wallets\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Wallets\Models\Wallet;
use Modules\Wallets\Models\Transaction;
use Modules\Wallets\Enums\TransactionType;
use Modules\Wallets\Enums\TransactionStatus;
use Modules\Wallets\Http\Requests\DepositRequest;
use Modules\Wallets\Http\Requests\WithdrawRequest;
use Modules\Wallets\Http\Requests\TransferRequest;

class TransactionController extends Controller
{
    /**
     * Display all transactions across all wallets.
     */
    public function all(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['type', 'status', 'wallet_id', 'date_from', 'date_to', 'search']);

        $query = Transaction::with(['wallet.customer', 'relatedWallet'])->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('wallet', function ($q) use ($search) {
                      $q->where('wallet_number', 'like', "%{$search}%");
                  });
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

        // Transform transactions for frontend
        $transformedData = $transactions->through(function ($transaction) {
            return [
                'id' => $transaction->id,
                'reference' => $transaction->reference,
                'wallet_id' => $transaction->wallet_id,
                'wallet_number' => $transaction->wallet?->wallet_number,
                'customer_name' => $transaction->wallet?->customer?->name ?? 'N/A',
                'type' => $transaction->type->value,
                'type_label' => $transaction->type->label(),
                'type_color' => $transaction->type->color(),
                'status' => $transaction->status->value,
                'status_label' => $transaction->status->label(),
                'status_variant' => $transaction->status->variant(),
                'amount' => (float) $transaction->amount,
                'fee' => (float) $transaction->fee,
                'net_amount' => $transaction->net_amount,
                'signed_amount' => $transaction->signed_amount,
                'balance_before' => (float) $transaction->balance_before,
                'balance_after' => (float) $transaction->balance_after,
                'currency' => $transaction->currency,
                'description' => $transaction->description,
                'is_credit' => $transaction->is_credit,
                'is_debit' => $transaction->is_debit,
                'is_reversed' => $transaction->is_reversed,
                'related_wallet' => $transaction->relatedWallet ? [
                    'id' => $transaction->relatedWallet->id,
                    'wallet_number' => $transaction->relatedWallet->wallet_number,
                ] : null,
                'created_at' => $transaction->created_at->toISOString(),
                'completed_at' => $transaction->completed_at?->toISOString(),
            ];
        });

        $stats = [
            'total_transactions' => Transaction::count(),
            'completed' => Transaction::completed()->count(),
            'pending' => Transaction::pending()->count(),
            'failed' => Transaction::failed()->count(),
            'total_credits' => (float) Transaction::completed()->credits()->sum('amount'),
            'total_debits' => (float) Transaction::completed()->debits()->sum('amount'),
        ];

        // Get wallets for filter dropdown
        $wallets = Wallet::with('customer')
            ->orderBy('wallet_number')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'customer_name' => $w->customer?->name ?? 'N/A',
            ]);

        return Inertia::render('wallets::dashboard/v1/transaction/All', [
            'transactions' => [
                'data' => $transformedData->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
            'wallets' => $wallets,
            'transactionTypes' => TransactionType::options(),
            'transactionStatuses' => TransactionStatus::options(),
        ]);
    }

    /**
     * Display transactions list for a wallet.
     */
    public function index(Request $request, Wallet $wallet): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['type', 'status', 'date_from', 'date_to']);

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

        // Transform transactions for frontend
        $transformedData = $transactions->through(function ($transaction) {
            return [
                'id' => $transaction->id,
                'reference' => $transaction->reference,
                'type' => $transaction->type->value,
                'type_label' => $transaction->type->label(),
                'type_color' => $transaction->type->color(),
                'status' => $transaction->status->value,
                'status_label' => $transaction->status->label(),
                'status_variant' => $transaction->status->variant(),
                'amount' => (float) $transaction->amount,
                'fee' => (float) $transaction->fee,
                'net_amount' => $transaction->net_amount,
                'signed_amount' => $transaction->signed_amount,
                'balance_before' => (float) $transaction->balance_before,
                'balance_after' => (float) $transaction->balance_after,
                'currency' => $transaction->currency,
                'description' => $transaction->description,
                'external_reference' => $transaction->external_reference,
                'payment_method' => $transaction->payment_method,
                'is_credit' => $transaction->is_credit,
                'is_debit' => $transaction->is_debit,
                'is_reversed' => $transaction->is_reversed,
                'related_wallet' => $transaction->relatedWallet ? [
                    'id' => $transaction->relatedWallet->id,
                    'wallet_number' => $transaction->relatedWallet->wallet_number,
                ] : null,
                'created_at' => $transaction->created_at->toISOString(),
                'completed_at' => $transaction->completed_at?->toISOString(),
            ];
        });

        $stats = [
            'total_transactions' => $wallet->transactions()->count(),
            'completed' => $wallet->transactions()->completed()->count(),
            'pending' => $wallet->transactions()->pending()->count(),
            'failed' => $wallet->transactions()->failed()->count(),
            'total_credits' => $wallet->getTotalCredits(),
            'total_debits' => $wallet->getTotalDebits(),
        ];

        return Inertia::render('wallets::dashboard/v1/transaction/Index', [
            'wallet' => [
                'id' => $wallet->id,
                'wallet_number' => $wallet->wallet_number,
                'balance' => (float) $wallet->balance,
                'locked_amount' => (float) $wallet->locked_amount,
                'available_balance' => $wallet->available_balance,
                'currency' => $wallet->currency,
                'status' => $wallet->status,
                'customer' => $wallet->customer ? [
                    'id' => $wallet->customer->id,
                    'name' => $wallet->customer->name,
                ] : null,
            ],
            'transactions' => [
                'data' => $transformedData->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
            'transactionTypes' => TransactionType::options(),
            'transactionStatuses' => TransactionStatus::options(),
        ]);
    }

    /**
     * Show transaction details.
     */
    public function show(Wallet $wallet, Transaction $transaction): Response
    {
        $transaction->load(['relatedWallet', 'reversedTransaction', 'reversalTransaction']);

        return Inertia::render('wallets::dashboard/v1/transaction/Show', [
            'wallet' => [
                'id' => $wallet->id,
                'wallet_number' => $wallet->wallet_number,
            ],
            'transaction' => [
                'id' => $transaction->id,
                'reference' => $transaction->reference,
                'type' => $transaction->type->value,
                'type_label' => $transaction->type->label(),
                'type_color' => $transaction->type->color(),
                'status' => $transaction->status->value,
                'status_label' => $transaction->status->label(),
                'status_variant' => $transaction->status->variant(),
                'amount' => (float) $transaction->amount,
                'fee' => (float) $transaction->fee,
                'net_amount' => $transaction->net_amount,
                'signed_amount' => $transaction->signed_amount,
                'balance_before' => (float) $transaction->balance_before,
                'balance_after' => (float) $transaction->balance_after,
                'currency' => $transaction->currency,
                'description' => $transaction->description,
                'external_reference' => $transaction->external_reference,
                'payment_method' => $transaction->payment_method,
                'metadata' => $transaction->metadata,
                'is_credit' => $transaction->is_credit,
                'is_debit' => $transaction->is_debit,
                'is_reversed' => $transaction->is_reversed,
                'is_final' => $transaction->is_final,
                'can_reverse' => $transaction->status->canReverse() && !$transaction->is_reversed,
                'can_cancel' => $transaction->status->canCancel(),
                'related_wallet' => $transaction->relatedWallet ? [
                    'id' => $transaction->relatedWallet->id,
                    'wallet_number' => $transaction->relatedWallet->wallet_number,
                ] : null,
                'reversed_transaction' => $transaction->reversedTransaction ? [
                    'id' => $transaction->reversedTransaction->id,
                    'reference' => $transaction->reversedTransaction->reference,
                ] : null,
                'reversal_transaction' => $transaction->reversalTransaction ? [
                    'id' => $transaction->reversalTransaction->id,
                    'reference' => $transaction->reversalTransaction->reference,
                ] : null,
                'processed_at' => $transaction->processed_at?->toISOString(),
                'completed_at' => $transaction->completed_at?->toISOString(),
                'failed_at' => $transaction->failed_at?->toISOString(),
                'failure_reason' => $transaction->failure_reason,
                'reversed_at' => $transaction->reversed_at?->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
                'updated_at' => $transaction->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Show deposit form.
     */
    public function createDeposit(Wallet $wallet): Modal
    {
        return Inertia::modal('wallets::dashboard/v1/transaction/Deposit', [
            'wallet' => [
                'id' => $wallet->id,
                'wallet_number' => $wallet->wallet_number,
                'balance' => (float) $wallet->balance,
                'currency' => $wallet->currency,
                'status' => $wallet->status,
                'can_transact' => $wallet->canTransact(),
            ],
        ])->baseRoute('wallets.transactions.index', $wallet);
    }

    /**
     * Process deposit.
     */
    public function deposit(DepositRequest $request, Wallet $wallet): RedirectResponse
    {
        try {
            $transaction = $wallet->deposit(
                amount: $request->validated('amount'),
                description: $request->validated('description'),
                externalReference: $request->validated('external_reference'),
                paymentMethod: $request->validated('payment_method'),
            );

            return redirect()
                ->route('wallets.transactions.index', $wallet)
                ->with('success', "Deposit of {$wallet->currency} {$request->amount} completed. Ref: {$transaction->reference}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show withdraw form.
     */
    public function createWithdraw(Wallet $wallet): Modal
    {
        return Inertia::modal('wallets::dashboard/v1/transaction/Withdraw', [
            'wallet' => [
                'id' => $wallet->id,
                'wallet_number' => $wallet->wallet_number,
                'balance' => (float) $wallet->balance,
                'available_balance' => $wallet->available_balance,
                'currency' => $wallet->currency,
                'status' => $wallet->status,
                'can_transact' => $wallet->canTransact(),
            ],
        ])->baseRoute('wallets.transactions.index', $wallet);
    }

    /**
     * Process withdrawal.
     */
    public function withdraw(WithdrawRequest $request, Wallet $wallet): RedirectResponse
    {
        try {
            $transaction = $wallet->withdraw(
                amount: $request->validated('amount'),
                description: $request->validated('description'),
                externalReference: $request->validated('external_reference'),
            );

            return redirect()
                ->route('wallets.transactions.index', $wallet)
                ->with('success', "Withdrawal of {$wallet->currency} {$request->amount} completed. Ref: {$transaction->reference}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show transfer form.
     */
    public function createTransfer(Wallet $wallet): Modal
    {
        $availableWallets = Wallet::where('id', '!=', $wallet->id)
            ->active()
            ->with('customer')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'customer_name' => $w->customer?->name ?? 'N/A',
                'currency' => $w->currency,
            ]);

        return Inertia::modal('wallets::dashboard/v1/transaction/Transfer', [
            'wallet' => [
                'id' => $wallet->id,
                'wallet_number' => $wallet->wallet_number,
                'balance' => (float) $wallet->balance,
                'available_balance' => $wallet->available_balance,
                'currency' => $wallet->currency,
                'status' => $wallet->status,
                'can_transact' => $wallet->canTransact(),
            ],
            'availableWallets' => $availableWallets,
        ])->baseRoute('wallets.transactions.index', $wallet);
    }

    /**
     * Process transfer.
     */
    public function transfer(TransferRequest $request, Wallet $wallet): RedirectResponse
    {
        try {
            $destinationWallet = Wallet::findOrFail($request->validated('destination_wallet_id'));

            $transactions = $wallet->transferTo(
                destinationWallet: $destinationWallet,
                amount: $request->validated('amount'),
                description: $request->validated('description'),
            );

            return redirect()
                ->route('wallets.transactions.index', $wallet)
                ->with('success', "Transfer of {$wallet->currency} {$request->amount} to {$destinationWallet->wallet_number} completed.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reverse a transaction.
     */
    public function reverse(Request $request, Wallet $wallet, Transaction $transaction): RedirectResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $reversal = $transaction->reverse($request->input('description'));

            if (!$reversal) {
                return redirect()
                    ->back()
                    ->with('error', 'Transaction cannot be reversed.');
            }

            return redirect()
                ->route('wallets.transactions.index', $wallet)
                ->with('success', "Transaction reversed. Reversal ref: {$reversal->reference}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a pending transaction.
     */
    public function cancel(Wallet $wallet, Transaction $transaction): RedirectResponse
    {
        if (!$transaction->cancel()) {
            return redirect()
                ->back()
                ->with('error', 'Transaction cannot be cancelled.');
        }

        return redirect()
            ->route('wallets.transactions.index', $wallet)
            ->with('success', 'Transaction cancelled successfully.');
    }
}
