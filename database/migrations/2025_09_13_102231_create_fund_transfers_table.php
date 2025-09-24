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
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // Unique transfer number
            $table->unsignedBigInteger('from_account_id'); // Source account
            $table->unsignedBigInteger('to_account_id'); // Destination account
            $table->decimal('amount', 15, 2); // Transfer amount
            $table->string('currency', 3)->default('TZS'); // Currency
            $table->text('description'); // Transfer description
            $table->enum('status', ['pending', 'approved', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('requested_by'); // User who requested transfer
            $table->unsignedBigInteger('approved_by')->nullable(); // User who approved
            $table->timestamp('approved_at')->nullable(); // Approval timestamp
            $table->timestamp('completed_at')->nullable(); // Completion timestamp
            $table->text('rejection_reason')->nullable(); // Reason for rejection
            $table->json('metadata')->nullable(); // Additional transfer data
            $table->timestamps();

            $table->foreign('from_account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('to_account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'requested_by']);
            $table->index(['from_account_id', 'to_account_id']);
            $table->index('transfer_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
