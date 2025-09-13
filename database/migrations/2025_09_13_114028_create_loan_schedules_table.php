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
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            
            // Loan Reference
            $table->unsignedBigInteger('loan_id');
            $table->integer('installment_number'); // Installment number (1, 2, 3, etc.)
            
            // Payment Schedule
            $table->date('due_date'); // Due date for this installment
            $table->decimal('principal_amount', 15, 2); // Principal amount for this installment
            $table->decimal('interest_amount', 15, 2); // Interest amount for this installment
            $table->decimal('total_amount', 15, 2); // Total amount due for this installment
            
            // Payment Status
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial', 'waived'])->default('pending');
            $table->decimal('paid_amount', 15, 2)->default(0.00); // Amount paid for this installment
            $table->decimal('outstanding_amount', 15, 2); // Outstanding amount for this installment
            $table->date('paid_date')->nullable(); // Date when this installment was paid
            
            // Late Payment Information
            $table->integer('days_overdue')->default(0); // Days overdue for this installment
            $table->decimal('late_fee', 15, 2)->default(0.00); // Late fee for this installment
            $table->decimal('penalty_fee', 15, 2)->default(0.00); // Penalty fee for this installment
            
            // Additional Information
            $table->text('notes')->nullable(); // Notes for this installment
            $table->timestamps();
            
            // Foreign Keys and Indexes
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->index(['loan_id', 'installment_number']);
            $table->index(['due_date', 'status']);
            $table->index(['status', 'days_overdue']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_schedules');
    }
};
