<?php

namespace Modules\Wallets\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Wallets\Actions\Dashboard\V1\GetAllTransactionsAction;
use Modules\Wallets\Actions\Dashboard\V1\GetWalletTransactionsAction;
use Modules\Wallets\Http\Requests\DepositRequest;
use Modules\Wallets\Http\Requests\WithdrawRequest;
use Modules\Wallets\Http\Requests\TransferRequest;
use Modules\Wallets\Http\Resources\Dashboard\V1\TransactionDetailResource;
use Modules\Wallets\Http\Resources\Dashboard\V1\WalletSummaryResource;
use Modules\Wallets\Models\Transaction;
use Modules\Wallets\Models\Wallet;

class TransactionController extends Controller
{
    public function __construct(
        protected GetAllTransactionsAction $getAllTransactionsAction,
        protected GetWalletTransactionsAction $getWalletTransactionsAction,
    ) {}

    /**
     * Display all transactions across all wallets.
     */
    public function all(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['type', 'status', 'wallet_id', 'date_from', 'date_to', 'search']);

        $data = $this->getAllTransactionsAction->execute($perPage, $filters);

        return Inertia::render('wallets::dashboard/v1/Transaction/All', $data);
    }

    /**
     * Display transactions list for a wallet.
     */
    public function index(Request $request, Wallet $wallet): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['type', 'status', 'date_from', 'date_to']);

        $data = $this->getWalletTransactionsAction->execute($wallet, $perPage, $filters);

        return Inertia::render('wallets::dashboard/v1/Transaction/Index', $data);
    }

    /**
     * Show transaction details.
     */
    public function show(Wallet $wallet, Transaction $transaction): Response
    {
        $transaction->load(['relatedWallet', 'reversedTransaction', 'reversalTransaction']);

        return Inertia::render('wallets::dashboard/v1/Transaction/Show', [
            'wallet' => (new WalletSummaryResource($wallet->load('customer')))->resolve(),
            'transaction' => (new TransactionDetailResource($transaction))->resolve(),
        ]);
    }

    /**
     * Show deposit form.
     */
    public function createDeposit(Wallet $wallet): Modal
    {
        return Inertia::modal('wallets::dashboard/v1/Transaction/Deposit', [
            'wallet' => (new WalletSummaryResource($wallet->load('customer')))->resolve(),
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
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show withdraw form.
     */
    public function createWithdraw(Wallet $wallet): Modal
    {
        return Inertia::modal('wallets::dashboard/v1/Transaction/Withdraw', [
            'wallet' => (new WalletSummaryResource($wallet->load('customer')))->resolve(),
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
            return redirect()->back()->with('error', $e->getMessage());
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

        return Inertia::modal('wallets::dashboard/v1/Transaction/Transfer', [
            'wallet' => (new WalletSummaryResource($wallet->load('customer')))->resolve(),
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

            $wallet->transferTo(
                destinationWallet: $destinationWallet,
                amount: $request->validated('amount'),
                description: $request->validated('description'),
            );

            return redirect()
                ->route('wallets.transactions.index', $wallet)
                ->with('success', "Transfer of {$wallet->currency} {$request->amount} to {$destinationWallet->wallet_number} completed.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
                return redirect()->back()->with('error', 'Transaction cannot be reversed.');
            }

            return redirect()
                ->route('wallets.transactions.index', $wallet)
                ->with('success', "Transaction reversed. Reversal ref: {$reversal->reference}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a pending transaction.
     */
    public function cancel(Wallet $wallet, Transaction $transaction): RedirectResponse
    {
        if (!$transaction->cancel()) {
            return redirect()->back()->with('error', 'Transaction cannot be cancelled.');
        }

        return redirect()
            ->route('wallets.transactions.index', $wallet)
            ->with('success', 'Transaction cancelled successfully.');
    }
}
