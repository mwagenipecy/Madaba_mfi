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
        Schema::create('real_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id'); // Foreign key to accounts table
            $table->enum('provider_type', ['mno', 'bank', 'payment_gateway']); // MNO, Bank, or Payment Gateway
            $table->string('provider_name'); // e.g., "MTN", "Vodafone", "Standard Bank"
            $table->string('external_account_id'); // External system account ID
            $table->string('external_account_name')->nullable(); // External account name
            $table->string('api_endpoint')->nullable(); // API endpoint for balance checking
            $table->json('api_credentials')->nullable(); // Encrypted API credentials
            $table->decimal('last_balance', 15, 2)->default(0.00); // Last synced balance
            $table->timestamp('last_sync_at')->nullable(); // Last balance sync timestamp
            $table->enum('sync_status', ['pending', 'success', 'failed', 'disabled'])->default('pending');
            $table->text('sync_error_message')->nullable(); // Last sync error
            $table->json('provider_metadata')->nullable(); // Additional provider-specific data
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'provider_type']);
            $table->index(['provider_type', 'is_active']);
            $table->index('external_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_accounts');
    }
};
