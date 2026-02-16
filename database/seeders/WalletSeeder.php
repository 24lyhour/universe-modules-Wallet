<?php

namespace Modules\Wallets\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Wallets\Models\Wallet;
use Modules\Customer\Models\Customer;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all customers
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        $currencies = ['USD', 'EUR', 'GBP', 'KHR'];

        foreach ($customers as $index => $customer) {
            // Check if customer already has a wallet
            if (Wallet::where('customer_id', $customer->id)->exists()) {
                continue;
            }

            Wallet::create([
                'customer_id' => $customer->id,
                'wallet_number' => 'WAL-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
                'balance' => fake()->randomFloat(2, 0, 10000),
                'locked_amount' => fake()->randomFloat(2, 0, 500),
                'currency' => $currencies[array_rand($currencies)],
                'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
                'description' => fake()->optional(0.5)->sentence(),
            ]);
        }

        $this->command->info('Wallets seeded successfully for ' . $customers->count() . ' customers.');
    }
}
