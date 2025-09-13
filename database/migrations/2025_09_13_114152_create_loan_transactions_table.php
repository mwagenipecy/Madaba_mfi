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
        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->id();
            
            // Loan Reference
            $table->unsignedBigInteger('loan_id');
            $table->unsignedBigInteger('loan_schedule_id')->nullable(); // Reference to specific installment
            
            // Transaction Information
            $table->string('transaction_number')->unique(); // Auto-generated transaction number
            $table->enum('transaction_type', [
                'disbursement', 'principal_payment', 'interest_payment', 
                'late_fee', 'penalty_fee', 'processing_fee', 'insurance_fee',
                'refund', 'adjustment', 'write_off'
            ]);
            $table->decimal('amount', 15, 2); // Transaction amount
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'other'])->nullable();
            $table->string('reference_number')->nullable(); // External reference number
            $table->date('transaction_date'); // Date of transaction
            $table->time('transaction_time')->nullable(); // Time of transaction
            
            // Payment Allocation
            $table->decimal('principal_amount', 15, 2)->default(0.00); // Amount allocated to principal
            $table->decimal('interest_amount', 15, 2)->default(0.00); // Amount allocated to interest
            $table->decimal('fee_amount', 15, 2)->default(0.00); // Amount allocated to fees
            $table->decimal('penalty_amount', 15, 2)->default(0.00); // Amount allocated to penalties
            
            // Transaction Status
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable(); // Transaction notes
            $table->text('failure_reason')->nullable(); // Reason for failed transaction
            
            // System Information
            $table->unsignedBigInteger('processed_by'); // User who processed the transaction
            $table->unsignedBigInteger('organization_id'); // Organization
            $table->unsignedBigInteger('branch_id')->nullable(); // Branch where transaction was made
            
            $table->timestamps();
            
            // Foreign Keys and Indexes
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('loan_schedule_id')->references('id')->on('loan_schedules')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            
            $table->index(['loan_id', 'transaction_type']);
            $table->index(['transaction_date', 'organization_id']);
            $table->index(['status', 'transaction_type']);
            $table->index(['payment_method', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_transactions');
    }
};
