<?php

namespace Modules\Wallets\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletSettingsController extends Controller
{
    /**
     * Default wallet settings.
     */
    protected array $defaults = [
        'id_prefix' => 'W',
        'id_padding' => 8,
        'number_prefix' => 'WLT',
        'number_separator' => '-',
        'number_date_format' => 'Ymd',
        'number_random_length' => 5,
        'default_currency' => 'USD',
    ];

    /**
     * Display wallet settings page.
     */
    public function index(): Response
    {
        $walletSettings = Setting::getGroup('wallet');

        // Merge with defaults for any missing settings
        $walletSettings = array_merge($this->defaults, $walletSettings);

        return Inertia::render('wallets::settings/Index', [
            'walletSettings' => $walletSettings,
        ]);
    }

    /**
     * Update wallet settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_prefix' => 'required|string|max:5',
            'id_padding' => 'required|integer|min:4|max:12',
            'number_prefix' => 'required|string|max:10',
            'number_separator' => 'required|string|max:1',
            'number_date_format' => 'required|string|max:20',
            'number_random_length' => 'required|integer|min:3|max:10',
            'default_currency' => 'required|string|size:3',
        ]);

        Setting::setValue('wallet', 'id_prefix', $validated['id_prefix'], 'string');
        Setting::setValue('wallet', 'id_padding', $validated['id_padding'], 'integer');
        Setting::setValue('wallet', 'number_prefix', $validated['number_prefix'], 'string');
        Setting::setValue('wallet', 'number_separator', $validated['number_separator'], 'string');
        Setting::setValue('wallet', 'number_date_format', $validated['number_date_format'], 'string');
        Setting::setValue('wallet', 'number_random_length', $validated['number_random_length'], 'integer');
        Setting::setValue('wallet', 'default_currency', $validated['default_currency'], 'string');

        return back()->with('success', 'Wallet settings updated successfully.');
    }
}
