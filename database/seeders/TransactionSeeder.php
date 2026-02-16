<?php

namespace Modules\Wallets\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Wallets\Models\Wallet;
use Modules\Wallets\Models\Transaction;
use Modules\Wallets\Enums\TransactionType;
use Modules\Wallets\Enums\TransactionStatus;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wallets = Wallet::all();

        if ($wallets->isEmpty()) {
            $this->command->warn('No wallets found. Please run WalletSeeder first.');
            return;
        }

        $transactionCount = 0;

        foreach ($wallets as $wallet) {
            // Generate 5-15 transactions per wallet
            $numTransactions = fake()->numberBetween(5, 15);

            for ($i = 0; $i < $numTransactions; $i++) {
                $this->createTransaction($wallet);
                $transactionCount++;
            }
        }

        // Create some transfer transactions between wallets
        if ($wallets->count() >= 2) {
            $transferCount = fake()->numberBetween(5, 10);
            for ($i = 0; $i < $transferCount; $i++) {
                $this->createTransferTransaction($wallets);
                $transactionCount += 2; // Each transfer creates 2 transactions
            }
        }

        $this->command->info("Created {$transactionCount} transactions successfully.");
    }

    /**
     * Create a single transaction for a wallet.
     */
    protected function createTransaction(Wallet $wallet): void
    {
        $type = fake()->randomElement([
            TransactionType::DEPOSIT,
            TransactionType::WITHDRAWAL,
            TransactionType::PAYMENT,
            TransactionType::REFUND,
            TransactionType::FEE,
        ]);

        $status = $this->getWeightedStatus();
        $amount = fake()->randomFloat(2, 10, 500);
        $fee = $type === TransactionType::FEE ? 0 : fake()->randomFloat(2, 0, 5);

        $balanceBefore = (float) $wallet->balance;

        // Calculate balance after based on type
        if ($type->isCredit()) {
            $balanceAfter = $balanceBefore + $amount;
        } else {
            // For debits, make sure we don't go negative (for completed transactions)
            if ($status === TransactionStatus::COMPLETED && $balanceBefore < $amount) {
                $amount = max(10, $balanceBefore * 0.5); // Take at most 50% of balance
            }
            $balanceAfter = $balanceBefore - $amount;
        }

        $createdAt = fake()->dateTimeBetween('-6 months', 'now');
        $completedAt = null;
        $failedAt = null;
        $failureReason = null;

        if ($status === TransactionStatus::COMPLETED) {
            $completedAt = fake()->dateTimeBetween($createdAt, 'now');
        } elseif ($status === TransactionStatus::FAILED) {
            $failedAt = fake()->dateTimeBetween($createdAt, 'now');
            $failureReason = fake()->randomElement([
                'Insufficient funds',
                'Transaction limit exceeded',
                'Account suspended',
                'Network error',
                'Invalid payment method',
            ]);
        }

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'status' => $status,
            'amount' => $amount,
            'fee' => $fee,
            'balance_before' => $balanceBefore,
            'balance_after' => $status === TransactionStatus::COMPLETED ? $balanceAfter : $balanceBefore,
            'currency' => $wallet->currency,
            'description' => $this->getDescription($type),
            'external_reference' => fake()->optional(0.3)->uuid(),
            'payment_method' => fake()->optional(0.5)->randomElement(['card', 'bank_transfer', 'cash', 'mobile_money']),
            'completed_at' => $completedAt,
            'failed_at' => $failedAt,
            'failure_reason' => $failureReason,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        // Update wallet balance for completed transactions
        if ($status === TransactionStatus::COMPLETED) {
            $wallet->update(['balance' => $balanceAfter]);
        }
    }

    /**
     * Create a transfer transaction between two wallets.
     */
    protected function createTransferTransaction($wallets): void
    {
        $sourceWallet = $wallets->random();
        $destWallet = $wallets->where('id', '!=', $sourceWallet->id)->random();

        if (!$destWallet) {
            return;
        }

        $amount = fake()->randomFloat(2, 50, 500);
        $status = fake()->randomElement([
            TransactionStatus::COMPLETED,
            TransactionStatus::COMPLETED,
            TransactionStatus::COMPLETED,
            TransactionStatus::PENDING,
        ]);

        // Ensure source has enough balance for completed transfers
        if ($status === TransactionStatus::COMPLETED && $sourceWallet->balance < $amount) {
            $amount = max(10, (float) $sourceWallet->balance * 0.3);
        }

        $createdAt = fake()->dateTimeBetween('-3 months', 'now');
        $completedAt = $status === TransactionStatus::COMPLETED
            ? fake()->dateTimeBetween($createdAt, 'now')
            : null;

        $sourceBalanceBefore = (float) $sourceWallet->balance;
        $destBalanceBefore = (float) $destWallet->balance;

        DB::transaction(function () use (
            $sourceWallet, $destWallet, $amount, $status,
            $createdAt, $completedAt, $sourceBalanceBefore, $destBalanceBefore
        ) {
            // Source wallet: Transfer Out
            Transaction::create([
                'wallet_id' => $sourceWallet->id,
                'related_wallet_id' => $destWallet->id,
                'type' => TransactionType::TRANSFER_OUT,
                'status' => $status,
                'amount' => $amount,
                'fee' => 0,
                'balance_before' => $sourceBalanceBefore,
                'balance_after' => $status === TransactionStatus::COMPLETED
                    ? $sourceBalanceBefore - $amount
                    : $sourceBalanceBefore,
                'currency' => $sourceWallet->currency,
                'description' => "Transfer to wallet {$destWallet->wallet_number}",
                'completed_at' => $completedAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Destination wallet: Transfer In
            Transaction::create([
                'wallet_id' => $destWallet->id,
                'related_wallet_id' => $sourceWallet->id,
                'type' => TransactionType::TRANSFER_IN,
                'status' => $status,
                'amount' => $amount,
                'fee' => 0,
                'balance_before' => $destBalanceBefore,
                'balance_after' => $status === TransactionStatus::COMPLETED
                    ? $destBalanceBefore + $amount
                    : $destBalanceBefore,
                'currency' => $destWallet->currency,
                'description' => "Transfer from wallet {$sourceWallet->wallet_number}",
                'completed_at' => $completedAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Update wallet balances for completed transfers
            if ($status === TransactionStatus::COMPLETED) {
                $sourceWallet->update(['balance' => $sourceBalanceBefore - $amount]);
                $destWallet->update(['balance' => $destBalanceBefore + $amount]);
            }
        });
    }

    /**
     * Get weighted random status (more completed transactions).
     */
    protected function getWeightedStatus(): TransactionStatus
    {
        $rand = fake()->numberBetween(1, 100);

        return match (true) {
            $rand <= 70 => TransactionStatus::COMPLETED,  // 70%
            $rand <= 85 => TransactionStatus::PENDING,    // 15%
            $rand <= 95 => TransactionStatus::FAILED,     // 10%
            default => TransactionStatus::CANCELLED,      // 5%
        };
    }

    /**
     * Get transaction description based on type.
     */
    protected function getDescription(TransactionType $type): string
    {
        return match ($type) {
            TransactionType::DEPOSIT => fake()->randomElement([
                'Cash deposit',
                'Bank transfer deposit',
                'Top-up via mobile',
                'Salary deposit',
                'Refund credit',
            ]),
            TransactionType::WITHDRAWAL => fake()->randomElement([
                'ATM withdrawal',
                'Cash withdrawal',
                'Bank transfer',
                'Emergency withdrawal',
            ]),
            TransactionType::PAYMENT => fake()->randomElement([
                'Bill payment',
                'Subscription payment',
                'Service charge',
                'Purchase payment',
                'Utility payment',
            ]),
            TransactionType::REFUND => fake()->randomElement([
                'Order refund',
                'Service refund',
                'Overcharge refund',
                'Cancellation refund',
            ]),
            TransactionType::FEE => fake()->randomElement([
                'Monthly maintenance fee',
                'Transaction fee',
                'Service fee',
                'Processing fee',
            ]),
            default => 'Transaction',
        };
    }
}
