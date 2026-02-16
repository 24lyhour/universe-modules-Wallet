<?php

namespace Modules\Wallets\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Wallets\Models\Wallet;
use Modules\Wallets\Http\Requests\StoreWalletRequest;
use Modules\Wallets\Http\Requests\UpdateWalletRequest;
use Modules\Customer\Models\Customer;

class WalletController extends Controller
{
    /**
     * Display a listing of wallets.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $query = Wallet::with('customer')->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('wallet_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $wallets = $query->paginate($perPage);

        $stats = [
            'total' => Wallet::count(),
            'active' => Wallet::where('status', 'active')->count(),
            'inactive' => Wallet::where('status', 'inactive')->count(),
            'total_balance' => Wallet::sum('balance'),
            'total_locked' => Wallet::sum('locked_amount'),
        ];

        return Inertia::render('wallets::dashboard/v1/wallet/Index', [
            'walletItems' => [
                'data' => $wallets->items(),
                'meta' => [
                    'current_page' => $wallets->currentPage(),
                    'last_page' => $wallets->lastPage(),
                    'per_page' => $wallets->perPage(),
                    'total' => $wallets->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form for creating a new wallet.
     */
    public function create(): Modal
    {
        $customers = Customer::select('id', 'name', 'email')
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::modal('wallets::dashboard/v1/wallet/Create', [
            'customers' => $customers,
        ])->baseRoute('wallets.index');
    }

    /**
     * Store a new wallet.
     */
    public function store(StoreWalletRequest $request): RedirectResponse
    {
        Wallet::create($request->validated());

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Wallet created successfully.');
    }

    /**
     * Display a specific wallet.
     */
    public function show(Wallet $wallet): Response
    {
        return Inertia::render('wallets::dashboard/v1/wallet/Show', [
            'wallet' => $wallet->load('customer'),
        ]);
    }

    /**
     * Show form for editing a wallet.
     */
    public function edit(Wallet $wallet): Modal
    {
        $customers = Customer::select('id', 'name', 'email')
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::modal('wallets::dashboard/v1/wallet/Edit', [
            'wallet' => $wallet->load('customer'),
            'customers' => $customers,
        ])->baseRoute('wallets.index');
    }

    /**
     * Update a wallet.
     */
    public function update(UpdateWalletRequest $request, Wallet $wallet): RedirectResponse
    {
        $wallet->update($request->validated());

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Wallet updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(Wallet $wallet): Modal
    {
        return Inertia::modal('wallets::dashboard/v1/wallet/Delete', [
            'wallet' => $wallet->load('customer'),
        ])->baseRoute('wallets.index');
    }

    /**
     * Delete a wallet.
     */
    public function destroy(Wallet $wallet): RedirectResponse
    {
        $wallet->delete();

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Wallet deleted successfully.');
    }

    /**
     * Toggle wallet status.
     */
    public function toggleStatus(Request $request, Wallet $wallet): RedirectResponse
    {
        $wallet->update([
            'status' => $request->boolean('status') ? 'active' : 'inactive',
        ]);

        return redirect()->back();
    }
}
