<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Dedicated table for repayment transactions recorded via the repayments page;
     * stores who recorded each transaction (recorded_by).
     */
    public function up(): void
    {
        Schema::create('repayment_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('loan_transaction_id')->nullable(); // Link to loan_transactions
            $table->string('transaction_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('principal_amount', 15, 2)->default(0);
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->string('payment_method', 50)->nullable(); // cash, bank_transfer, mobile_money, cheque, other
            $table->date('payment_date');
            $table->unsignedBigInteger('recorded_by'); // User who recorded the transaction
            $table->unsignedBigInteger('collection_account_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_type', 50)->default('loan_repayment'); // loan_repayment, charge_payment
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('loan_transaction_id')->references('id')->on('loan_transactions')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('collection_account_id')->references('id')->on('accounts')->onDelete('set null');

            $table->index(['organization_id', 'payment_date']);
            $table->index(['payment_date', 'recorded_by']);
            $table->index(['branch_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repayment_records');
    }
};
