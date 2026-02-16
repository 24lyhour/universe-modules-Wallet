<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('related_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();

            // Transaction type enum
            $table->enum('type', [
                'deposit',
                'withdrawal',
                'transfer_in',
                'transfer_out',
                'payment',
                'refund',
                'fee',
                'adjustment',
            ]);

            // Transaction status enum
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'reversed',
            ])->default('pending');

            // Amounts
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('currency', 3)->default('USD');

            // Metadata
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();

            // Reference to external systems
            $table->string('external_reference')->nullable();
            $table->string('payment_method')->nullable();

            // Processing info
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();

            // Reversal tracking
            $table->foreignId('reversed_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['wallet_id', 'created_at']);
            $table->index(['wallet_id', 'type']);
            $table->index(['wallet_id', 'status']);
            $table->index('reference');
            $table->index('external_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
