<?php

namespace Modules\Wallets\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;

class DashboardMiddlewareHandle
{
    protected static bool $registered = false;

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('dashboard', 'dashboard/*')) {
            $this->registerMenuItems();
        }

        return $next($request);
    }

    protected function registerMenuItems(): void
    {
        if (static::$registered) {
            return;
        }

        MenuService::addMenuItem(
            menu: 'primary',
            id: 'wallets',
            title: __('Wallets'),
            url: route('wallets.index'),
            icon: 'Wallet',
            order: 50,
            permissions: 'wallets.view_any',
            route: 'wallets.*'
        );

        MenuService::addSubmenuItem('primary', 'wallets', __('Wallets'), route('wallets.index'), 10, 'wallets.view_any', 'wallets.index', 'Wallet');
        MenuService::addSubmenuItem('primary', 'wallets', __('Transactions'), route('transactions.index'), 20, 'transactions.view_any', 'transactions.*', 'ArrowLeftRight');

        static::$registered = true;
    }
}
