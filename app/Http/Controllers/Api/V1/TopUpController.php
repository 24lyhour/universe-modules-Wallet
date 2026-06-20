<?php

namespace Modules\Wallets\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Wallets\Actions\Api\V1\CreateTopUpAction;
use Modules\Wallets\Http\Requests\TopUpRequest;
use Modules\Wallets\Http\Resources\TopUpResource;
use Modules\Wallets\Http\Resources\WalletResource;
use Modules\Wallets\Models\TopUp;
use Modules\Wallets\Models\Wallet;

class TopUpController extends Controller
{
    public function __construct(
        protected CreateTopUpAction $createTopUpAction
    ) {}

    /**
     * List the authenticated customer's top-ups.
     */
    public function index(Request $request): JsonResponse
    {
        $wallet = $this->getCustomerWallet($request);

        if (!$wallet) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ]);
        }

        $perPage = $request->integer('per_page', 10);

        $topups = TopUp::byWallet($wallet->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => TopUpResource::collection($topups),
            'meta' => [
                'current_page' => $topups->currentPage(),
                'last_page' => $topups->lastPage(),
                'per_page' => $topups->perPage(),
                'total' => $topups->total(),
            ],
        ]);
    }

    /**
     * Create a top-up. Manual top-ups credit the wallet immediately;
     * gateway top-ups stay pending until the provider callback completes them.
     */
    public function store(TopUpRequest $request): JsonResponse
    {
        $wallet = $this->getOrCreateWallet($request);

        if (!$wallet->canTransact()) {
            return response()->json(['message' => 'Wallet is not active.'], 422);
        }

        $topup = $this->createTopUpAction->execute($wallet, $request->validated());

        return response()->json([
            'message' => 'Top-up created.',
            'data' => [
                'topup' => new TopUpResource($topup),
                'wallet' => new WalletResource($wallet->fresh()),
            ],
        ], 201);
    }

    /**
     * Get a single top-up by reference.
     */
    public function show(Request $request, string $reference): JsonResponse
    {
        $wallet = $this->getCustomerWallet($request);

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found.'], 404);
        }

        $topup = TopUp::byWallet($wallet->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return response()->json([
            'data' => new TopUpResource($topup),
        ]);
    }

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

    private function getCustomerWallet(Request $request): ?Wallet
    {
        return Wallet::where('customer_id', $request->user()->id)
            ->active()
            ->first();
    }
}
