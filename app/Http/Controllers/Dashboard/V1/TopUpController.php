<?php

namespace Modules\Wallets\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Wallets\Actions\Dashboard\V1\CreateTopUpAction;
use Modules\Wallets\Actions\Dashboard\V1\GetTopUpsAction;
use Modules\Wallets\Enums\TopUpStatus;
use Modules\Wallets\Http\Requests\TopUpRequest;
use Modules\Wallets\Http\Resources\TopUpResource;
use Modules\Wallets\Models\TopUp;
use Modules\Wallets\Models\Wallet;

class TopUpController extends Controller
{
    public function __construct(
        protected CreateTopUpAction $createTopUpAction,
        protected GetTopUpsAction $getTopUpsAction,
    ) {}

    /**
     * Display a listing of top-ups.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $filters = $request->only(['search', 'status', 'wallet_id', 'payment_method', 'date_from', 'date_to']);

        $data = $this->getTopUpsAction->execute($perPage, $filters);

        return Inertia::render('wallets::dashboard/v1/topup/Index', $data);
    }

    /**
     * Show form for creating a new top-up.
     */
    public function create(): Modal
    {
        $wallets = Wallet::with('customer')
            ->active()
            ->orderBy('wallet_number')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'customer_name' => $w->customer?->name ?? 'N/A',
                'currency' => $w->currency,
            ]);

        return Inertia::modal('wallets::dashboard/v1/topup/Create', [
            'wallets' => $wallets,
            'topupStatuses' => TopUpStatus::options(),
        ])->baseRoute('topups.index');
    }

    /**
     * Store a new top-up.
     */
    public function store(TopUpRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $wallet = Wallet::findOrFail($request->integer('wallet_id'));

        if (!$wallet->canTransact()) {
            return redirect()->back()->with('error', 'Wallet is not active.');
        }

        $topup = $this->createTopUpAction->execute($wallet, $data);

        $msg = $topup->status === TopUpStatus::COMPLETED
            ? "Top-up {$topup->reference} completed. Wallet credited."
            : "Top-up {$topup->reference} created (pending).";

        return redirect()
            ->route('topups.index')
            ->with('success', $msg);
    }

    /**
     * Display a specific top-up.
     */
    public function show(TopUp $topup): Response
    {
        $topup->load(['wallet.customer', 'transaction', 'creator']);

        return Inertia::render('wallets::dashboard/v1/topup/Show', [
            'topup' => (new TopUpResource($topup))->resolve(),
            'wallet' => [
                'id' => $topup->wallet->id,
                'wallet_number' => $topup->wallet->wallet_number,
                'balance' => (float) $topup->wallet->balance,
                'currency' => $topup->wallet->currency,
                'customer_name' => $topup->wallet->customer?->name ?? 'N/A',
            ],
            'transaction' => $topup->transaction ? [
                'id' => $topup->transaction->id,
                'reference' => $topup->transaction->reference,
                'amount' => (float) $topup->transaction->amount,
                'balance_after' => (float) $topup->transaction->balance_after,
                'completed_at' => $topup->transaction->completed_at?->toISOString(),
            ] : null,
            'creator' => $topup->creator ? [
                'id' => $topup->creator->id,
                'name' => $topup->creator->name,
            ] : null,
        ]);
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(TopUp $topup): Modal
    {
        $topup->load('wallet');

        return Inertia::modal('wallets::dashboard/v1/topup/Delete', [
            'topup' => (new TopUpResource($topup))->resolve(),
            'wallet_number' => $topup->wallet->wallet_number,
        ])->baseRoute('topups.index');
    }

    /**
     * Delete a top-up. Only allowed for non-completed records to preserve the audit trail.
     */
    public function destroy(TopUp $topup): RedirectResponse
    {
        if ($topup->status === TopUpStatus::COMPLETED) {
            return redirect()->back()->with('error', 'Cannot delete a completed top-up. Reverse the wallet transaction instead.');
        }

        $topup->delete();

        return redirect()
            ->route('topups.index')
            ->with('success', 'Top-up deleted.');
    }

    /**
     * Manually complete a pending top-up.
     */
    public function complete(TopUp $topup): RedirectResponse
    {
        $transaction = $topup->complete();

        if (!$transaction) {
            return redirect()->back()->with('error', 'Top-up cannot be completed in its current state.');
        }

        return redirect()
            ->back()
            ->with('success', "Top-up completed. Wallet credited. Txn: {$transaction->reference}");
    }

    /**
     * Cancel a pending or processing top-up.
     */
    public function cancel(TopUp $topup): RedirectResponse
    {
        if (!$topup->cancel()) {
            return redirect()->back()->with('error', 'Top-up cannot be cancelled.');
        }

        return redirect()->back()->with('success', 'Top-up cancelled.');
    }

    /**
     * Mark a top-up as failed.
     */
    public function markFailed(Request $request, TopUp $topup): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (!$topup->markAsFailed($request->input('reason'))) {
            return redirect()->back()->with('error', 'Top-up cannot be marked as failed.');
        }

        return redirect()->back()->with('success', 'Top-up marked as failed.');
    }
}
