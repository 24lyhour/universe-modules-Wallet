<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add column without unique constraint
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('wallet_id', 20)->nullable()->after('id');
        });

        // Step 2: Generate wallet_id for existing records
        $wallets = \DB::table('wallets')->orderBy('id')->get();
        foreach ($wallets as $index => $wallet) {
            $walletId = 'W' . str_pad($wallet->id, 8, '0', STR_PAD_LEFT);
            \DB::table('wallets')->where('id', $wallet->id)->update(['wallet_id' => $walletId]);
        }

        // Step 3: Add unique constraint
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('wallet_id', 20)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('wallet_id');
        });
    }
};
