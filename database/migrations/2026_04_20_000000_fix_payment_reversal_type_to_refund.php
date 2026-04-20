<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix historical reversal transactions that were created with type = 'deposit'
 * when reversing a PAYMENT. Semantically those should be 'refund'.
 *
 * The Transaction::reverse() method has been updated to emit REFUND for
 * payment reversals going forward. This migration cleans up old rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE wallet_transactions AS reversal
            INNER JOIN wallet_transactions AS original
                ON original.id = reversal.reversed_transaction_id
            SET reversal.type = 'refund'
            WHERE reversal.reversed_transaction_id IS NOT NULL
              AND reversal.type = 'deposit'
              AND original.type = 'payment'
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            UPDATE wallet_transactions AS reversal
            INNER JOIN wallet_transactions AS original
                ON original.id = reversal.reversed_transaction_id
            SET reversal.type = 'deposit'
            WHERE reversal.reversed_transaction_id IS NOT NULL
              AND reversal.type = 'refund'
              AND original.type = 'payment'
        SQL);
    }
};
