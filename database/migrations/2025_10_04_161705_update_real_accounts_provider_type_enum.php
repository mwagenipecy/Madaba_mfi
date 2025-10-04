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
        Schema::table('real_accounts', function (Blueprint $table) {
            // Modify the provider_type enum to include 'other'
            $table->enum('provider_type', ['mno', 'bank', 'payment_gateway', 'other'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any 'other' values to 'payment_gateway'
        DB::statement("UPDATE real_accounts SET provider_type = 'payment_gateway' WHERE provider_type = 'other'");
        
        Schema::table('real_accounts', function (Blueprint $table) {
            $table->enum('provider_type', ['mno', 'bank', 'payment_gateway'])->change();
        });
    }
};
