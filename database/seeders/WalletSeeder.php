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
        $createdCount = 0;

        // Get wallet number settings
        $numberPrefix = \App\Models\Setting::getValue('wallet', 'number_prefix', 'WLT');
        $numberSeparator = \App\Models\Setting::getValue('wallet', 'number_separator', '-');
        $dateFormat = \App\Models\Setting::getValue('wallet', 'number_date_format', 'Ymd');
        $randomLength = (int) \App\Models\Setting::getValue('wallet', 'number_random_length', 5);

        foreach ($customers as $index => $customer) {
            // Check if customer already has a wallet
            if (Wallet::where('customer_id', $customer->id)->exists()) {
                continue;
            }

            // Generate wallet_number using settings pattern: WLT-20260216-12345
            $datePart = now()->format($dateFormat);
            $randomPart = str_pad(mt_rand(0, pow(10, $randomLength) - 1), $randomLength, '0', STR_PAD_LEFT);
            $walletNumber = $numberPrefix . $numberSeparator . $datePart . $numberSeparator . $randomPart;

            Wallet::create([
                'customer_id' => $customer->id,
                'wallet_number' => $walletNumber,
                'balance' => fake()->randomFloat(2, 0, 10000),
                'locked_amount' => fake()->randomFloat(2, 0, 500),
                'currency' => $currencies[array_rand($currencies)],
                'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
                'description' => fake()->optional(0.5)->sentence(),
            ]);

            $createdCount++;
        }

        $this->command->info("Wallets seeded successfully. Created {$createdCount} wallets.");
    }
}
