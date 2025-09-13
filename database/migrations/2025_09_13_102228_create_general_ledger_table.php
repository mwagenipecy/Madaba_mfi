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
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Unique transaction identifier
            $table->date('transaction_date'); // Transaction date
            $table->unsignedBigInteger('account_id'); // Account affected
            $table->enum('transaction_type', ['debit', 'credit']); // Debit or Credit
            $table->decimal('amount', 15, 2); // Transaction amount
            $table->string('currency', 3)->default('USD'); // Currency code
            $table->text('description'); // Transaction description
            $table->string('reference_type')->nullable(); // Type of reference (fund_transfer, recharge, etc.)
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference record
            $table->unsignedBigInteger('created_by'); // User who created the transaction
            $table->unsignedBigInteger('approved_by')->nullable(); // User who approved (if applicable)
            $table->timestamp('approved_at')->nullable(); // Approval timestamp
            $table->decimal('balance_after', 15, 2); // Account balance after transaction
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['transaction_date', 'account_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['created_by', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger');
    }
};
