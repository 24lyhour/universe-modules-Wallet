<?php

namespace Modules\Wallets\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Wallets\Http\Resources\TransactionResource;
use Modules\Wallets\Http\Resources\WalletResource;
use Modules\Wallets\Models\Wallet;

class WalletController extends Controller
{
    /**
     * Get customer's wallet with balance.
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = $this->getOrCreateWallet($request);

        return response()->json([
            'data' => new WalletResource($wallet),
        ]);
    }

    /**
     * Get wallet balance only.
     */
    public function balance(Request $request): JsonResponse
    {
        $wallet = $this->getOrCreateWallet($request);

        return response()->json([
            'data' => [
                'balance' => (float) $wallet->balance,
                'available_balance' => (float) $wallet->available_balance,
                'currency' => $wallet->currency,
            ],
        ]);
    }

    /**
     * Get wallet transactions.
     */
    public function transactions(Request $request): JsonResponse
    {
        $wallet = $this->getCustomerWallet($request);

        if (!$wallet) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ]);
        }

        $perPage = $request->integer('per_page', 10);
        $type = $request->input('type');

        $query = $wallet->transactions()
            ->notReversed()
            ->latest();

        if ($type) {
            $query->byType($type);
        }

        $transactions = $query->paginate($perPage);

        return response()->json([
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Top up wallet (deposit).
     */
    public function topUp(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:10000'],
            'payment_method' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $wallet = $this->getOrCreateWallet($request);

        if (!$wallet->canTransact()) {
            return response()->json([
                'message' => 'Wallet is not active.',
            ], 422);
        }

        $transaction = $wallet->deposit(
            $request->float('amount'),
            $request->input('description', 'Top up'),
            [
                'payment_method' => $request->input('payment_method', 'manual'),
                'source' => 'app',
            ]
        );

        return response()->json([
            'message' => 'Top up successful.',
            'data' => [
                'transaction' => new TransactionResource($transaction),
                'wallet' => new WalletResource($wallet->fresh()),
            ],
        ]);
    }

    /**
     * Pay from wallet (deduct balance).
     */
    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'integer'],
        ]);

        $wallet = $this->getCustomerWallet($request);

        if (!$wallet || !$wallet->canTransact()) {
            return response()->json([
                'message' => 'Wallet is not active.',
            ], 422);
        }

        if ($wallet->available_balance < $request->float('amount')) {
            return response()->json([
                'message' => 'Insufficient balance.',
            ], 422);
        }

        $transaction = $wallet->pay(
            $request->float('amount'),
            $request->input('description', 'Payment'),
            [
                'order_id' => $request->input('order_id'),
                'source' => 'app',
            ]
        );

        return response()->json([
            'message' => 'Payment successful.',
            'data' => [
                'transaction' => new TransactionResource($transaction),
                'wallet' => new WalletResource($wallet->fresh()),
            ],
        ]);
    }

    /**
     * Transfer to another wallet.
     */
    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.50', 'max:5000'],
            'recipient_wallet_number' => ['required', 'string', 'exists:wallets,wallet_number'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $wallet = $this->getCustomerWallet($request);

        if (!$wallet || !$wallet->canTransact()) {
            return response()->json(['message' => 'Wallet is not active.'], 422);
        }

        if ($wallet->available_balance < $request->float('amount')) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        $recipient = Wallet::where('wallet_number', $request->recipient_wallet_number)->first();

        if ($recipient->id === $wallet->id) {
            return response()->json(['message' => 'Cannot transfer to yourself.'], 422);
        }

        $transactions = $wallet->transferTo(
            $recipient,
            $request->float('amount'),
            $request->input('description', 'Transfer')
        );

        return response()->json([
            'message' => 'Transfer successful.',
            'data' => [
                'transaction' => new TransactionResource($transactions['outgoing']),
                'wallet' => new WalletResource($wallet->fresh()),
            ],
        ]);
    }

    /**
     * Get or create wallet for customer.
     */
    private function getOrCreateWallet(Request $request): Wallet
    {
        $customer = $request->user();

        return Wallet::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'balance' => 0,
                'locked_amount' => 0,
                'currency' => 'USD',
                'status' => 'active',
            ]
        );
    }

    /**
     * Get customer's active wallet.
     */
    private function getCustomerWallet(Request $request): ?Wallet
    {
        return Wallet::where('customer_id', $request->user()->id)
            ->active()
            ->first();
    }
}
