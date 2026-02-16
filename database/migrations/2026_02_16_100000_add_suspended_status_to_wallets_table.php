<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum
        DB::statement("ALTER TABLE wallets MODIFY COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any suspended to inactive
        DB::table('wallets')->where('status', 'suspended')->update(['status' => 'inactive']);

        // Then modify the enum back
        DB::statement("ALTER TABLE wallets MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }
};
